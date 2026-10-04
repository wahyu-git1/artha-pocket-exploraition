# ✅ CatatDuit — Backend Developer Checklist
> Role: **Backend Developer**
> Stack: Laravel | PostgreSQL | Redis
> Base API: `/api/v1`

---

## 🗂️ FASE 0 — Setup & Fondasi Proyek

### 0.1 Inisialisasi Project
- [x] Install Laravel fresh project (Laravel 13 + PHP 8.4)
- [x] Setup `.env` (database PostgreSQL, Redis, APP_KEY)
- [x] Install package tambahan:
  - [x] `laravel/sanctum` v4.3 — auth token
  - [x] `spatie/laravel-query-builder` v7.3 — filter & sort
  - [x] `spatie/laravel-data` v4.23 — DTO / typed request
  - [x] `dedoc/scramble` v0.13 — dokumentasi OpenAPI
- [x] Setup queue driver ke Redis (`QUEUE_CONNECTION=redis`)
- [x] Setup CORS (`config/cors.php` — `allowed_origins: ['*']`)
- [x] Setup rate limiting (`$middleware->throttleApi()`)

### 0.2 Struktur Folder
- [x] Buat struktur folder: `app/Http/Controllers/Api/V1/` (+ subfolder Auth)
- [x] Buat folder: `app/Services/`
- [x] Buat folder: `app/DTOs/`
- [x] Buat folder: `app/Actions/`
- [x] Buat folder: `app/Enums/`
- [x] Buat folder: `app/Http/Requests/Api/V1/`
- [x] Buat folder: `app/Http/Resources/V1/`
- [x] Buat folder: `app/Traits/`
- [x] Buat base `ApiResponse` helper / trait (`app/Traits/ApiResponse.php`)
- [x] Buat `BaseController` untuk semua controller V1

### 0.3 Response Format Standard
- [x] Buat `ApiResponse` trait/helper (`app/Traits/ApiResponse.php`):
  ```json
  // Success
  { "data": ..., "meta": { ... } }

  // Error
  { "error": { "code": "VALIDATION_ERROR", "message": "...", "details": [] } }
  ```
- [x] Buat custom Exception Handler di `bootstrap/app.php` (Laravel 13 style)
  - Handles: `ValidationException`, `AuthenticationException`, `NotFoundHttpException`, `HttpException`
- [x] Daftarkan semua custom error code di `app/Enums/ErrorCode.php`
- [x] Buat `routes/api.php` dengan **66 route** untuk semua fase
- [x] Buat stub controller untuk semua 18 controller

---

## 🗄️ FASE 1.A — Database: Migration & Model

> ⚠️ **Urutan migration penting!** Buat sesuai urutan di bawah karena ada foreign key.

### Migration (urutan berdasarkan dependency)
- [x] `create_users_table`
  - `id` UUID PK, `name`, `email` UNIQUE, `password_hash`
  - `marital_status` ENUM(single, married) nullable
  - `dependents_count` SMALLINT default 0
  - `income_stability` ENUM(stable, variable) nullable
  - `has_installments` BOOLEAN default false
  - `timezone` VARCHAR(40) default `Asia/Jakarta`
  - `primary_income_id` UUID nullable (FK ditambah belakangan)
  - `created_at`, `updated_at`, `deleted_at`

- [x] `create_refresh_tokens_table`
  - `id` UUID PK, `user_id` FK
  - `token_hash` VARCHAR(255)
  - `expires_at` TIMESTAMPTZ, `revoked_at` TIMESTAMPTZ nullable

- [x] `create_devices_table`
  - `id` UUID PK, `user_id` FK
  - `push_token` VARCHAR(255) UNIQUE
  - `platform` ENUM(android, ios, web)
  - `device_name` VARCHAR(100), `last_seen_at` TIMESTAMPTZ

