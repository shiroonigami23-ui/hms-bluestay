from pathlib import Path
from PIL import Image, ImageDraw

root = Path(__file__).resolve().parents[1]
out = root / "docs" / "demo.gif"
out.parent.mkdir(parents=True, exist_ok=True)

screens = [
    ("Landing", "#0A63FF"),
    ("Auth", "#0D7FEA"),
    ("Dashboard", "#0A4CB8"),
    ("Invoices", "#006AD4"),
    ("API Ready", "#0058AD"),
]

frames = []
for title, color in screens:
    img = Image.new("RGB", (960, 540), color)
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((60, 60, 900, 480), radius=24, fill="white")
    draw.text((100, 110), "BlueStay HMS", fill=color)
    draw.text((100, 180), f"{title} Preview", fill="#0A2447")
    draw.text((100, 250), "Responsive blue-white panel", fill="#35557A")
    draw.text((100, 300), "Role dashboards | Billing | Services | Reports", fill="#35557A")
    frames.append(img)

frames[0].save(out, save_all=True, append_images=frames[1:], duration=900, loop=0)
print(f"created {out}")
