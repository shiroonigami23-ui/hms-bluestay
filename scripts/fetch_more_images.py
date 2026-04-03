from __future__ import annotations

import json
import time
import urllib.parse
import urllib.request
from pathlib import Path

OUT_DIR = Path(__file__).resolve().parents[1] / "public" / "assets" / "img"
OUT_DIR.mkdir(parents=True, exist_ok=True)

SEARCH_TERMS = {
    "cuisine_indian": "indian cuisine plated hotel restaurant",
    "cuisine_continental": "continental breakfast hotel",
    "cuisine_asian": "asian cuisine hotel restaurant",
    "suite_couple": "hotel honeymoon suite interior",
    "suite_family": "hotel family suite room",
    "suite_business": "hotel executive suite room desk",
    "facility_bar": "hotel bar interior counter",
    "facility_reception": "hotel reception desk lobby",
}

UA = {"User-Agent": "BlueStayHMS/1.0 (image-fetch)"}


def api_json(url: str) -> dict:
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=45) as resp:
        return json.loads(resp.read().decode("utf-8", "ignore"))


def get_image_url(query: str) -> tuple[str, str] | None:
    params = urllib.parse.urlencode(
        {
            "action": "query",
            "format": "json",
            "generator": "search",
            "gsrsearch": query,
            "gsrnamespace": 6,
            "gsrlimit": 8,
            "prop": "imageinfo",
            "iiprop": "url",
        }
    )
    url = f"https://commons.wikimedia.org/w/api.php?{params}"
    payload = api_json(url)
    pages = payload.get("query", {}).get("pages", {})
    for page in pages.values():
        title = page.get("title", "")
        infos = page.get("imageinfo") or []
        if not infos:
            continue
        direct = infos[0].get("url", "")
        if direct.lower().endswith((".jpg", ".jpeg", ".png", ".webp")):
            return title, direct
    return None


def download(url: str, target: Path) -> bool:
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=60) as resp:
        ctype = (resp.getheader("Content-Type") or "").lower()
        data = resp.read()
    if "image" not in ctype or len(data) < 20000:
        return False
    target.write_bytes(data)
    return True


def fetch_one(key: str, query: str) -> str | None:
    for attempt in range(1, 6):
        try:
            found = get_image_url(query)
            if not found:
                time.sleep(2 * attempt)
                continue
            title, url = found
            out = OUT_DIR / f"{key}.jpg"
            if download(url, out):
                return f"{key}: {title} | {url}"
        except Exception:
            pass
        time.sleep(2 * attempt)
    return None


def main() -> None:
    source_lines = []
    for key, query in SEARCH_TERMS.items():
        result = fetch_one(key, query)
        if result:
            print("ok", key)
            source_lines.append(result)
        else:
            print("skip", key)
        time.sleep(1.5)

    if source_lines:
        src_file = OUT_DIR / "facility_sources.txt"
        existing = src_file.read_text(encoding="utf-8") if src_file.exists() else ""
        merged = existing.strip() + ("\n" if existing.strip() else "") + "\n".join(source_lines)
        src_file.write_text(merged.strip() + "\n", encoding="utf-8")
        print("sources updated")


if __name__ == "__main__":
    main()