- [x] `create_categories_table`
  - `id` UUID PK, `user_id` UUID nullable FK (NULL = default sistem)
  - `name` VARCHAR(60)
  - `type` ENUM(expense, income)
  - `bucket` ENUM(need, want) nullable
  - `icon` VARCHAR(40), `color` CHAR(7)
  - `is_default` BOOLEAN
  - UNIQUE(`user_id`, `name`, `type`)

- [x] `create_category_rules_table`
  - `id` UUID PK
  - `keyword` VARCHAR(60), `category_id` FK, `priority` SMALLINT
  - *(tidak pakai deleted_at)*

- [x] `create_incomes_table`
  - `id` UUID PK, `user_id` FK
  - `name` VARCHAR(100)
  - `default_amount` BIGINT nullable
  - `frequency` ENUM(monthly, weekly, irregular)
  - `pay_day` SMALLINT nullable
  - `is_primary` BOOLEAN, `is_active` BOOLEAN default true

- [x] `add_primary_income_id_fk_to_users_table`
  - Tambahkan FK constraint `users.primary_income_id → incomes.id`

- [x] `create_income_receipts_table`
  - `id` UUID PK, `income_id` FK, `user_id` FK
  - `category_id` UUID FK nullable
  - `amount` BIGINT, `received_at` DATE
  - `note` VARCHAR(255) nullable, `raw_input` TEXT nullable
  - `client_id` UUID, UNIQUE(`user_id`, `client_id`)

- [x] `create_user_category_preferences_table`
  - `id` UUID PK, `user_id` FK
  - `keyword` VARCHAR(60), `category_id` FK
  - `hit_count` INT default 0
  - UNIQUE(`user_id`, `keyword`)

- [x] `create_expenses_table`
  - `id` UUID PK, `user_id` FK, `income_id` FK, `category_id` FK
  - `item` VARCHAR(150), `amount` BIGINT
  - `spent_at` DATE, `note` VARCHAR(255) nullable
  - `raw_input` TEXT nullable
  - `confidence_score` NUMERIC(3,2) nullable
  - `source` ENUM(manual, smart_entry, sync)
  - `client_id` UUID, UNIQUE(`user_id`, `client_id`)
  - Index: (`user_id`, `spent_at`), (`user_id`, `category_id`)

- [x] `create_savings_goals_table`
  - `id` UUID PK, `user_id` FK
  - `name` VARCHAR(100), `price` BIGINT
  - `saved_amount` BIGINT default 0
  - `target_date` DATE, `monthly_amount` BIGINT
  - `status` ENUM(active, paused, completed, cancelled)
  - `completed_at` TIMESTAMPTZ nullable

- [x] `create_goal_deposits_table`
  - `id` UUID PK, `goal_id` FK, `income_id` FK, `user_id` FK
  - `amount` BIGINT, `deposited_at` DATE
  - `note` VARCHAR(255) nullable
  - `client_id` UUID, UNIQUE(`user_id`, `client_id`)

- [x] `create_emergency_funds_table`
  - `id` UUID PK, `user_id` FK UNIQUE
  - `multiplier` SMALLINT (3–12)
  - `avg_monthly_expense` BIGINT, `target_amount` BIGINT
  - `saved_amount` BIGINT default 0
  - `plan_months` SMALLINT, `monthly_amount` BIGINT
  - `status` ENUM(active, paused, completed)

- [x] `create_emergency_transactions_table`
  - `id` UUID PK, `fund_id` FK, `income_id` FK nullable, `user_id` FK
  - `type` ENUM(deposit, withdrawal)
  - `amount` BIGINT, `reason` VARCHAR(255) nullable
  - `occurred_at` DATE
  - `client_id` UUID, UNIQUE(`user_id`, `client_id`)

- [x] `create_allocation_plans_table`
  - `id` UUID PK, `user_id` FK
  - `month` CHAR(7) (`YYYY-MM`), `income_id` FK nullable
  - `template_code` VARCHAR(20) nullable
  - UNIQUE(`user_id`, `month`, `income_id`)

