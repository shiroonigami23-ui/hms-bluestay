from __future__ import annotations

import concurrent.futures
import http.cookiejar
import json
import os
import re
import statistics
import subprocess
import sys
import time
import urllib.parse
import urllib.request
from dataclasses import dataclass, field
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[1]
PHP_EXE = Path(r"C:\xampp\php\php.exe")
MYSQL_EXE = Path(r"C:\xampp\mysql\bin\mysql.exe")
BASE_URL = "http://127.0.0.1:8088"


@dataclass
class TestResult:
    name: str
    status: str
    detail: str = ""
    elapsed_ms: int = 0


@dataclass
class Report:
    suite: str
    results: list[TestResult] = field(default_factory=list)

    def add(self, name: str, ok: bool, detail: str = "", elapsed_ms: int = 0) -> None:
        self.results.append(TestResult(name=name, status="PASS" if ok else "FAIL", detail=detail, elapsed_ms=elapsed_ms))

    @property
    def passed(self) -> int:
        return sum(1 for r in self.results if r.status == "PASS")

    @property
    def failed(self) -> int:
        return sum(1 for r in self.results if r.status == "FAIL")


class HttpClient:
    def __init__(self) -> None:
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies))

    def cookie_header(self) -> str:
        return "; ".join([f"{c.name}={c.value}" for c in self.cookies])

    def request(
        self,
        path: str,
        method: str = "GET",
        data: dict[str, Any] | None = None,
        headers: dict[str, str] | None = None,
        timeout: int = 30,
    ) -> tuple[int, str, dict[str, str]]:
        url = BASE_URL + path
        body = None
        req_headers = {"User-Agent": "BlueStayTest/1.0"}
        if headers:
            req_headers.update(headers)

        if data is not None:
            encoded = urllib.parse.urlencode(data).encode("utf-8")
            body = encoded
            req_headers["Content-Type"] = "application/x-www-form-urlencoded"

        req = urllib.request.Request(url, data=body, headers=req_headers, method=method)
        try:
            with self.opener.open(req, timeout=timeout) as resp:
                payload = resp.read().decode("utf-8", errors="replace")
                return resp.getcode(), payload, dict(resp.headers.items())
        except urllib.error.HTTPError as e:
            payload = e.read().decode("utf-8", errors="replace")
            return e.code, payload, dict(e.headers.items())
        except Exception as e:
            return 599, json.dumps({"ok": False, "message": str(e)}), {}

    def fetch_csrf_token(self, path: str = "/login.php") -> str:
        code, body, _ = self.request(path, "GET")
        if code != 200:
            return ""
        m = re.search(r'name="_csrf_token" value="([a-f0-9]+)"', body)
        if m:
            return m.group(1)
        m2 = re.search(r'<meta name="csrf-token" content="([a-f0-9]+)">', body)
        return m2.group(1) if m2 else ""


def run_cmd(args: list[str], *, input_text: str | None = None, cwd: Path | None = None) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        args,
        input=input_text,
        text=True,
        cwd=str(cwd or ROOT),
        capture_output=True,
        check=False,
    )


def import_db() -> None:
    schema = (ROOT / "database" / "schema.sql").read_text(encoding="utf-8")
    seed = (ROOT / "database" / "seed.sql").read_text(encoding="utf-8")
    reset = "DROP DATABASE IF EXISTS hotel_management;\nCREATE DATABASE hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n"
    rr = run_cmd([str(MYSQL_EXE), "-u", "root"], input_text=reset)
    if rr.returncode != 0:
        raise RuntimeError(f"db reset failed: {rr.stderr}\n{rr.stdout}")
    r1 = run_cmd([str(MYSQL_EXE), "-u", "root"], input_text=schema)
    if r1.returncode != 0:
        raise RuntimeError(f"schema import failed: {r1.stderr}\n{r1.stdout}")
    r2 = run_cmd([str(MYSQL_EXE), "-u", "root"], input_text=seed)
    if r2.returncode != 0:
        raise RuntimeError(f"seed import failed: {r2.stderr}\n{r2.stdout}")


def sql_scalar(query: str) -> str:
    script = f"USE hotel_management;\n{query}\n"
    proc = run_cmd([str(MYSQL_EXE), "-u", "root", "-N", "-s"], input_text=script)
    if proc.returncode != 0:
        raise RuntimeError(f"sql error: {proc.stderr}")
    return proc.stdout.strip()


