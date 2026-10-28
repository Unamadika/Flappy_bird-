// api.js
// Interoperability layer: the only file that talks to the Banana API.
// Keeping it separate means the UI never needs to know HTTP details, and the
// URL can later be pointed at your own backend proxy without touching ui.js.

const BANANA_CONFIG = {
  url: "https://marcconrad.com/uob/banana/api.php",
  timeoutMs: 6000
};

/**
 * Requests one puzzle from the Banana API.
 * Never throws: always resolves to an object the UI can display.
 *
 * Success: { ok: true,  imageUrl, answer, raw, status, ms, requestUrl }
 * Failure: { ok: false, kind, error, raw, status, ms, requestUrl }
 *   kind is one of: "timeout" | "network" | "http" | "format"
 */
async function fetchBananaPuzzle() {
  const requestUrl = BANANA_CONFIG.url + "?out=json";
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), BANANA_CONFIG.timeoutMs);
  const started = performance.now();
  const elapsed = () => Math.round(performance.now() - started);

  try {
    // cache: "no-store" because every call should return a fresh random puzzle
    const res = await fetch(requestUrl, { signal: controller.signal, cache: "no-store" });
    const text = await res.text(); // keep the real response text as evidence

    if (!res.ok) {
      return {
        ok: false, kind: "http",
        error: `The Banana API answered with HTTP ${res.status}.`,
        raw: text, status: res.status, ms: elapsed(), requestUrl
      };
    }

    let data;
    try {
      data = JSON.parse(text);
    } catch {
      return {
        ok: false, kind: "format",
        error: "The Banana API response was not valid JSON.",
        raw: text, status: res.status, ms: elapsed(), requestUrl
      };
    }

    // Validate the shape we observed in Step 3:
    // { "question": "<image url>", "solution": <single digit> }
    const answer = Number(data.solution);
    const validImage = typeof data.question === "string" && data.question.startsWith("http");
    const validAnswer = Number.isInteger(answer) && answer >= 0 && answer <= 9;

    if (!validImage || !validAnswer) {
      return {
        ok: false, kind: "format",
        error: "The Banana API response had an unexpected shape.",
        raw: text, status: res.status, ms: elapsed(), requestUrl
      };
    }

    return {
      ok: true, imageUrl: data.question, answer,
      raw: text, status: res.status, ms: elapsed(), requestUrl
    };

  } catch (err) {
    if (err.name === "AbortError") {
      return {
        ok: false, kind: "timeout",
        error: `No reply from the Banana API within ${BANANA_CONFIG.timeoutMs / 1000} seconds.`,
        raw: "", status: null, ms: elapsed(), requestUrl
      };
    }
    // fetch() rejects with a TypeError for offline, DNS failure AND blocked-by-CORS,
    // and the browser does not say which, so the message names the likely causes.
    return {
      ok: false, kind: "network",
      error: "Could not reach the Banana API. Check your connection, then try again.",
      raw: String(err), status: null, ms: elapsed(), requestUrl
    };
  } finally {
    clearTimeout(timer);
  }
}