- [x] `create_allocation_items_table`
  - `id` UUID PK, `plan_id` FK
  - `bucket` ENUM(need, want, saving, investment)
  - `percent` NUMERIC(5,2)
  - UNIQUE(`plan_id`, `bucket`)
  - *(tidak pakai deleted_at)*

- [x] `create_category_bucket_mappings_table`
  - `id` UUID PK, `user_id` FK, `category_id` FK
  - `bucket` ENUM(need, want, saving, investment)
  - UNIQUE(`user_id`, `category_id`)

- [x] `create_investments_table`
  - `id` UUID PK, `user_id` FK, `income_id` FK nullable
  - `instrument_type` ENUM(mutual_fund, gold, stock, bond, deposit, crypto, other)
  - `instrument_name` VARCHAR(100), `amount` BIGINT
  - `invested_at` DATE, `note` VARCHAR(255) nullable
  - `client_id` UUID, UNIQUE(`user_id`, `client_id`)

- [x] `create_notifications_table`
  - `id` UUID PK, `user_id` FK
  - `type` VARCHAR(40), `title` VARCHAR(120)
  - `body` VARCHAR(255), `data` JSONB
  - `read_at` TIMESTAMPTZ nullable

### Models (Eloquent)
- [x] `User` — relasi ke semua entitas miliknya
- [x] `RefreshToken`
- [x] `Device`
- [x] `Category` — scope: default & kustom
- [x] `CategoryRule`
- [x] `UserCategoryPreference`
- [x] `CategoryBucketMapping`
- [x] `Income` — accessor: `balance`
- [x] `IncomeReceipt`
- [x] `Expense` — scope: by period, by category
- [x] `SavingsGoal` — accessor: `progress_percent`, `months_left`
- [x] `GoalDeposit`
- [x] `EmergencyFund` — accessor: `progress_percent`
- [x] `EmergencyTransaction`
- [x] `AllocationPlan`
- [x] `AllocationItem`
- [x] `Investment`
- [x] `Notification`

---

## 🌱 FASE 1.B — Database Seeder

- [x] `CategorySeeder` — 12 kategori expense + 7 kategori income default
  - Expense: Makanan & Minuman, Transportasi, Belanja, Tagihan & Utilitas, Kesehatan, Hiburan, Pendidikan, Kecantikan & Perawatan, Olahraga, Rumah Tangga, Sosial & Hadiah, Lain-lain
  - Income: Gaji, Freelance, Bisnis, Investasi, Hadiah, Pinjaman Kembali, Lain-lain
- [x] `CategoryRuleSeeder` — kamus kata kunci awal (bakso, kopi, ojek, bensin, listrik, dll)
- [x] `AllocationTemplateSeeder` — template: 50-30-20, 60-20-20, 70-20-10

---

## 🔐 FASE 1.C — Auth & Profil

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/v1/auth/register` | Daftar akun baru |
| `POST` | `/api/v1/auth/login` | Masuk, dapat access + refresh token |
| `POST` | `/api/v1/auth/refresh` | Perbarui access token |
| `POST` | `/api/v1/auth/logout` | Cabut refresh token |
| `GET` | `/api/v1/users/me` | Data profil pengguna |
| `PATCH` | `/api/v1/users/me` | Update profil |
| `DELETE` | `/api/v1/users/me` | Hapus akun dan seluruh data |

### Checklist Implementasi
- [x] `AuthController@register` — validasi, hash password, buat user, issue token
- [x] `AuthController@login` — validasi kredensial, buat refresh token, kembalikan token pair
- [x] `AuthController@refresh` — validasi refresh token, rotasi (cabut lama, buat baru)
- [x] `AuthController@logout` — cabut refresh token
- [x] `UserController@me` — tampilkan profil + primary_income_id
- [x] `UserController@update` — update profil (semua field opsional)
- [x] `UserController@destroy` — konfirmasi password, soft delete user + cascade data
- [x] Service: `TokenService` — generate, verify, rotate refresh token
- [x] Middleware: `auth:sanctum` untuk semua route kecuali auth
- [x] Rate limiting: login maksimal 5x/menit per IP

---

## 💰 FASE 1.D — Pendapatan (Income)

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/v1/incomes` | List semua sumber pendapatan |
| `POST` | `/api/v1/incomes` | Tambah sumber pendapatan |
| `GET` | `/api/v1/incomes/{id}` | Detail + balance pendapatan |
| `PATCH` | `/api/v1/incomes/{id}` | Update pendapatan |
| `DELETE` | `/api/v1/incomes/{id}` | Hapus (hanya jika belum ada transaksi) |
| `GET` | `/api/v1/incomes/{id}/receipts` | List penerimaan dari sumber ini |
| `POST` | `/api/v1/incomes/{id}/receipts` | Tambah penerimaan |
| `PATCH` | `/api/v1/incomes/{id}/receipts/{rid}` | Update penerimaan |
| `DELETE` | `/api/v1/incomes/{id}/receipts/{rid}` | Hapus penerimaan |