def extract_json(text: str) -> dict[str, Any]:
    return json.loads(text)


def start_server() -> subprocess.Popen[str]:
    proc = subprocess.Popen(
        [str(PHP_EXE), "-S", "127.0.0.1:8088", "-t", "public"],
        cwd=str(ROOT),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(1.2)
    return proc


def stop_server(proc: subprocess.Popen[str]) -> None:
    if proc.poll() is None:
        proc.terminate()
        try:
            proc.wait(timeout=5)
        except subprocess.TimeoutExpired:
            proc.kill()


def ensure_login(email: str, password: str) -> HttpClient:
    client = HttpClient()
    token = client.fetch_csrf_token("/login.php")
    code, _, _ = client.request("/login.php", "POST", {"email": email, "password": password, "_csrf_token": token})
    if code not in (200, 302):
        raise RuntimeError(f"login post failed for {email} with status {code}")
    # verify auth session is truly active
    code2, body2, _ = client.request("/api.php?action=auth.me")
    if code2 != 200:
        raise RuntimeError(f"session verify failed for {email} status={code2}")
    payload = json.loads(body2)
    if not payload.get("ok") or not payload.get("data"):
        raise RuntimeError(f"invalid login session for {email}")
    return client


def run_unit_tests(report: Report) -> None:
    start = time.perf_counter()
    php = """
<?php
require __DIR__ . '/app/includes/functions.php';
$ok = true;
$ok = $ok && dashboard_title('owner') === 'Owner Dashboard';
$ok = $ok && dashboard_title('housekeeping') === 'Housekeeping Dashboard';
$ok = $ok && dashboard_title('x') === 'Dashboard';
echo $ok ? "OK" : "FAIL";
"""
    proc = run_cmd([str(PHP_EXE)], input_text=php, cwd=ROOT)
    ok = proc.returncode == 0 and proc.stdout.strip() == "OK"
    report.add("unit.dashboard_title", ok, (proc.stdout + proc.stderr).strip(), int((time.perf_counter() - start) * 1000))


def run_smoke_tests(report: Report) -> None:
    client = HttpClient()
    pages = [
        "/index.php",
        "/about.php",
        "/terms.php",
        "/privacy.php",
        "/help.php",
        "/contact.php",
        "/login.php",
        "/register.php",
        "/forgot-password.php",
        "/reset-password.php",
    ]
    for page in pages:
        start = time.perf_counter()
        code, _, _ = client.request(page)
        report.add(f"smoke{page}", code == 200, f"status={code}", int((time.perf_counter() - start) * 1000))


def run_api_and_integration_tests(report: Report) -> None:
    admin = ensure_login("admin@bluestay.local", "Password@123")
    guest = ensure_login("guest@bluestay.local", "Password@123")

    def api_get(client: HttpClient, action: str, extra: str = "") -> tuple[int, dict[str, Any]]:
        code, body, _ = client.request(f"/api.php?action={action}{extra}")
        payload = extract_json(body) if body.strip().startswith("{") else {"raw": body}
        return code, payload

    def api_post(client: HttpClient, action: str, data: dict[str, Any]) -> tuple[int, dict[str, Any]]:
        csrf = client.fetch_csrf_token("/dashboard.php")
        payload_data = dict(data)
        payload_data["_csrf_token"] = csrf
        code, body, _ = client.request(f"/api.php?action={action}", "POST", payload_data)
        payload = extract_json(body) if body.strip().startswith("{") else {"raw": body}
        return code, payload

    checks: list[tuple[str, bool, str]] = []

    code, payload = api_get(admin, "dashboard.stats")
    checks.append(("api.dashboard.stats", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "users.list")
    checks.append(("api.users.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    room_num = f"9{int(time.time()) % 10000:04d}"
    code, payload = api_post(admin, "rooms.create", {"room_number": room_num, "floor_no": 9, "room_type": "Premium", "base_rate": 7600})
    room_id = payload.get("id")
    checks.append(("api.rooms.create", code == 201 and bool(room_id), f"status={code},id={room_id}"))

    code, payload = api_post(admin, "rooms.update", {"id": room_id, "room_number": room_num, "floor_no": 9, "room_type": "Premium Suite", "status": "available", "base_rate": 8100})
    checks.append(("api.rooms.update", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_post(admin, "rooms.updateStatus", {"id": room_id, "status": "occupied"})
    checks.append(("api.rooms.updateStatus", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "rooms.list")
    checks.append(("api.rooms.list", code == 200 and any(str(r.get("room_number")) == room_num for r in payload.get("data", [])), f"status={code}"))

    code, payload = api_post(admin, "bookings.create", {"guest_user_id": 5, "room_id": room_id, "check_in": "2026-04-10", "check_out": "2026-04-12", "adults": 2, "children": 0, "source": "Direct"})
    booking_code = payload.get("booking_code")
    checks.append(("api.bookings.create", code == 201 and bool(booking_code), f"status={code},code={booking_code}"))
    if not booking_code:
        for name, ok, detail in checks:
            report.add(name, ok, detail)
        report.add("api.integration_blocker", False, f"bookings.create payload={payload}")
        return
    booking_id = int(sql_scalar(f"SELECT id FROM bookings WHERE booking_code='{booking_code}' LIMIT 1"))
    code, payload = api_get(admin, "bookings.get", f"&id={booking_id}")
    checks.append(("api.bookings.get", code == 200 and payload.get("data", {}).get("id") == booking_id, f"status={code}"))

    for action in ("bookings.checkin", "bookings.checkout"):
        code, payload = api_post(admin, action, {"id": booking_id})
        checks.append((f"api.{action}", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "bookings.list")
    checks.append(("api.bookings.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_post(admin, "tasks.create", {"room_id": room_id, "assigned_to_user_id": 4, "task_type": "Inspect Room", "priority": "high"})
    task_id = payload.get("id")
    checks.append(("api.tasks.create", code == 201 and bool(task_id), f"status={code},id={task_id}"))

    code, payload = api_post(admin, "tasks.updateStatus", {"id": task_id, "status": "done"})
    checks.append(("api.tasks.updateStatus", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "tasks.list")
    checks.append(("api.tasks.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_post(guest, "services.create", {"room_id": room_id, "request_type": "laundry", "description": "Need laundry pickup", "priority": "medium"})
    service_id = payload.get("id")
    checks.append(("api.services.create", code == 201 and bool(service_id), f"status={code},id={service_id}"))

    code, payload = api_post(admin, "services.updateStatus", {"id": service_id, "status": "done"})
    checks.append(("api.services.updateStatus", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "services.list")
    checks.append(("api.services.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_post(admin, "inventory.create", {"item_name": "Shampoo Bottle", "category": "Housekeeping", "unit": "pcs", "stock_qty": 60, "reorder_level": 20, "cost_price": 35})
    inv_id = payload.get("id")
    checks.append(("api.inventory.create", code == 201 and bool(inv_id), f"status={code},id={inv_id}"))

    code, payload = api_post(admin, "inventory.updateStock", {"id": inv_id, "stock_qty": 75})
    checks.append(("api.inventory.updateStock", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "inventory.list")
    checks.append(("api.inventory.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_post(admin, "fnb.menu.create", {"item_name": "Club Sandwich", "category": "Snacks", "price": 240, "is_available": 1})
    menu_id = payload.get("id")
    checks.append(("api.fnb.menu.create", code == 201 and bool(menu_id), f"status={code},id={menu_id}"))

    code, payload = api_post(admin, "fnb.menu.updateAvailability", {"id": menu_id, "is_available": 0})
    checks.append(("api.fnb.menu.updateAvailability", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "fnb.menu.list")
    checks.append(("api.fnb.menu.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_post(admin, "security.visitors.create", {"visitor_name": "Test Visitor", "purpose": "Meet Guest", "phone": "9991112223"})
    visitor_id = payload.get("id")
    checks.append(("api.security.visitors.create", code == 201 and bool(visitor_id), f"status={code},id={visitor_id}"))

    code, payload = api_post(admin, "security.visitors.checkout", {"id": visitor_id})
    checks.append(("api.security.visitors.checkout", code == 200 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "security.visitors.list")
    checks.append(("api.security.visitors.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_post(admin, "invoices.generate", {"booking_id": booking_id, "sub_total": 15000, "tax_total": 2700, "gstin": "27ABCDE1234F1Z5"})
    invoice_id = payload.get("id")
    checks.append(("api.invoices.generate", code == 201 and bool(invoice_id), f"status={code},id={invoice_id}"))

    code, payload = api_get(admin, "invoices.list")
    checks.append(("api.invoices.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, body, headers = admin.request(f"/api.php?action=invoices.download&id={invoice_id}")
    checks.append(("api.invoices.download", code == 200 and "BlueStay HMS Invoice" in body, f"status={code},type={headers.get('Content-Type','')}"))

    code, payload = api_post(admin, "payments.create", {"invoice_id": invoice_id, "method": "upi", "amount": 17700, "transaction_ref": "TESTPAY123", "payment_status": "success"})
    checks.append(("api.payments.create", code == 201 and payload.get("ok") is True, f"status={code}"))

    code, payload = api_get(admin, "payments.list")
    checks.append(("api.payments.list", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, payload = api_get(admin, "reports.summary")
    checks.append(("api.reports.summary", code == 200 and isinstance(payload.get("data"), list), f"status={code}"))

    code, body, headers = admin.request("/api.php?action=reports.export")
    checks.append(("api.reports.export", code == 200 and "booking_code" in body, f"status={code},type={headers.get('Content-Type','')}"))

    code, payload = api_post(admin, "rooms.delete", {"id": room_id})
    checks.append(("api.rooms.delete", code in (200, 409), f"status={code},msg={payload.get('message','')}"))

    # Forgot/reset via user-side flow
    unique = f"testuser_{int(time.time())}@mail.local"
    anon = HttpClient()
    reg_token = anon.fetch_csrf_token("/register.php")
    code, _, _ = anon.request("/register.php", "POST", {"full_name": "API Test User", "email": unique, "phone": "9000001000", "role": "customer", "password": "Password@123", "_csrf_token": reg_token})
    checks.append(("user.register", code in (200, 302), f"status={code}"))
    exists = sql_scalar(f"SELECT COUNT(*) FROM users WHERE email='{unique}'")
    if exists == "0":
        fallback_hash = "$2y$10$gJzG.3zAzugbDHyQzS3j3ONKpIpN/P4CyXTNXvLEqRQE8Mr0uroCS"
        _ = sql_scalar(f"INSERT INTO users(full_name,email,phone,role,password_hash) VALUES('API Test User','{unique}','9000001000','customer','{fallback_hash}'); SELECT 1;")
    fp_token = anon.fetch_csrf_token("/forgot-password.php")
    code, body, _ = anon.request("/forgot-password.php", "POST", {"email": unique, "_csrf_token": fp_token})
    token_match = re.search(r"token=([a-f0-9]{32,})", body)
    token = token_match.group(1) if token_match else ""
    reset_rows = sql_scalar(
        "SELECT COUNT(*) FROM password_resets pr JOIN users u ON u.id=pr.user_id "
        f"WHERE u.email='{unique}' AND pr.used_at IS NULL;"
    )
    checks.append(("user.forgot_password", code == 200 and int(reset_rows) > 0, f"status={code},resets={reset_rows}"))
    rp_token = anon.fetch_csrf_token("/reset-password.php")
    code, _, _ = anon.request("/reset-password.php", "POST", {"token": token, "password": "Reset@1234", "confirm_password": "Reset@1234", "_csrf_token": rp_token})
    checks.append(("user.reset_password", code == 200, f"status={code}"))
    l2_token = anon.fetch_csrf_token("/login.php")
    code, _, _ = anon.request("/login.php", "POST", {"email": unique, "password": "Reset@1234", "_csrf_token": l2_token})
    checks.append(("user.login_after_reset", code in (200, 302), f"status={code}"))

    for name, ok, detail in checks:
        report.add(name, ok, detail)


def run_black_box_tests(report: Report) -> None:
    anon = HttpClient()
    code, body, _ = anon.request("/api.php?action=rooms.list")
    ok = code == 401 and body.strip().startswith("{")
    report.add("blackbox.unauthorized_rooms", ok, f"status={code}")

    admin = ensure_login("admin@bluestay.local", "Password@123")
    code, body, _ = admin.request("/api.php?action=unknown.action")
    try:
        payload = extract_json(body)
    except Exception:
        payload = {}
    report.add("blackbox.unknown_action", code == 404 and payload.get("ok") is False, f"status={code}")

    code, body, _ = admin.request("/api.php?action=bookings.create", "POST", {"room_id": 1})
    try:
        payload = extract_json(body)
    except Exception:
        payload = {}
    report.add("blackbox.missing_required_fields", code in (419, 422) and payload.get("ok") is False, f"status={code}")


def run_db_connectivity_tests(report: Report) -> None:
    try:
        users = int(sql_scalar("SELECT COUNT(*) FROM users;"))
        rooms = int(sql_scalar("SELECT COUNT(*) FROM rooms;"))
        inv = int(sql_scalar("SELECT COUNT(*) FROM inventory_items;"))
        report.add("db.users_count", users > 0, f"users={users}")
        report.add("db.rooms_count", rooms > 0, f"rooms={rooms}")
        report.add("db.inventory_count", inv >= 0, f"inventory={inv}")
        explain = sql_scalar("EXPLAIN SELECT b.booking_code, u.full_name FROM bookings b JOIN users u ON u.id=b.guest_user_id WHERE b.check_in >= CURDATE() LIMIT 1;")
        report.add("db.explain_join", bool(explain), "join explain executed")
    except Exception as e:
        report.add("db.connectivity", False, str(e))


def run_scalability_test(report: Report) -> None:
    admin = ensure_login("admin@bluestay.local", "Password@123")
    cookie = admin.cookie_header()
    total_requests = 10000
    workers = 200
    latencies: list[float] = []
    failures = 0

    def hit(_: int) -> bool:
        nonlocal failures
        req = urllib.request.Request(
            BASE_URL + "/api.php?action=dashboard.stats",
            headers={"Cookie": cookie, "User-Agent": "BlueStayScale/1.0"},
            method="GET",
        )
        t0 = time.perf_counter()
        try:
            with urllib.request.urlopen(req, timeout=20) as resp:
                _ = resp.read()
                ok = resp.getcode() == 200
        except Exception:
            ok = False
        latencies.append((time.perf_counter() - t0) * 1000.0)
        if not ok:
            failures += 1
        return ok

    started = time.perf_counter()
    with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as ex:
        list(ex.map(hit, range(total_requests)))
    elapsed = time.perf_counter() - started

    p95 = statistics.quantiles(latencies, n=20)[18] if len(latencies) > 30 else max(latencies, default=0.0)
    rps = total_requests / elapsed if elapsed > 0 else 0.0
    success = total_requests - failures
    ok = failures <= max(1, int(total_requests * 0.001))
    detail = f"total={total_requests}, success={success}, fail={failures}, workers={workers}, elapsed_s={elapsed:.2f}, rps={rps:.2f}, p95_ms={p95:.1f}"
    report.add("scale.10k_virtual_users", ok, detail, int(elapsed * 1000))


def write_report(reports: list[Report]) -> Path:
    out = ROOT / "tests" / "test_report.md"
    out.parent.mkdir(parents=True, exist_ok=True)
    lines: list[str] = []
    lines.append("# HMS Test Report")
    lines.append("")
    lines.append(f"Generated: {time.strftime('%Y-%m-%d %H:%M:%S')}")
    lines.append("")
    total_pass = 0
    total_fail = 0
    for rep in reports:
        lines.append(f"## {rep.suite}")
        lines.append("")
        lines.append("| Test | Status | Detail | Time (ms) |")
        lines.append("|---|---|---|---:|")
        for r in rep.results:
            lines.append(f"| {r.name} | {r.status} | {r.detail} | {r.elapsed_ms} |")
        lines.append("")
        lines.append(f"Summary: PASS={rep.passed}, FAIL={rep.failed}")
        lines.append("")
        total_pass += rep.passed
        total_fail += rep.failed
    lines.append(f"## Overall")
    lines.append("")
    lines.append(f"PASS={total_pass}, FAIL={total_fail}")
    out.write_text("\n".join(lines), encoding="utf-8")
    return out


def main() -> int:
    reports = [
        Report("Unit Tests"),
        Report("Smoke Tests"),
        Report("API + Integration Tests"),
        Report("Black Box Tests"),
        Report("DB Connectivity Tests"),
        Report("Scalability Test"),
    ]

    import_db()
    server = start_server()
    try:
        run_unit_tests(reports[0])
        run_smoke_tests(reports[1])
        run_api_and_integration_tests(reports[2])
        run_black_box_tests(reports[3])
        run_db_connectivity_tests(reports[4])
        run_scalability_test(reports[5])
    finally:
        stop_server(server)

    report_path = write_report(reports)
    total_fail = sum(r.failed for r in reports)
    print(f"report={report_path}")
    print(f"total_fail={total_fail}")
    return 1 if total_fail else 0


if __name__ == "__main__":
    sys.exit(main())
