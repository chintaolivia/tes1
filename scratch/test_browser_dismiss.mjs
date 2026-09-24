import { spawn } from "child_process";
import fs from "fs";
import path from "path";

const chromePath = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const profileDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_shot_profile";
const artDir =
  "C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3";

async function main() {
  const chrome = spawn(chromePath, [
    "--headless=new",
    "--remote-debugging-port=9966",
    "--user-data-dir=" + profileDir,
    "about:blank",
  ]);

  await new Promise((r) => setTimeout(r, 2000));
  const resTabs = await fetch("http://127.0.0.1:9966/json/list");
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
    if (d.method === "Runtime.exceptionThrown") {
      consoleErrors.push(
        d.params.exceptionDetails.text +
          " " +
          (d.params.exceptionDetails.exception?.description || ""),
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
    width: 390,
    height: 844,
    deviceScaleFactor: 2,
    mobile: true,
  });

  console.log("Testing Homepage: http://localhost:8080/index.php");
  await send("Page.navigate", { url: "http://localhost:8080/index.php" });
  await new Promise((r) => setTimeout(r, 1500));

  const splashStateHome = await send("Runtime.evaluate", {
    expression: `(() => {
      const splash = document.getElementById('splash');
      return {
        exists: !!splash,
        classList: splash ? Array.from(splash.classList) : [],
        display: splash ? window.getComputedStyle(splash).display : 'none'
      };
    })()`,
    returnByValue: true,
  });
  console.log("Home Page Splash State:", splashStateHome.result.value);

  const shotHome = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "homepage_mobile_splash_fixed.png"),
    Buffer.from(shotHome.data, "base64"),
  );
  console.log("Saved homepage_mobile_splash_fixed.png");

  console.log(
    "Testing Menu Page: http://localhost:8080/views/customer/index.php",
  );
  await send("Page.navigate", {
    url: "http://localhost:8080/views/customer/index.php",
  });
  await new Promise((r) => setTimeout(r, 1500));

  // Check splash element
  const splashStateMenu = await send("Runtime.evaluate", {
    expression: `(() => {
      const splash = document.getElementById('splash');
      return {
        exists: !!splash,
        classList: splash ? Array.from(splash.classList) : [],
        display: splash ? window.getComputedStyle(splash).display : 'none',
        opacity: splash ? window.getComputedStyle(splash).opacity : '0',
        pointerEvents: splash ? window.getComputedStyle(splash).pointerEvents : 'none'
      };
    })()`,
    returnByValue: true,
  });
  console.log("Menu Page Splash State:", splashStateMenu.result.value);

  const shotMenu = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "menu_mobile_splash_fixed.png"),
    Buffer.from(shotMenu.data, "base64"),
  );
  console.log("Saved menu_mobile_splash_fixed.png");

  console.log(
    "Testing Invoice Page: http://localhost:8080/views/customer/invoice.php?order_code=SB2309264458",
  );
  await send("Page.navigate", {
    url: "http://localhost:8080/views/customer/invoice.php?order_code=SB2309264458",
  });
  await new Promise((r) => setTimeout(r, 1500));

  const splashStateInv = await send("Runtime.evaluate", {
    expression: `(() => {
      const splash = document.getElementById('splash');
      return {
        exists: !!splash,
        classList: splash ? Array.from(splash.classList) : [],
        display: splash ? window.getComputedStyle(splash).display : 'none',
        opacity: splash ? window.getComputedStyle(splash).opacity : '0',
        pointerEvents: splash ? window.getComputedStyle(splash).pointerEvents : 'none'
      };
    })()`,
    returnByValue: true,
  });
  console.log("Invoice Page Splash State:", splashStateInv.result.value);

  const shotInv = await send("Page.captureScreenshot", { format: "png" });
  fs.writeFileSync(
    path.join(artDir, "invoice_mobile_splash_fixed.png"),
    Buffer.from(shotInv.data, "base64"),
  );
  console.log("Saved invoice_mobile_splash_fixed.png");

  console.log(
    "Console Errors caught:",
    consoleErrors.length ? consoleErrors : "NONE (Clean!)",
  );

  chrome.kill();
  process.exit(0);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