### Checklist Implementasi
- [x] `IncomeController` — CRUD income
- [x] `IncomeReceiptController` — CRUD receipt
- [x] Service: `IncomeBalanceService@calculate(incomeId)`:
  ```
  balance = SUM(receipts) - SUM(expenses) - SUM(goal_deposits)
           - SUM(emergency deposits) + SUM(emergency withdrawals kembali) - SUM(investments)
  ```
- [x] Validasi: hanya 1 `is_primary = true` per user
- [x] Aturan hapus: `409 HAS_TRANSACTIONS` jika ada expense/receipt terkait
- [x] Response `/incomes/{id}` menyertakan `balance` yang dihitung real-time
- [x] Query filter: `?is_active=true/false`

---

## 🏷️ FASE 1.E — Kategori

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/v1/categories` | List kategori default + kustom user |
| `POST` | `/api/v1/categories` | Buat kategori kustom |
| `PATCH` | `/api/v1/categories/{id}` | Edit kategori (nama, icon, color, bucket) |
| `DELETE` | `/api/v1/categories/{id}` | Hapus kategori (dengan opsi reassign) |

### Checklist Implementasi
- [x] `CategoryController` — CRUD
- [x] Scope: gabungkan kategori `user_id = NULL` (default) + milik user yang login
- [x] Guard: `403 DEFAULT_READONLY` jika user coba edit nama kategori default
- [x] Guard: `409 IN_USE` jika hapus kategori yang masih dipakai expense, tanpa `?reassign_to`
- [x] Jika ada `?reassign_to`, update semua expense ke kategori baru dalam satu transaksi
- [x] Filter: `?type=expense` atau `?type=income`

---

## 📝 FASE 1.F — Pengeluaran (Expense)

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/v1/expenses` | List pengeluaran (dengan filter) |
| `POST` | `/api/v1/expenses` | Tambah pengeluaran |
| `POST` | `/api/v1/expenses/bulk` | Tambah banyak sekaligus (maks 20) |
| `GET` | `/api/v1/expenses/{id}` | Detail pengeluaran |
| `PATCH` | `/api/v1/expenses/{id}` | Update pengeluaran |
| `DELETE` | `/api/v1/expenses/{id}` | Hapus pengeluaran |

### Checklist Implementasi
- [x] `ExpenseController` — full CRUD
- [x] Filter query: `from`, `to`, `category_id`, `income_id`, `q` (search item), `min_amount`, `max_amount`
- [x] Sorting: `?sort=-spent_at` (default descending)
- [x] Paginasi: `?page=1&limit=20`, meta: `total`, `total_amount`
- [x] Idempotensi: cek `client_id` sebelum insert — jika sudah ada, kembalikan data lama (`200`)
- [x] Bulk: atomic (semua gagal jika satu gagal), maks 20 item
- [x] Response POST menyertakan `income_balance` terbaru

---

