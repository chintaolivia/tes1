const fs = require("fs");
const path = require("path");
const vm = require("vm");

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach((file) => {
    const full = path.join(dir, file);
    if (
      file === "node_modules" ||
      file === ".git" ||
      file === "vendor" ||
      file === "scratch"
    )
      return;
    const stat = fs.statSync(full);
    if (stat && stat.isDirectory()) {
      results = results.concat(walk(full));
    } else if (file.endsWith(".php")) {
      results.push(full);
    }
  });
  return results;
}

const phpFiles = walk(".");
let failCount = 0;
phpFiles.forEach((f) => {
  const content = fs.readFileSync(f, "utf8");
  const scriptRegex = /<script(?:\s+[^>]*)?>([\s\S]*?)<\/script>/gi;
  let match;
  let idx = 0;
  while ((match = scriptRegex.exec(content)) !== null) {
    idx++;
    let jsCode = match[1];
    if (!jsCode.trim()) continue;
    // Replace php tags like <?= ... ?> or <?php ... ?> with safe literals like 0 or ''
    const sanitizedJs = jsCode
      .replace(/<\?=\s*json_encode\([^)]*\)\s*\?>/g, '"test"')
      .replace(/<\?=\s*[^?]+\?>/g, '"sample"')
      .replace(/<\?php[\s\S]*?\?>/g, "/* php block */");
    try {
      new vm.Script(sanitizedJs);
    } catch (err) {
      console.error(`FAIL script in ${f} (tag #${idx}): ${err.message}`);
      failCount++;
    }
  }
});
console.log(
  `Checked inline scripts in ${phpFiles.length} PHP files. Fails: ${failCount}`,
);
