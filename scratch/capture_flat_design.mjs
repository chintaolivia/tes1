import { spawn } from "child_process";
import fs from "fs";
import path from "path";

const chromePath = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const profileDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_flat_profile";
const artDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3";

async function main() {
  const chrome = spawn(chromePath, [
    "--headless=new",
    "--remote-debugging-port=9992",
    "--user-data-dir=" + profileDir,
    "about:blank",
  ]);

  await new Promise((r) => setTimeout(r, 2000));
  const resTabs = await fetch("http://127.0.0.1:9992/json/list");
  const tabs = await resTabs.json();
  const pageTab = tabs.find((t) => t.type === "page") || tabs[0];
  const ws = new WebSocket(pageTab.webSocketDebuggerUrl);
  await new Promise((r) => (ws.onopen = r));

  let id = 1;
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

  // Helper to take a screenshot
  async function snap(
    url,
    filename,
    viewport = { width: 430, height: 932, mobile: true },
  ) {
    await send("Emulation.setDeviceMetricsOverride", {
      width: viewport.width,
      height: viewport.height,
      deviceScaleFactor: 2,
      mobile: viewport.mobile,
    });
    console.log(`Navigating to ${url}...`);
    await send("Page.navigate", { url });
    await new Promise((r) => setTimeout(r, 2000));
    // Dismiss splash if present
    await send("Runtime.evaluate", {
      expression: `(() => {
        const s = document.getElementById('splash');
        if (s) { s.style.display = 'none'; s.style.opacity = '0'; }
        document.body.classList.remove('loading');
      })()`,
    });
    await new Promise((r) => setTimeout(r, 500));

    const shot = await send("Page.captureScreenshot", { format: "png" });
    const outPath = path.join(artDir, filename);
    fs.writeFileSync(outPath, Buffer.from(shot.data, "base64"));
    console.log(`Saved screenshot: ${filename}`);
  }

  // 1. Checkout empty state (The button the user complained about)
  await snap(
    "http://localhost:8080/views/customer/checkout.php",
    "checkout_flat_button.png",
    { width: 430, height: 750, mobile: true },
  );

  // 2. Customer menu with item in cart showing floating cart bar
  await send("Emulation.setDeviceMetricsOverride", {
    width: 430,
    height: 850,
    deviceScaleFactor: 2,
    mobile: true,
  });
  await send("Page.navigate", {
    url: "http://localhost:8080/views/customer/index.php",
  });
  await new Promise((r) => setTimeout(r, 2000));
  await send("Runtime.evaluate", {
    expression: `(() => {
      const s = document.getElementById('splash');
      if (s) { s.style.display = 'none'; s.style.opacity = '0'; }
      document.body.classList.remove('loading');
      // Add item to cart to trigger floating cart bar
      if (typeof Cart !== 'undefined' && typeof Cart.addItem === 'function') {
        Cart.addItem(1, 'Original Sea Salt Bread', 14000, 'Original');
      }
    })()`,
  });
  await new Promise((r) => setTimeout(r, 800));
  const shotMenu = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "customer_menu_flat_cart.png"),
    Buffer.from(shotMenu.data, "base64"),
  );
  console.log("Saved screenshot: customer_menu_flat_cart.png");

  // 3. Invoice page
  await snap(
    "http://localhost:8080/views/customer/invoice.php?order_code=ORD-20260923-0002",
    "invoice_flat_hero.png",
    { width: 430, height: 900, mobile: true },
  );

  // 4. Landing page (desktop)
  await snap("http://localhost:8080/index.php", "landing_flat_hero.png", {
    width: 1200,
    height: 750,
    mobile: false,
  });

  ws.close();
  chrome.kill();
  console.log("Completed capturing all flat design screenshots!");
}

main().catch((err) => {
  console.error("Error:", err);
  process.exit(1);
});