## 🤖 FASE 1.G — Smart Entry

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/v1/smart-entry/parse` | Parse kalimat bebas ke struktur transaksi |
| `POST` | `/api/v1/smart-entry/feedback` | Simpan koreksi kategori user |

### Checklist Implementasi
- [x] `SmartEntryController@parse`
- [x] Service: `SmartEntryParser`:
  - [x] **Parser nominal**: kenali `10k`, `10rb`, `10 ribu`, `1,5jt`, `Rp 10.000`
  - [x] **Deteksi tipe**: `beli/bayar/jajan` → expense; `gajian/terima/dapat` → income
  - [x] **Parser tanggal**: `kemarin`, `tadi pagi`, default hari ini
  - [x] **Ekstrak item**: pisah kata kunci item dari kalimat
  - [x] **Kategorisasi**: cek `user_category_preferences` dulu → lalu `category_rules`
  - [x] **Multi-item**: kalimat "bakso 10k sama es teh 5k" → pecah jadi 2 item
  - [x] **Confidence score**: hitung berdasarkan kejelasan parsing
  - [x] Jika confidence < 0.7: `needs_review = true`, tampilkan `suggestions[]` (2–3 pilihan)
- [x] `SmartEntryController@feedback`:
  - Simpan ke `user_category_preferences`
  - Increment `hit_count` jika keyword sudah ada
- [x] Validasi: `text` maks 200 karakter, wajib ada nominal (`422 PARSE_NO_AMOUNT`)
- [x] **Endpoint hanya parse, TIDAK menyimpan** — penyimpanan lewat `POST /expenses`
- [x] Target waktu respons: < 2 detik

---

## 📊 FASE 1.H — Ringkasan & Laporan

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/v1/summary` | Ringkasan keuangan bulanan |
| `GET` | `/api/v1/reports/categories` | Laporan per kategori |
| `GET` | `/api/v1/reports/monthly` | Tren bulanan (beberapa bulan) |
| `GET` | `/api/v1/exports/transactions` | Export CSV |

### Checklist Implementasi
- [x] `SummaryController@index`:
  - Response: `total_income`, `total_expense`, `balance`
  - `per_income[]`: `{income_id, name, received, spent, balance}`
  - `per_category[]`: nominal & persentase per kategori
  - Filter: `?month=YYYY-MM`
- [x] `ReportController@categories`: filter `from`, `to`, `type`
- [x] `ReportController@monthly`: default 6 bulan terakhir
- [x] `ExportController@transactions`: generate CSV, return file download
- [x] Service: `SummaryService` — query aggregasi dari DB

---

## 🔄 FASE 1.I — Sinkronisasi Offline

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/v1/sync/push` | Kirim perubahan dari klien |
| `GET` | `/api/v1/sync/pull` | Ambil perubahan dari server |

### Checklist Implementasi
- [x] `SyncController@push`:
  - Proses `changes[]`: `{entity, op (create/update/delete), client_id, data, updated_at}`
  - Idempotensi: cek `client_id` untuk mencegah duplikat
  - Konflik: last-write-wins berdasarkan `updated_at`
  - Response per item: `{client_id, status (applied/conflict/rejected), server_id, error?}`
- [x] `SyncController@pull`:
  - Query semua entitas milik user yang `updated_at > since`
  - Sertakan data yang di-soft-delete (untuk sync hapus di klien)
  - Response: `changes[]`, `server_time`, `has_more`, `next_cursor`
- [x] Validasi `since` format ISO 8601 / timestamp string

---

## 🎯 FASE 2 — Target Menabung

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/v1/savings-goals/simulate` | Simulasi target (tanpa simpan) |
| `GET` | `/api/v1/savings-goals` | List semua target |
| `POST` | `/api/v1/savings-goals` | Buat target baru |
| `GET` | `/api/v1/savings-goals/{id}` | Detail target + deposit |
| `PATCH` | `/api/v1/savings-goals/{id}` | Update target |
| `DELETE` | `/api/v1/savings-goals/{id}` | Hapus target |
| `POST` | `/api/v1/savings-goals/{id}/deposits` | Catat setoran |
| `GET` | `/api/v1/savings-goals/{id}/deposits` | List setoran |

