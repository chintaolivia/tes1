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
    "--remote-debugging-port=9995",
    "--user-data-dir=" + profileDir,
    "about:blank",
  ]);

  await new Promise((r) => setTimeout(r, 2000));
  const resTabs = await fetch("http://127.0.0.1:9995/json/list");
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
  await send("Emulation.setDeviceMetricsOverride", {
    width: 430,
    height: 1600,
    deviceScaleFactor: 2,
    mobile: true,
  });

  await send("Page.navigate", {
    url: "http://localhost:8080/views/customer/invoice.php?order_code=SB2409266DA7",
  });
  await new Promise((r) => setTimeout(r, 2000));
  await send("Runtime.evaluate", {
    expression:
      "document.querySelectorAll('#splash').forEach(s => s.remove()); document.body.classList.remove('loading');",
  });

  // 1. Test clicking "Ganti Metode Pembayaran"
  await send("Runtime.evaluate", {
    expression: "togglePaymentChangeOptions();",
  });
  await new Promise((r) => setTimeout(r, 300));
  let shot = await send("Page.captureScreenshot", {
    format: "png",
    captureBeyondViewport: true,
  });
  fs.writeFileSync(
    path.join(artDir, "invoice_bca_toggle_switcher.png"),
    Buffer.from(shot.data, "base64"),
  );
  console.log("Saved invoice_bca_toggle_switcher.png");

  // 2. Test selecting QRIS
  await send("Runtime.evaluate", {
    expression: "selectPaymentMethod('qris');",
  });
  await new Promise((r) => setTimeout(r, 300));
  shot = await send("Page.captureScreenshot", {
    format: "png",
    captureBeyondViewport: true,
  });
  fs.writeFileSync(
    path.join(artDir, "invoice_switched_to_qris.png"),
    Buffer.from(shot.data, "base64"),
  );
  console.log("Saved invoice_switched_to_qris.png");

  // 3. Test selecting back to BCA
  await send("Runtime.evaluate", { expression: "selectPaymentMethod('va');" });
  await new Promise((r) => setTimeout(r, 300));

  ws.close();
  chrome.kill();
}

main().catch(console.error);
