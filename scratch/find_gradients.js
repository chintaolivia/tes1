const fs = require("fs");
const path = require("path");

function scanDir(dir, results = []) {
  for (const item of fs.readdirSync(dir)) {
    if (
      ["node_modules", ".git", "vendor", "tes1-main", "scratch"].includes(item)
    )
      continue;
    const full = path.join(dir, item);
    const stat = fs.statSync(full);
    if (stat.isDirectory()) {
      scanDir(full, results);
    } else if (/\.(css|php|js|html)$/i.test(item)) {
      const content = fs.readFileSync(full, "utf8");
      if (content.toLowerCase().includes("gradient")) {
        const lines = content.split("\n");
        const matches = [];
        lines.forEach((line, i) => {
          if (line.toLowerCase().includes("gradient")) {
            matches.push({ line: i + 1, text: line.trim() });
          }
        });
        results.push({ file: full, matches });
      }
    }
  }
  return results;
}

const list = scanDir(".");
console.log(`Found ${list.length} files with gradient:`);
for (const item of list) {
  console.log(`\n=== ${item.file} (${item.matches.length} occurrences) ===`);
  for (const m of item.matches) {
    console.log(`  L${m.line}: ${m.text}`);
  }
}
