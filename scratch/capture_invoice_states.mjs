import { spawn } from 'child_process';
import fs from 'fs';
import path from 'path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const profileDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_shot_profile';
const artDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3';

async function main() {
  const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9955',
    '--user-data-dir=' + profileDir,
    'about:blank'
  ]);

  await new Promise(r => setTimeout(r, 2000));
  const resTabs = await fetch('http://127.0.0.1:9955/json/list');
  const tabs = await resTabs.json();
  const pageTab = tabs.find(t => t.type === 'page') || tabs[0];
  const ws = new WebSocket(pageTab.webSocketDebuggerUrl);
  await new Promise(r => ws.onopen = r);

  let id = 1;
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
  await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });

  // 1. Paid order SB2309264458
  console.log('Navigating to paid invoice...');
  await send('Page.navigate', { url: 'http://localhost:8080/views/customer/invoice.php?order_code=SB2309264458' });
  // Wait 2.5s for splash screen to disappear completely
  await new Promise(r => setTimeout(r, 2500));

  const shotPaid = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'invoice_paid_activated.png'), Buffer.from(shotPaid.data, 'base64'));
  console.log('Saved invoice_paid_activated.png');

  // 2. Create another unpaid order to capture the unpaid screen with QRIS tab open
  console.log('Creating unpaid order for fresh screenshot...');
  const orderPayload = {
    customer_name: 'Dewi Lestari',
    customer_phone: '081298765432',
    customer_email: 'dewi@example.com',
    order_type: 'dine_in',
    payment_method: 'qris',
    items: [{ product_id: 1, quantity: 1, options: {} }]
  };
  const crRes = await fetch('http://localhost:8080/api/create_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(orderPayload)
  });
  const crJson = await crRes.json();
  const unpaidCode = crJson.data.order_code;
  console.log('Unpaid order code:', unpaidCode);

  await send('Page.navigate', { url: `http://localhost:8080/views/customer/invoice.php?order_code=${unpaidCode}` });
  await new Promise(r => setTimeout(r, 2500));

  const shotUnpaid = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'invoice_unpaid_ipaymu.png'), Buffer.from(shotUnpaid.data, 'base64'));
  console.log('Saved invoice_unpaid_ipaymu.png');

  // 3. Switch to VA tab on unpaid order
  await send('Runtime.evaluate', { expression: "switchIpaymuTab('va')" });
  await new Promise(r => setTimeout(r, 600));
  const shotVa = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'invoice_va_tab.png'), Buffer.from(shotVa.data, 'base64'));
  console.log('Saved invoice_va_tab.png');

  // 4. TV Monitor in Desktop
  console.log('Capturing TV Monitor...');
  await send('Emulation.setDeviceMetricsOverride', { width: 1200, height: 750, deviceScaleFactor: 1, mobile: false });
  await send('Page.navigate', { url: 'http://localhost:8080/monitor.php' });
  await new Promise(r => setTimeout(r, 2500));
  const shotMon = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'tv_monitor_synced.png'), Buffer.from(shotMon.data, 'base64'));
  console.log('Saved tv_monitor_synced.png');

  // 5. Admin Dashboard
  console.log('Capturing Admin Dashboard...');
  await send('Page.navigate', { url: 'http://localhost:8080/views/admin/dashboard.php' });
  await new Promise(r => setTimeout(r, 2500));
  const shotAdmin = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'admin_queue_synced.png'), Buffer.from(shotAdmin.data, 'base64'));
  console.log('Saved admin_queue_synced.png');

  ws.close();
  chrome.kill();
  process.exit(0);
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});

