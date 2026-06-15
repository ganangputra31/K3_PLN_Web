# Sistem Informasi K3 PT PLN (Persero)

Aplikasi web Keselamatan dan Kesehatan Kerja (K3) untuk PT PLN (Persero) yang dibangun dengan **Laravel 11**, **Blade + Bootstrap 5**, database **Supabase PostgreSQL**, penyimpanan file **Supabase Storage**, dan grafik dashboard **Chart.js**.

Aplikasi terdiri dari dua sisi:

1. **Situs publik** (tanpa login) — menampilkan konten K3: profil, identifikasi bahaya, risiko, APD, SOP, prosedur evakuasi, program kesehatan, struktur tim K3, denah lokasi, serta kesimpulan & saran.
2. **Panel admin** (dengan login) — dashboard statistik + grafik dan modul CRUD untuk APD, SOP, Bahaya, Insiden, Tim K3, Program Kesehatan, serta Audit Log.

---

## 1. Prasyarat

| Komponen | Versi minimum |
|----------|---------------|
| PHP | 8.2 |
| Composer | 2.x |
| Ekstensi PHP | `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `fileinfo`, `curl` |
| Akun Supabase | gratis (https://supabase.com) |

> **Penting:** ekstensi `pdo_pgsql` **wajib** aktif karena database memakai PostgreSQL. Cek dengan `php -m | grep pgsql`.

---

## 2. Membuat Project Supabase

1. Buat project baru di https://supabase.com/dashboard.
2. Catat **Database Password** yang Anda buat saat pembuatan project.
3. Ambil kredensial database di **Project Settings → Database → Connection info**:
   - Gunakan **Session pooler** (host `...pooler.supabase.com`, port **5432**) untuk menjalankan migrasi.
4. Ambil kredensial API di **Project Settings → API**:
   - `Project URL` → `SUPABASE_URL`
   - `anon public` key → `SUPABASE_ANON_KEY`
   - `service_role` key → `SUPABASE_SERVICE_ROLE_KEY` (rahasia, hanya dipakai di server)

### 2.1 Membuat Storage Bucket

1. Buka **Storage → Create a new bucket**.
2. Nama bucket: `k3-files` (samakan dengan `SUPABASE_BUCKET` di `.env`).
3. Centang **Public bucket** agar URL gambar bisa diakses publik.
4. (Opsional) Jika ingin bucket privat, tambahkan kebijakan baca publik via SQL Editor:

```sql
-- Izinkan baca publik untuk bucket k3-files
create policy "Public read k3-files"
on storage.objects for select
using ( bucket_id = 'k3-files' );
```

Upload dari aplikasi memakai `service_role` key sehingga tidak terhalang RLS.

---

## 3. Instalasi Aplikasi

```bash
# 1. Masuk ke folder project
cd k3-pln

# 2. Install dependency PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate APP_KEY
php artisan key:generate
```

### 3.1 Konfigurasi `.env`

Edit `.env` dan isi sesuai kredensial Supabase Anda:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com   # host pooler Anda
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.xxxxxxxxxxxxxxxx               # username pooler Anda
DB_PASSWORD=your-supabase-db-password
DB_SSLMODE=require

SUPABASE_URL=https://xxxxxxxxxxxxxxxx.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOi...
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOi...
SUPABASE_BUCKET=k3-files
```

---

## 4. Migrasi & Seeder

```bash
# Membuat seluruh tabel di Supabase PostgreSQL
php artisan migrate

# Mengisi data awal (admin, APD, SOP, bahaya, insiden, tim, program kesehatan)
php artisan db:seed
```

Atau sekaligus:

```bash
php artisan migrate --seed
```

### Akun admin default

| Email | Password |
|-------|----------|
| `admin@k3pln.com` | `admin123` |

> Ganti password ini setelah login pertama (atau ubah di `database/seeders/AdminUserSeeder.php` sebelum seeding).

---

## 5. Menjalankan Aplikasi

```bash
php artisan serve
```

Akses:

- Situs publik: http://localhost:8000
- Login admin: http://localhost:8000/login
- Dashboard admin: http://localhost:8000/admin/dashboard

---

## 6. Gambar Denah Lokasi

Halaman **Denah Lokasi** memuat gambar dari `public/assets/denah.png`.
Letakkan file denah Anda di path tersebut. Jika tidak ada, halaman tetap tampil dengan placeholder dan legenda ruangan.

Alternatif: upload denah ke Supabase Storage lalu ganti `src` gambar di
`resources/views/public/denah.blade.php` dengan URL publiknya.

---

## 7. Struktur Folder Penting

```
app/
  Http/Controllers/
    PublicController.php        # semua halaman publik
    Auth/LoginController.php    # login/logout
    Admin/                      # controller CRUD admin + dashboard
  Models/                       # model Eloquent (PostgreSQL)
  Services/SupabaseStorageService.php   # upload/hapus file ke Supabase Storage
  Support/AuditLogger.php       # pencatat audit log
config/
  database.php                  # koneksi pgsql + sslmode
  supabase.php                  # konfigurasi Supabase API/Storage
database/
  migrations/                   # skema PostgreSQL-compatible
  seeders/                      # data awal
resources/views/
  public/                       # halaman publik
  admin/                        # halaman admin (dashboard + CRUD)
  layouts/                      # layout publik & admin
routes/web.php                  # definisi route publik & admin
public/assets/app.css           # tema warna PLN (biru & kuning)
```

---

## 8. Catatan Teknis

- **PostgreSQL-only.** Tidak menggunakan MySQL, `AUTO_INCREMENT`, atau `ENUM` native. Kolom berjenis pilihan disimpan sebagai `string` dan divalidasi di controller memakai konstanta pada model (mis. `Hazard::CATEGORIES`, `Incident::STATUSES`).
- **Auth** memakai session-based Laravel; route `/admin/*` dilindungi middleware `auth`.
- **Audit log** otomatis mencatat aksi create/update/delete tiap modul melalui `AuditLogger::record()`.
- **Upload file** dikirim ke Supabase Storage REST API memakai `service_role` key; hanya URL publik yang disimpan ke database (kolom `image_url` / `evidence_url`).
- **Chart.js** dimuat via CDN pada dashboard (bar = bahaya per kategori, doughnut = status insiden, line = tren insiden per bulan).

---

## 9. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `could not find driver` | Aktifkan ekstensi `pdo_pgsql` di `php.ini`. |
| Migrasi gagal / timeout | Pastikan memakai host **pooler** port `5432` dan `DB_SSLMODE=require`. |
| Gambar tidak tampil | Pastikan bucket `k3-files` bersifat **public** atau policy SELECT sudah dibuat. |
| Upload gagal (401/403) | Periksa `SUPABASE_SERVICE_ROLE_KEY` dan nama `SUPABASE_BUCKET`. |
| Session error | Jalankan `php artisan migrate` (tabel `sessions` dibuat oleh migrasi). |

---

Dikembangkan untuk kebutuhan sistem informasi K3 PT PLN (Persero).
