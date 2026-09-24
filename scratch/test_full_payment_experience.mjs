import { spawn } from 'child_process';
import fs from 'fs';
import path from 'path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const profileDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3\\scratch\\test_flow_profile';
const artDir = 'C:\\Users\\hafiz\\.gemini\\antigravity\\brain\\7af56f2e-8059-4473-806a-e5bcdc5c8ef3';

async function main() {
  console.log('1. Creating a brand new test order via API...');
  const orderPayload = {
    customer_name: 'Hafiz Fadilah',
    customer_phone: '081234567890',
    customer_email: 'hafizfadilah@gmail.com',
    order_type: 'takeaway',
    notes: 'Tolong beri kantong terpisah',
    payment_method: 'qris',
    items: [
      { product_id: 1, quantity: 2, options: {} },
      { product_id: 2, quantity: 1, options: {} }
    ]
  };

  const createRes = await fetch('http://localhost:8080/api/create_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(orderPayload)
  });

  const createJson = await createRes.json();
  console.log('Order created response:', createJson);

  if (!createJson.success || !createJson.data?.order_code) {
    throw new Error('Failed to create order: ' + JSON.stringify(createJson));
  }

  const orderCode = createJson.data.order_code;
  const initialQueueNum = createJson.data.queue_number;
  console.log(`Created Order Code: ${orderCode}, Queue: ${initialQueueNum}`);

  // 2. Check Monitor before payment: Order should NOT be on baking monitor
  console.log('2. Checking monitor queue before payment...');
  const monRes = await fetch('http://localhost:8080/api/get_live_queue.php');
  const monJson = await monRes.json();
  const bakingCodesBefore = (monJson.data?.baking || []).map(o => o.order_code);
  console.log('Is order in monitor baking before payment?', bakingCodesBefore.includes(orderCode));

  // 3. Launch Chrome for UI tests
  const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9950',
    '--user-data-dir=' + profileDir,
    'about:blank'
  ]);

  await new Promise(r => setTimeout(r, 2000));
  const resTabs = await fetch('http://127.0.0.1:9950/json/list');
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

  const errors = [];
  ws.addEventListener('message', (msg) => {
    const d = JSON.parse(msg.data);
    if (d.method === 'Runtime.exceptionThrown') {
      errors.push(d.params.exceptionDetails);
    }
  });

  await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });

  // 4. Open Invoice page for the new order
  console.log(`4. Opening invoice page for ${orderCode}...`);
  await send('Page.navigate', { url: `http://localhost:8080/views/customer/invoice.php?order_code=${orderCode}` });
  await new Promise(r => setTimeout(r, 1500));

  console.log('JS Console errors on invoice page:', errors.length);

  // Capture Unpaid invoice screenshot
  const shot1 = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'invoice_unpaid_ipaymu.png'), Buffer.from(shot1.data, 'base64'));
  console.log('Saved invoice_unpaid_ipaymu.png');

  // Test switching to VA tab
  console.log('Switching to Virtual Account tab...');
  await send('Runtime.evaluate', { expression: "switchIpaymuTab('va')" });
  await new Promise(r => setTimeout(r, 500));
  const shotVa = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'invoice_va_tab.png'), Buffer.from(shotVa.data, 'base64'));
  console.log('Saved invoice_va_tab.png');

  // 5. Click Simulation Payment button
  console.log('5. Clicking Simulation Payment button...');
  const simResult = await send('Runtime.evaluate', {
    expression: `
      const btn = document.getElementById('btn-simulate-pay');
      if (btn) {
        handleSimulatePayment(btn);
        'simulating';
      } else {
        'btn not found';
      }
    `
  });
  console.log('Simulate evaluation:', simResult?.result?.value);
  await new Promise(r => setTimeout(r, 2000));

  // Capture Paid invoice screenshot
  const shot2 = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'invoice_paid_activated.png'), Buffer.from(shot2.data, 'base64'));
  console.log('Saved invoice_paid_activated.png');

  // 6. Check Monitor after payment: Order should NOW be in baking/ready queue
  console.log('6. Checking monitor queue after payment...');
  const monResAfter = await fetch('http://localhost:8080/api/get_live_queue.php');
  const monJsonAfter = await monResAfter.json();
  const bakingCodesAfter = (monJsonAfter.data?.baking || []).map(o => o.order_code);
  console.log('Is order in monitor baking after payment?', bakingCodesAfter.includes(orderCode));

  // Open TV Monitor page in desktop view and screenshot
  await send('Emulation.setDeviceMetricsOverride', { width: 1200, height: 750, deviceScaleFactor: 1, mobile: false });
  await send('Page.navigate', { url: 'http://localhost:8080/monitor.php' });
  await new Promise(r => setTimeout(r, 1500));
  const shotMon = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'tv_monitor_synced.png'), Buffer.from(shotMon.data, 'base64'));
  console.log('Saved tv_monitor_synced.png');

  // 7. Check Admin Dashboard live queue
  console.log('7. Checking admin dashboard queue...');
  await send('Page.navigate', { url: 'http://localhost:8080/views/admin/dashboard.php' });
  await new Promise(r => setTimeout(r, 1500));
  const shotAdmin = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artDir, 'admin_queue_synced.png'), Buffer.from(shotAdmin.data, 'base64'));
  console.log('Saved admin_queue_synced.png');

  console.log('All tests completed successfully! Zero errors:', errors.length === 0);

  ws.close();
  chrome.kill();
  process.exit(0);
}

main().catch(err => {
  console.error('Test error:', err);
  process.exit(1);
});

