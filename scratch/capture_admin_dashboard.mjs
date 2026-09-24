import { spawn } from 'child_process';
import fs from 'fs';
import path from 'path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const profileDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_admin_shot_profile';
const profileDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_admin_profile';
const artDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3';

async function main() {
  const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9965',
    '--remote-debugging-port=9988',
    '--user-data-dir=' + profileDir,
    'about:blank'
  ]);

  await new Promise(r => setTimeout(r, 2000));
  const resTabs = await fetch('http://127.0.0.1:9965/json/list');
  const resTabs = await fetch('http://127.0.0.1:9988/json/list');
  const tabs = await resTabs.json();
  const pageTab = tabs.find(t => t.type === 'page') || tabs[0];
  const ws = new WebSocket(pageTab.webSocketDebuggerUrl);
  await new Promise(r => ws.onopen = r);

  let id = 1;
  const consoleErrors = [];

  ws.addEventListener('message', (msg) => {
    const d = JSON.parse(msg.data);
    if (d.method === 'Runtime.consoleAPICalled' && d.params.type === 'error') {
      consoleErrors.push(d.params.args.map(a => a.value || a.description).join(' '));
    }
  });

  const send = (method, params = {}) => new Promise(res => {
    const curId = id++;
    const cb = (msg) => {
      const d = JSON.parse(msg.data);
      if (d.id === curId) { ws.removeEventListener('message', cb); res(d.result); }
    };
    ws.addEventListener('message', cb);
    ws.send(JSON.stringify({ id: curId, method, params }));
  });

  await send('Page.enable');
  await send('Runtime.enable');
  await send('Emulation.setDeviceMetricsOverride', { width: 1300, height: 800, deviceScaleFactor: 1, mobile: false });
  await send('Emulation.setDeviceMetricsOverride', { width: 1280, height: 800, deviceScaleFactor: 1, mobile: false });

  // 1. Login as admin
  console.log('Navigating to login...');
  console.log('Navigating to login page...');
  await send('Page.navigate', { url: 'http://localhost:8080/views/auth/login.php' });
  await new Promise(r => setTimeout(r, 1500));

  console.log('Submitting login credentials...');
  await send('Runtime.evaluate', {
    expression: `
      document.getElementById('email').value = 'admin@saltbread.id';
      document.getElementById('password').value = 'admin123';
      document.getElementById('btn-login').click();
    `
    expression: `(() => {
      const emailInput = document.querySelector('input[type="email"], input[name="email"]');
      const passInput = document.querySelector('input[type="password"], input[name="password"]');
      const form = document.querySelector('form');
      if (emailInput) emailInput.value = 'admin@saltbread.id';
      if (passInput) passInput.value = 'admin123';
      const submitBtn = document.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.click();
      else if (form) form.submit();
    })()`
  });
  console.log('Login submitted, waiting for redirect...');
  await new Promise(r => setTimeout(r, 3000));

  // 2. Open Admin Dashboard
  console.log('Navigating to dashboard...');
  await new Promise(r => setTimeout(r, 2500));

  console.log('Navigating to Dashboard: http://localhost:8080/views/admin/dashboard.php');
  await send('Page.navigate', { url: 'http://localhost:8080/views/admin/dashboard.php' });
  await new Promise(r => setTimeout(r, 2000));

  // Switch to Antrean Dapur tab in POS panel
  console.log('Switching to Antrean Dapur tab...');
  await send('Runtime.evaluate', {
    expression: `
      const tab = document.querySelectorAll('.pos-tab-btn')[1];
      if (tab) tab.click();
    `
  const pageInfo = await send('Runtime.evaluate', {
    expression: `(() => {
      return {
        url: window.location.href,
        title: document.title,
        hasError: document.body.innerText.includes('Fatal error') || document.body.innerText.includes('PDOException'),
        bodySnippet: document.body.innerText.substring(0, 300)
      };
    })()`,
    returnByValue: true
  });
  await new Promise(r => setTimeout(r, 1000));
  console.log('Dashboard Page Info:', pageInfo.result.value);

  const shotAdmin = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'admin_queue_synced.png'), Buffer.from(shotAdmin.data, 'base64'));
  console.log('Saved admin_queue_synced.png');
  const shot = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'admin_dashboard_sql_fixed.png'), Buffer.from(shot.data, 'base64'));
  console.log('Saved admin_dashboard_sql_fixed.png');

  ws.close();
  console.log('Navigating to Live Orders POS: http://localhost:8080/views/admin/orders.php');
  await send('Page.navigate', { url: 'http://localhost:8080/views/admin/orders.php' });
  await new Promise(r => setTimeout(r, 2000));

  const shotOrders = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'admin_orders_sql_fixed.png'), Buffer.from(shotOrders.data, 'base64'));
  console.log('Saved admin_orders_sql_fixed.png');

  console.log('Console Errors:', consoleErrors.length ? consoleErrors : 'NONE');

  chrome.kill();
  process.exit(0);
}

main().catch(err => {
  console.error(err);
main().catch(e => {
  console.error(e);
  process.exit(1);
});

