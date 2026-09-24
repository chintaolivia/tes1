import { spawn } from "child_process";
import fs from "fs";
import path from "path";

const chromePath = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const profileDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_checkout_profile3";
const artDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3";

const chrome = spawn(chromePath, [
  "--headless=new",
  "--remote-debugging-port=9945",
  "--user-data-dir=" + profileDir,
  "about:blank",
]);

async function run() {
  await new Promise((r) => setTimeout(r, 2000));
  const res = await fetch("http://127.0.0.1:9945/json/list");
  const tabs = await res.json();
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

  const errors = [];
  ws.addEventListener("message", (msg) => {
    const d = JSON.parse(msg.data);
    if (d.method === "Runtime.exceptionThrown") {
      errors.push(d.params.exceptionDetails);
    }
  });

  await send("Emulation.setDeviceMetricsOverride", {
    width: 390,
    height: 844,
    deviceScaleFactor: 2,
    mobile: true,
  });

  console.log("1. Setting cart items...");
  await send("Page.navigate", {
    url: "http://localhost:8080/views/customer/index.php",
  });
  await new Promise((r) => setTimeout(r, 1000));

  await send("Runtime.evaluate", {
    expression: `
      const sampleCart = [
        { id: 1, name: 'Original Salt Bread', price: 17000, quantity: 2, image: 'assets/img/products/original.png' },
        { id: 2, name: 'Truffle Egg Salt Bread', price: 28000, quantity: 1, image: 'assets/img/products/truffle.png' }
      ];
      localStorage.setItem('sb_cart', JSON.stringify(sampleCart));
    `,
  });

  console.log("2. Navigating to checkout.php...");
  await send("Page.navigate", {
    url: "http://localhost:8080/views/customer/checkout.php",
  });
  await new Promise((r) => setTimeout(r, 1500));

  // Scroll to bottom of step 1 to see button
  await send("Runtime.evaluate", {
    expression: "window.scrollTo(0, document.body.scrollHeight);",
  });
  await new Promise((r) => setTimeout(r, 500));

  const shotStep1 = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "checkout_step1_scrolled.png"),
    Buffer.from(shotStep1.data, "base64"),
  );

  // Click #btn-next-step
  console.log("3. Clicking #btn-next-step...");
  const nextRes = await send("Runtime.evaluate", {
    expression: `
      const btn = document.getElementById('btn-next-step');
      if (btn) {
        btn.click();
        'success';
      } else {
        'not found';
      }
    `,
  });
  console.log("Next step click result:", nextRes?.result?.value);
  await new Promise((r) => setTimeout(r, 800));

  // Capture Step 2 (Payment Details form)
  const shotStep2 = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "checkout_step2_verified.png"),
    Buffer.from(shotStep2.data, "base64"),
  );
  console.log("Saved checkout_step2_verified.png");

  console.log("Errors:", errors.length);

  ws.close();
  chrome.kill();
  process.exit(0);
}

run().catch((err) => {
  console.error(err);
  chrome.kill();
  process.exit(1);
});
