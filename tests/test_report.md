# HMS Test Report

Generated: 2026-04-03 19:10:23

## Unit Tests

| Test | Status | Detail | Time (ms) |
|---|---|---|---:|
| unit.dashboard_title | PASS | OK | 50 |

Summary: PASS=1, FAIL=0

## Smoke Tests

| Test | Status | Detail | Time (ms) |
|---|---|---|---:|
| smoke/index.php | PASS | status=200 | 10 |
| smoke/about.php | PASS | status=200 | 4 |
| smoke/terms.php | PASS | status=200 | 27 |
| smoke/privacy.php | PASS | status=200 | 15 |
| smoke/help.php | PASS | status=200 | 4 |
| smoke/contact.php | PASS | status=200 | 27 |
| smoke/login.php | PASS | status=200 | 2 |
| smoke/register.php | PASS | status=200 | 2 |
| smoke/forgot-password.php | PASS | status=200 | 2 |
| smoke/reset-password.php | PASS | status=200 | 2 |

Summary: PASS=10, FAIL=0

## API + Integration Tests

| Test | Status | Detail | Time (ms) |
|---|---|---|---:|
| api.dashboard.stats | PASS | status=200 | 0 |
| api.users.list | PASS | status=200 | 0 |
| api.rooms.create | PASS | status=201,id=6 | 0 |
| api.rooms.update | PASS | status=200 | 0 |
| api.rooms.updateStatus | PASS | status=200 | 0 |
| api.rooms.list | PASS | status=200 | 0 |
| api.bookings.create | PASS | status=201,code=BK2604031827 | 0 |
| api.bookings.get | PASS | status=200 | 0 |
| api.bookings.checkin | PASS | status=200 | 0 |
| api.bookings.checkout | PASS | status=200 | 0 |
| api.bookings.list | PASS | status=200 | 0 |
| api.tasks.create | PASS | status=201,id=3 | 0 |
| api.tasks.updateStatus | PASS | status=200 | 0 |
| api.tasks.list | PASS | status=200 | 0 |
| api.services.create | PASS | status=201,id=3 | 0 |
| api.services.updateStatus | PASS | status=200 | 0 |
| api.services.list | PASS | status=200 | 0 |
| api.inventory.create | PASS | status=201,id=4 | 0 |
| api.inventory.updateStock | PASS | status=200 | 0 |
| api.inventory.list | PASS | status=200 | 0 |
| api.fnb.menu.create | PASS | status=201,id=4 | 0 |
| api.fnb.menu.updateAvailability | PASS | status=200 | 0 |
| api.fnb.menu.list | PASS | status=200 | 0 |
| api.security.visitors.create | PASS | status=201,id=3 | 0 |
| api.security.visitors.checkout | PASS | status=200 | 0 |
| api.security.visitors.list | PASS | status=200 | 0 |
| api.invoices.generate | PASS | status=201,id=3 | 0 |
| api.invoices.list | PASS | status=200 | 0 |
| api.invoices.download | PASS | status=200,type=text/html; charset=utf-8 | 0 |
| api.payments.create | PASS | status=201 | 0 |
| api.payments.list | PASS | status=200 | 0 |
| api.reports.summary | PASS | status=200 | 0 |
| api.reports.export | PASS | status=200,type=text/csv; charset=utf-8 | 0 |
| api.rooms.delete | PASS | status=409,msg=Room has linked records; marked as blocked instead | 0 |
| user.register | PASS | status=200 | 0 |
| user.forgot_password | PASS | status=200,resets=1 | 0 |
| user.reset_password | PASS | status=200 | 0 |
| user.login_after_reset | PASS | status=200 | 0 |

Summary: PASS=38, FAIL=0

## Black Box Tests

| Test | Status | Detail | Time (ms) |
|---|---|---|---:|
| blackbox.unauthorized_rooms | PASS | status=401 | 0 |
| blackbox.unknown_action | PASS | status=404 | 0 |
| blackbox.missing_required_fields | PASS | status=422 | 0 |

Summary: PASS=3, FAIL=0

## DB Connectivity Tests

| Test | Status | Detail | Time (ms) |
|---|---|---|---:|
| db.users_count | PASS | users=6 | 0 |
| db.rooms_count | PASS | rooms=6 | 0 |
| db.inventory_count | PASS | inventory=4 | 0 |
| db.explain_join | PASS | join explain executed | 0 |

Summary: PASS=4, FAIL=0

## Scalability Test

| Test | Status | Detail | Time (ms) |
|---|---|---|---:|
| scale.10k_virtual_users | PASS | total=10000, success=9998, fail=2, workers=200, elapsed_s=64.29, rps=155.55, p95_ms=1840.6 | 64288 |

Summary: PASS=1, FAIL=0

## Overall

PASS=57, FAIL=0