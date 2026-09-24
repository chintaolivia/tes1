const fs = require("fs");
const vm = require("vm");

function checkFile(filePath) {
  const content = fs.readFileSync(filePath, "utf8");
  const scriptRegex = /<script(?:\s+[^>]*)?>([\s\S]*?)<\/script>/gi;
  let match;
  let idx = 0;
  while ((match = scriptRegex.exec(content)) !== null) {
    if (match[0].includes(" src=")) continue;
    idx++;
    let jsCode = match[1];
    if (!jsCode.trim()) continue;
    // Normalize PHP tags for testing
    let sanitized = jsCode
      .replace(/<\?=\s*json_encode\([^)]*\)\s*\?>/g, '"test"')
      .replace(/<\?=\s*[^?]+\?>/g, '"sample"')
      .replace(/<\?php[\s\S]*?\?>/g, "/* php */");
    try {
      new vm.Script(sanitized);
      console.log(`[PASS] ${filePath} script #${idx}`);
    } catch (err) {
      console.error(`[FAIL] ${filePath} script #${idx}: ${err.message}`);
      // find line number
      const lines = sanitized.split("\n");
      console.error(err.stack);
    }
  }
}

checkFile("views/customer/invoice.php");
checkFile("views/admin/orders.php");
checkFile("views/admin/dashboard.php");
checkFile("views/partials/footer.php");
