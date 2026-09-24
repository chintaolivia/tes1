const fs = require("fs");
const path = require("path");
const vm = require("vm");

function getAllPhpFiles(dir, fileList = []) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      if (file !== "vendor" && file !== "node_modules" && file !== ".git") {
        getAllPhpFiles(fullPath, fileList);
      }
    } else if (file.endsWith(".php")) {
      fileList.push(fullPath);
    }
  }
  return fileList;
}

const phpFiles = getAllPhpFiles("views");
phpFiles.push(path.join(__dirname, "../index.php"));
phpFiles.push(path.join(__dirname, "../monitor.php"));

const { execSync } = require("child_process");

let hasError = false;

for (const filePath of phpFiles) {
  if (!fs.existsSync(filePath)) continue;
  try {
    execSync(`php -l "${filePath}"`, { stdio: "pipe" });
  } catch (err) {
    hasError = true;
    console.error(
      `[PHP FAIL] ${filePath}:`,
      err.stdout ? err.stdout.toString() : err.message,
    );
  }
}
console.log("PHP syntax check done.");

for (const filePath of phpFiles) {
  if (!fs.existsSync(filePath)) continue;
  const content = fs.readFileSync(filePath, "utf8");
  const scriptRegex = /<script(?:\s+[^>]*)?>([\s\S]*?)<\/script>/gi;
  let match;
  let idx = 0;
  while ((match = scriptRegex.exec(content)) !== null) {
    const openTag = match[0].match(/<script(?:\s+[^>]*)?>/i)[0];
    if (/\ssrc\s*=/i.test(openTag)) continue;
    idx++;
    let jsCode = match[1];
    if (!jsCode.trim()) continue;
    let sanitized = jsCode
      .replace(/<\?=\s*json_encode\([^)]*\)\s*\?>/g, '"test"')
      .replace(/<\?=(?:[\s\S]*?)\?>/g, '"sample"')
      .replace(/<\?php[\s\S]*?\?>/g, "/* php */");
    try {
      new vm.Script(sanitized);
      console.log(`[PASS] ${filePath} script #${idx}`);
    } catch (err) {
      hasError = true;
      console.error(`[FAIL] ${filePath} script #${idx}: ${err.message}`);
    }
  }
}

if (!hasError) {
  console.log("ALL INLINE JAVASCRIPT SCRIPTS ARE 100% VALID SYNTAX!");
} else {
  process.exit(1);
}
