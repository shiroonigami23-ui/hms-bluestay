from pathlib import Path
from PIL import Image, ImageDraw, ImageFont, ImageOps

root = Path(__file__).resolve().parents[1]
assets = root / "public" / "assets" / "img"
out = root / "docs" / "demo.gif"
out.parent.mkdir(parents=True, exist_ok=True)

W, H = 1080, 608
TITLE = ImageFont.load_default()


def fit(path: Path, size: tuple[int, int]) -> Image.Image:
    img = Image.open(path).convert("RGB")
    return ImageOps.fit(img, size, method=Image.Resampling.LANCZOS)


def frame(title: str, subtitle: str, hero: Path, tiles: list[Path]) -> Image.Image:
    canvas = Image.new("RGB", (W, H), "#edf4ff")
    draw = ImageDraw.Draw(canvas)

    draw.rounded_rectangle((24, 22, W - 24, H - 22), radius=24, fill="#ffffff", outline="#d2e2ff", width=2)
    draw.rounded_rectangle((44, 44, W - 44, 98), radius=16, fill="#0a63ff")
    draw.text((64, 62), "BlueStay HMS", fill="white", font=TITLE)
    draw.text((220, 62), f"{title}", fill="white", font=TITLE)
    draw.text((64, 112), subtitle, fill="#0a2447", font=TITLE)

    hero_img = fit(hero, (520, 290))
    canvas.paste(hero_img, (64, 154))
    draw.rounded_rectangle((64, 154, 584, 444), radius=14, outline="#c7dcff", width=2)

    x0, y0 = 612, 154
    tw, th = 196, 136
    gap = 14
    for i, tile in enumerate(tiles[:4]):
        r = i // 2
        c = i % 2
        x = x0 + c * (tw + gap)
        y = y0 + r * (th + gap)
        tile_img = fit(tile, (tw, th))
        canvas.paste(tile_img, (x, y))
        draw.rounded_rectangle((x, y, x + tw, y + th), radius=10, outline="#c7dcff", width=2)

    draw.rounded_rectangle((64, 470, W - 64, 548), radius=14, fill="#f3f8ff", outline="#d4e2ff")
    draw.text((84, 492), "Premium suites | Cuisine experiences | Pool, lounge, reception & bar", fill="#1f3f6b", font=TITLE)
    return canvas


frames = [
    frame(
        "Landing Page",
        "Elegant homepage with premium packages and hospitality highlights.",
        assets / "wiki_2.jpg",
        [assets / "suite_couple.jpg", assets / "suite_family.jpg", assets / "cuisine_indian.jpg", assets / "facility_reception.jpg"],
    ),
    frame(
        "Auth Experience",
        "Customer-first registration and secure login flow for assigned staff accounts.",
        assets / "facility_reception.jpg",
        [assets / "suite_couple.jpg", assets / "cuisine_continental.jpg", assets / "facility_lounge.jpg", assets / "facility_bar.jpg"],
    ),
    frame(
        "Facilities Showcase",
        "Dedicated tabs for suites, cuisine, leisure and curated travel packages.",
        assets / "facility_pool.jpg",
        [assets / "cuisine_asian.jpg", assets / "facility_lounge.jpg", assets / "facility_bar.jpg", assets / "facility_cuisine.jpg"],
    ),
]

frames[0].save(out, save_all=True, append_images=frames[1:], duration=1200, loop=0)
print(f"created {out}")
