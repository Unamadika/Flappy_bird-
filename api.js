// api.js
const BANANA_URL = "https://marcconrad.com/uob/banana/api.php";

async function getBananaPuzzle() {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 5000); // timeout
  try {
    const res = await fetch(BANANA_URL, { signal: controller.signal });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!data.question || typeof data.solution !== "number")
      throw new Error("Unexpected response shape");
    return { imageUrl: data.question, answer: data.solution };
  } catch (err) {
    console.error("Banana API failed:", err);
    return null; // game.js shows a friendly message / fallback
  } finally {
    clearTimeout(timer);
  }
}