const fs = require("fs");
let content = fs.readFileSync("views/admin/dashboard.php", "utf8");
const badStr =
  "                                    <?php if ($lo['order_status'] === 'pending' || $lo['order_status'] === 'processing'): ?>\n";
if (content.includes(badStr)) {
  content = content.replace(badStr, "");
  fs.writeFileSync("views/admin/dashboard.php", content, "utf8");
  console.log("Fixed extra if in dashboard.php");
} else {
  console.log("badStr not found, checking alternatives");
  const idx = content.indexOf(
    "<?php if ($lo['order_status'] === 'pending' || $lo['order_status'] === 'processing'): ?>",
  );
  if (idx !== -1) {
    const lineStart = content.lastIndexOf("\n", idx);
    const lineEnd = content.indexOf("\n", idx);
    content =
      content.substring(0, lineStart + 1) + content.substring(lineEnd + 1);
    fs.writeFileSync("views/admin/dashboard.php", content, "utf8");
    console.log("Fixed extra if via index");
  }
}
