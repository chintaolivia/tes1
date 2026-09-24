import { spawn } from "child_process";
import fs from "fs";
import path from "path";

const chromePath = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const profileDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_admin_profile";
const artDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3";

async function main() {
  const chrome = spawn(chromePath, [
    "--headless=new",
    "--remote-debugging-port=9991",
    "--user-data-dir=" + profileDir,
    "about:blank",
  ]);

  await new Promise((r) => setTimeout(r, 2000));
  const resTabs = await fetch("http://127.0.0.1:9991/json/list");
  const tabs = await resTabs.json();
  const pageTab = tabs.find((t) => t.type === "page") || tabs[0];
  const ws = new WebSocket(pageTab.webSocketDebuggerUrl);
  await new Promise((r) => (ws.onopen = r));

  let id = 1;
  const consoleErrors = [];

  ws.addEventListener("message", (msg) => {
    const d = JSON.parse(msg.data);
    if (d.method === "Runtime.consoleAPICalled" && d.params.type === "error") {
      consoleErrors.push(
        d.params.args.map((a) => a.value || a.description).join(" "),
      );
    }
  });

  const send = (method, params = {}) =>
    new Promise((res) => {
      const curId = id++;
      const cb = (msg) => {
        const d = JSON.parse(msg.data);
        if (d.id === curId) {
          ws.removeEventListener("message", cb);
          res(d.result);
        }
      };
      ws.addEventListener("message", cb);
      ws.send(JSON.stringify({ id: curId, method, params }));
    });

  await send("Page.enable");
  await send("Runtime.enable");
  await send("Emulation.setDeviceMetricsOverride", {
    width: 1280,
    height: 850,
    deviceScaleFactor: 1,
    mobile: false,
  });

  console.log("Logging in as admin...");
  await send("Page.navigate", {
    url: "http://localhost:8080/views/auth/login.php",
  });
  await new Promise((r) => setTimeout(r, 1500));
  await send("Runtime.evaluate", {
    expression: `(() => {
      const emailInput = document.querySelector('input[type="email"], input[name="email"]');
      const passInput = document.querySelector('input[type="password"], input[name="password"]');
      const form = document.querySelector('form');
      if (emailInput) emailInput.value = 'admin@saltbread.id';
      if (passInput) passInput.value = 'admin123';
      const submitBtn = document.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.click();
      else if (form) form.submit();
    })()`,
  });
  await new Promise((r) => setTimeout(r, 2000));

  console.log(
    "Navigating to Live Orders POS: http://localhost:8080/views/admin/orders.php",
  );
  await send("Page.navigate", {
    url: "http://localhost:8080/views/admin/orders.php",
  });
  await new Promise((r) => setTimeout(r, 2000));

  const shotCards = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "admin_orders_clean_cards.png"),
    Buffer.from(shotCards.data, "base64"),
  );
  console.log("Saved admin_orders_clean_cards.png");

  // Trigger meatball menu on the second card
  console.log("Opening meatball menu on second card...");
  await send("Runtime.evaluate", {
    expression: `(() => {
      const buttons = document.querySelectorAll('.btn-meatball');
      if (buttons.length > 1) {
        buttons[1].click();
      } else if (buttons.length > 0) {
        buttons[0].click();
      }
    })()`,
  });

  await new Promise((r) => setTimeout(r, 600));

  const shotDropdown = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "admin_orders_meatball_menu.png"),
    Buffer.from(shotDropdown.data, "base64"),
  );
  console.log("Saved admin_orders_meatball_menu.png");

  console.log(
    "Console Errors caught:",
    consoleErrors.length ? consoleErrors : "NONE",
  );

  chrome.kill();
  process.exit(0);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
