export function extractFirstUrl(text) {
  if (typeof text !== "string") return null;

  const match = text.match(/https?:\/\/[^\s]+/i);
  if (!match) return null;

  return match[0].replace(/[)\],.!]+$/, "");
}

export function extractUrls(text) {
  if (typeof text !== "string") return [];

  const matches = text.match(/https?:\/\/[^\s]+/gi);
  if (!matches) return [];

  return matches.map((url) => url.replace(/[)\],.!]+$/, ""));
}

export function extractYoutubeVideoId(input) {
  if (!input) return null;

  const candidates =
    typeof input === "string" ? [...extractUrls(input), input.trim()] : [];

  for (const candidate of candidates) {
    if (!candidate) continue;

    let parsedUrl;
    try {
      parsedUrl = new URL(candidate);
    } catch {
      continue;
    }

    const hostname = parsedUrl.hostname.replace(/^www\./, "").replace(/^m\./, "");
    const pathParts = parsedUrl.pathname.split("/").filter(Boolean);

    let videoId = null;

    if (hostname === "youtu.be") {
      videoId = pathParts[0] ?? null;
    } else if (hostname === "youtube.com" || hostname === "youtube-nocookie.com") {
      if (parsedUrl.pathname === "/watch") {
        videoId = parsedUrl.searchParams.get("v");
      } else if (parsedUrl.pathname.startsWith("/shorts/")) {
        videoId = pathParts[1] ?? null;
      } else if (parsedUrl.pathname.startsWith("/embed/")) {
        videoId = pathParts[1] ?? null;
      } else if (parsedUrl.pathname.startsWith("/live/")) {
        videoId = pathParts[1] ?? null;
      }
    }

    if (!videoId) continue;

    const normalizedId = videoId.trim();
    if (!/^[a-zA-Z0-9_-]{6,}$/.test(normalizedId)) continue;

    return normalizedId;
  }

  return null;
}

export function extractYoutubeEmbedUrl(input) {
  const videoId = extractYoutubeVideoId(input);
  if (!videoId) return null;
  return `https://www.youtube.com/embed/${videoId}`;
}

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

export function stripYoutubeUrls(text) {
  if (typeof text !== "string") return "";

  let result = text;
  const urls = extractUrls(text);

  for (const url of urls) {
    if (!extractYoutubeEmbedUrl(url)) continue;

    const escaped = escapeRegExp(url);
    result = result.replace(new RegExp(escaped, "g"), "");
  }

  result = result
    .replace(/[ \t]+\n/g, "\n")
    .replace(/\n[ \t]+/g, "\n")
    .replace(/[ \t]{2,}/g, " ")
    .replace(/\s+([,.;:!?])/g, "$1")
    .replace(/\n{3,}/g, "\n\n")
    .trim();

  return result;
}
