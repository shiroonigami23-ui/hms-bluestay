from pathlib import Path
import json
import time
import urllib.parse
import urllib.request

OUT_DIR = Path(__file__).resolve().parents[1] / "public" / "assets" / "img"
OUT_DIR.mkdir(parents=True, exist_ok=True)

SEARCH_TERMS = {
    "pool": "hotel swimming pool",
    "lounge": "hotel lounge interior",
    "reception": "hotel reception desk",
    "bar": "hotel bar interior",
    "cuisine": "hotel restaurant food",
    "suite": "hotel suite room",
}

UA = {"User-Agent": "BlueStayHMS/1.0"}


def api_json(url: str) -> dict:
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read().decode("utf-8", "ignore"))


def image_url_for_query(query: str) -> tuple[str, str] | None:
    params = urllib.parse.urlencode(
        {
            "action": "query",
            "format": "json",
            "generator": "search",
            "gsrsearch": query,
            "gsrnamespace": 6,
            "gsrlimit": 5,
            "prop": "imageinfo",
            "iiprop": "url",
        }
    )
    url = f"https://commons.wikimedia.org/w/api.php?{params}"
    payload = api_json(url)
    pages = payload.get("query", {}).get("pages", {})
    for page in pages.values():
        infos = page.get("imageinfo")
        if not infos:
            continue
        direct_url = infos[0].get("url", "")
        title = page.get("title", "")
        if direct_url.lower().endswith((".jpg", ".jpeg", ".png", ".webp")):
            return title, direct_url
    return None


def download(url: str, target: Path) -> bool:
    req = urllib.request.Request(url, headers=UA)
    with urllib.request.urlopen(req, timeout=40) as resp:
        ctype = resp.getheader("Content-Type", "")
        data = resp.read()
    if "image" not in ctype or len(data) < 25000:
        return False
    target.write_bytes(data)
    return True


sources = []
for key, query in SEARCH_TERMS.items():
    try:
        result = image_url_for_query(query)
        if not result:
            print("skip", key, "no result")
            continue
        title, direct = result
        out = OUT_DIR / f"facility_{key}.jpg"
        if download(direct, out):
            sources.append(f"{key}: {title} | {direct}")
            print("ok", key, "->", out.name)
        else:
            print("skip", key, "download failed")
        time.sleep(1.2)
    except Exception as exc:
        print("skip", key, str(exc)[:120])

if sources:
    (OUT_DIR / "facility_sources.txt").write_text("\n".join(sources), encoding="utf-8")
    print("saved source map")
