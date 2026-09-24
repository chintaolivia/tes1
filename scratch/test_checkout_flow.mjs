import { chromium } from "playwright";

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    userAgent:
      "Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1",
  });

  const page = await context.newPage();

  const errors = [];
  page.on("console", (msg) => {
    if (msg.type() === "error") {
      errors.push(msg.text());
    }
  });
  page.on("pageerror", (err) => {
    errors.push(err.message);
  });

  console.log("Navigating to menu page...");
  await page.goto("http://localhost:8080/views/customer/index.php", {
    waitUntil: "networkidle",
  });

  // Add 1 item to cart using JS or click
  await page.evaluate(() => {
    // Add item to cart
    if (typeof addToCart === "function") {
      addToCart(1, "Original Salt Bread", 17000, "");
    } else {
      const cart = [
        {
          id: 1,
          name: "Original Salt Bread",
          price: 17000,
          quantity: 2,
          image: "",
        },
      ];
      localStorage.setItem("sb_cart", JSON.stringify(cart));
    }
  });

  // Navigate to checkout
  console.log("Navigating to checkout page...");
  const res = await page.goto(
    "http://localhost:8080/views/customer/checkout.php",
    { waitUntil: "networkidle" },
  );
  console.log("Checkout HTTP status:", res.status());

  const pageText = await page.innerText("body");
  if (pageText.includes("Parse error") || pageText.includes("Fatal error")) {
    console.error("PHP ERROR DETECTED ON PAGE:");
    console.error(pageText.substring(0, 300));
  } else {
    console.log("SUCCESS: No PHP errors on checkout page!");
  }

  // Take screenshot
  await page.screenshot({
    path: "C:/Users/hafiz/.gemini/antigravity/brain/7af56f2e-8059-4473-806a-e5bcdc5c8ef3/checkout_page_verified.png",
  });
  console.log("Screenshot saved to checkout_page_verified.png");
  console.log("Console errors:", errors);

  await browser.close();
})();