### Checklist Implementasi
- [x] `SavingsGoalController` — CRUD
- [x] `GoalDepositController` — tambah & list setoran
- [x] Service: `SavingsGoalService`:
  - [x] Rumus: `monthly_amount = ceil((price - saved_amount) / months_left)`
  - [x] `months_left` = dari bulan berjalan ke bulan target_date (minimal 1)
  - [x] Split proporsional per income (berdasarkan ratio balance masing-masing)
  - [x] Warning jika `monthly_amount > sisa_saldo_bulanan`
  - [x] Suggest `alternative_dates[]` jika tidak realistis
  - [x] Jika `price - saved_amount <= 0` → auto set `status = completed`
- [x] Notifikasi: trigger ketika target tercapai
- [x] Saat setoran: update `saved_amount` dalam satu DB transaction
- [x] Saat hapus goal: kembalikan `saved_amount` ke `income_balance` (catatan audit)
- [x] Pendapatan tidak tetap → pakai rata-rata 3 bulan terakhir

---

## 🆘 FASE 3 — Dana Darurat

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/v1/emergency-fund/recommendation` | Hitung rekomendasi (tanpa simpan) |
| `POST` | `/api/v1/emergency-fund` | Buat dana darurat |
| `GET` | `/api/v1/emergency-fund` | Status dana darurat |
| `PATCH` | `/api/v1/emergency-fund` | Update rencana |
| `POST` | `/api/v1/emergency-fund/deposits` | Setoran ke dana darurat |
| `POST` | `/api/v1/emergency-fund/withdrawals` | Penarikan dana darurat |
| `GET` | `/api/v1/emergency-fund/transactions` | Riwayat transaksi dana darurat |

### Checklist Implementasi
- [x] `EmergencyFundController` — semua endpoint
- [x] Service: `EmergencyFundService`:
  - [x] Algoritma multiplier:
    - Base: 3x
    - +1 jika menikah
    - +1 per tanggungan (maks +3)
    - +2 jika `income_stability = variable`
    - +1 jika punya cicilan
    - Maksimal: 12x
  - [x] `avg_monthly_expense` = rata-rata pengeluaran 3 bulan terakhir dari DB
  - [x] Kembalikan `reasoning[]` — alasan tiap penambahan multiplier
  - [x] `options[]` — pilihan 12, 24, 36 bulan dengan `monthly_amount` masing-masing
- [x] Validasi: hanya 1 dana darurat per user (`409 ALREADY_EXISTS`)
- [x] Saat withdrawal: wajib `reason`, kembalikan `refill_plan` (jadwal pengisian kembali)
- [x] Background job: cek tiap bulan jika `avg_monthly_expense` berubah > 15%, kirim notifikasi

---

## 📐 FASE 4 — Alokasi & Investasi

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/api/v1/allocations/templates` | List template alokasi |
| `GET` | `/api/v1/allocations/recommendation` | Saran alokasi berdasarkan histori |
| `GET` | `/api/v1/allocations` | Rencana alokasi bulan ini |
| `PUT` | `/api/v1/allocations` | Set/update rencana alokasi |
| `GET` | `/api/v1/allocations/summary` | Realisasi vs rencana |
| `GET` | `/api/v1/allocations/category-mapping` | Pemetaan kategori ke pos |
| `PUT` | `/api/v1/allocations/category-mapping` | Update pemetaan |
| `GET` | `/api/v1/investments` | List investasi |
| `POST` | `/api/v1/investments` | Catat investasi |
| `PATCH` | `/api/v1/investments/{id}` | Update investasi |
| `DELETE` | `/api/v1/investments/{id}` | Hapus investasi |

