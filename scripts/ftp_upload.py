from pathlib import Path
from ftplib import FTP
import os
import sys

ROOT = Path(__file__).resolve().parents[1]
HOST = os.getenv("FTP_HOST", "ftpupload.net")
USER = os.getenv("FTP_USER", "")
PASS = os.getenv("FTP_PASS", "")

if len(sys.argv) >= 4:
    HOST = sys.argv[1]
    USER = sys.argv[2]
    PASS = sys.argv[3]

SKIP_PARTS = {".git", "node_modules", "vendor", "__pycache__", "mobile-app", "tests", "keystore", ".idea"}


def ensure_dir(ftp: FTP, rel_path: str) -> None:
    parts = [p for p in rel_path.replace("\\", "/").split("/") if p and p != "."]
    current = ""
    for part in parts:
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
        except Exception:
            pass


def main() -> None:
    if not USER or not PASS:
        raise SystemExit("Provide credentials via env FTP_HOST/FTP_USER/FTP_PASS or args: host user pass")
    ftp = FTP(HOST, timeout=60)
    ftp.login(USER, PASS)
    print("logged in")

    try:
        ftp.cwd("htdocs")
    except Exception:
        pass

    uploaded = 0
    for path in ROOT.rglob("*"):
        rel = path.relative_to(ROOT).as_posix()
        if any(part in SKIP_PARTS for part in path.parts):
            continue
        if path.name.endswith((".jks", ".keystore", ".bak", ".pyc")):
            continue
        if path.is_dir():
            ensure_dir(ftp, rel)
            continue

        parent = Path(rel).parent.as_posix()
        ensure_dir(ftp, parent)
        with open(path, "rb") as fp:
            ftp.storbinary(f"STOR {rel}", fp)
        uploaded += 1
        if uploaded % 20 == 0:
            print(f"uploaded {uploaded}")

    ftp.quit()
    print(f"done {uploaded}")


if __name__ == "__main__":
    main()
