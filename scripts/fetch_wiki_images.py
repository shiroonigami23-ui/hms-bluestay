from pathlib import Path
import urllib.parse
import urllib.request

root = Path(__file__).resolve().parents[1] / "public" / "assets" / "img"
root.mkdir(parents=True, exist_ok=True)

candidates = [
    "Hotel room.jpg",
    "Hotel Exterior.jpg",
    "The Exterior of the hotel.jpg",
    "Hotel_Adlon_Kempinski_Berlin_2010.jpg",
    "The_Peninsula_Hong_Kong_hotel.jpg",
    "Hotel_Four_Seasons_Hong_Kong.jpg",
    "Ritz-Carlton,_Bangalore_hotel_room.jpg",
    "Hotel_room,_Sofitel_Chicago.jpg",
]

selected = []
ua = {"User-Agent": "Mozilla/5.0 BlueStayHMS/1.0"}

for filename in candidates:
    if len(selected) >= 2:
        break
    url = "https://commons.wikimedia.org/wiki/Special:FilePath/" + urllib.parse.quote(filename)
    try:
        req = urllib.request.Request(url, headers=ua)
        with urllib.request.urlopen(req, timeout=30) as resp:
            content_type = resp.getheader("Content-Type", "")
            data = resp.read()
        if "image" not in content_type or len(data) < 50000:
            continue
        out_name = f"wiki_{len(selected) + 1}.jpg"
        out_path = root / out_name
        out_path.write_bytes(data)
        selected.append((filename, url, out_name))
        print("downloaded", filename, "->", out_name)
    except Exception as exc:
        print("failed", filename, str(exc)[:80])

if not selected:
    raise SystemExit("No Wikimedia images downloaded.")

meta = root / "wiki_sources.txt"
meta_lines = [f"{fname} | {src} | {out}" for fname, src, out in selected]
meta.write_text("\n".join(meta_lines), encoding="utf-8")
print("saved source map to", meta)