### Checklist Implementasi
- [x] `AllocationController` — semua endpoint alokasi
- [x] `InvestmentController` — CRUD investasi
- [x] Service: `AllocationService`:
  - [x] Validasi total `percent` semua bucket = 100 (`422 SUM_NOT_100`)
  - [x] Rekomendasi: analisa histori 3 bulan, sesuaikan dengan goal + dana darurat aktif
  - [x] Summary: hitung realisasi per pos berdasarkan `category_bucket_mappings`
- [x] Notifikasi: trigger ketika pos mencapai 80% dan 100% dari rencana
- [x] Template bawaan tersimpan di DB (dari seeder)

---

## 🔔 FASE 2-4 — Notifikasi & Perangkat

### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/v1/devices` | Daftarkan device (upsert by push_token) |
| `DELETE` | `/api/v1/devices/{id}` | Hapus device |
| `GET` | `/api/v1/notifications` | List notifikasi user |
| `PATCH` | `/api/v1/notifications/{id}/read` | Tandai sudah dibaca |
| `POST` | `/api/v1/notifications/read-all` | Tandai semua sudah dibaca |

### Checklist Implementasi
- [x] `DeviceController` — upsert berdasarkan `push_token`
- [x] `NotificationController` — list & baca
- [x] Service: `NotificationService@send(userId, type, data)`
- [x] Integrasikan Firebase Cloud Messaging (FCM) dispatch
- [x] Tipe notifikasi yang dihandle:
  - [x] `budget_80` — pos alokasi 80%
  - [x] `budget_100` — pos alokasi penuh
  - [x] `goal_done` — target tercapai
  - [x] `emergency_alert` — penarikan / setoran dana darurat

---

## 🔧 Hal Teknis Tambahan

### Security & Performance
- [ ] Semua query difilter `user_id` dari token (row-level security manual)
- [ ] Implementasi soft delete di semua model utama
- [ ] Index DB: `(user_id, spent_at)`, `(user_id, category_id)`, `updated_at`
- [ ] Semua nominal simpan sebagai `BIGINT` (rupiah, tanpa desimal)
- [ ] Password hashed dengan `bcrypt` atau `argon2`
- [ ] HTTPS wajib (handle di infra / middleware)

### Testing
- [ ] Unit test: `SmartEntryParser` (berbagai format input)
- [ ] Unit test: `IncomeBalanceService` (kalkulasi saldo)
- [ ] Unit test: `SavingsGoalService` (rumus monthly_amount)
- [ ] Unit test: `EmergencyFundService` (algoritma multiplier)
- [ ] Feature test (API): auth flow, CRUD expense, smart entry parse
- [ ] Feature test: sinkronisasi (push & pull)

### Dokumentasi API
- [ ] Setup OpenAPI/Swagger (`/api/documentation`)
- [ ] Annotasi semua controller + request
- [ ] Export OpenAPI spec JSON untuk tim Flutter & Web

---

## 📋 Urutan Pengerjaan yang Disarankan

```
[0] Setup & Fondasi
 ↓
[1A] Migrations & Models (semua sekaligus, urut dari atas)
 ↓
[1B] Seeders (kategori default, rules, template)
 ↓
[1C] Auth & Profil  ← mulai dari sini untuk bisa test endpoint
 ↓
[1D] Income + [1E] Category  ← fondasi untuk semua transaksi
 ↓
[1F] Expense  ← fitur inti pencatatan
 ↓
[1G] Smart Entry  ← fitur kunci differensiator produk
 ↓
[1H] Summary & Laporan
 ↓
[1I] Sinkronisasi Offline
 ↓
[2] Target Menabung
 ↓
[3] Dana Darurat
 ↓
[4] Alokasi & Investasi
 ↓
[Notifikasi] Paralel setelah Fase 2 selesai
```

---

> 📌 **Catatan**: Setiap fitur selesai → tulis **Feature Test** sebelum lanjut ke fitur berikutnya.
> Gunakan `php artisan make:test`, `php artisan make:migration`, `php artisan make:model -mrc` untuk mempercepat.
