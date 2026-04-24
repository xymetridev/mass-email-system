# 🚀 Mass Email System - Project Blueprint

Sistem pengiriman email massal (Broadcasting) berbasis CodeIgniter 4 dengan fokus pada skalabilitas, keamanan, dan deliverability tinggi.

## 🛠 Tech Stack
- **Framework:** CodeIgniter 4.5+
- **Auth:** CodeIgniter Shield (Session & Magic Link)
- **UI:** Tabler Dashboard (Bootstrap 5)
- **Database:** MySQL / MariaDB
- **Icons:** Tabler Icons

---

## 🌟 Fitur Utama & Status Implementasi

### 1. Campaign Wizard (Manajemen Kampanye)
Alur pembuatan email massal dalam 5 langkah yang intuitif.
- [x] **Step 1: Info Dasar** - Nama kampanye & pemilihan akun pengirim (Bisnis/Individu).
- [x] **Step 2: Penerima** - Support upload CSV/TXT & integrasi database per-Tag.
- [x] **Step 3: Konten** - Integrasi Template & Preview real-time.
- [x] **Step 4: Penjadwalan** - Opsi kirim sekarang atau jadwalkan di waktu mendatang.
- [x] **Step 5: Review** - Ringkasan akhir sebelum peluncuran.
- [x] **Sistem Edit Draft:** Support pengalihan status kembali ke DRAFT saat diedit untuk keamanan.
- [x] **Cleanup:** Penghapusan kampanye otomatis membersihkan `email_queue` & `tracking_logs`.

### 2. SMTP & Deliverability Management
Fitur pengelolaan pengirim email untuk menghindari SPAM.
- [x] **Multi-Account SMTP:** Mendukung banyak akun pengirim sekaligus.
- [x] **Gmail Smart Detection:** Deteksi otomatis akun Gmail dengan panduan *App Password*.
- [x] **Domain Reputation Guide:** Panduan teknis SPF, DKIM, dan DMARC terintegrasi di UI.
- [x] **Warm-up Mode:** Batasan pengiriman harian & per-jam untuk menjaga reputasi IP/Domain.

### 3. Contact & Segmentation
- [x] **Master Contact:** Database kontak pusat.
- [x] **Tagging System:** Pengelompokan kontak berdasarkan segmen/tag.
- [x] **Bulk Import:** Import ribuan kontak via CSV dengan pembersihan data otomatis.

### 4. Email Automation (Sequences)
- [x] **Trigger-Based:** Automation berjalan otomatis saat kontak mendapatkan Tag tertentu.
- [x] **Sequence Steps:** Membuat rangkaian email (Series) dengan jeda waktu (delay) tertentu.

### 5. Security & Monitoring (Audit Trail)
- [x] **Activity Logs:** Mencatat setiap aksi krusial (Import, Hapus, Kirim, Login).
- [x] **System Logs:** Viewer log teknis (file-based) untuk debugging Admin.
- [x] **Rate Limiting (Throttler):** Melindungi rute-rute berat (Import/Process) dari penyalahgunaan.

---

## 🏗 Struktur Database Penting
- `campaigns`: Data utama kampanye dan statusnya.
- `email_queue`: Antrean email yang menunggu dikirim oleh Cron.
- `activity_logs`: Catatan audit aktivitas pengguna.
- `automation_steps`: Langkah-langkah dalam alur email otomatis.
- `smtp_accounts`: Kredensial server SMTP pengirim.

---

## 📅 Rencana Pengembangan (Roadmap)
- [ ] **Email Tracking:** Statistik Open Rate & Click-Through Rate (CTR).
- [ ] **Unsubscribe Link:** Sistem otomatis untuk menangani permintaan berhenti berlangganan.
- [ ] **A/B Testing:** Menguji dua subjek email berbeda dalam satu kampanye.
- [ ] **Bounce Handling:** Otomatis menonaktifkan email yang mental (Hard Bounce).

---

## 🛡 Checklist Produksi
1. [ ] Ubah `CI_ENVIRONMENT` ke `production` di file `.env`.
2. [ ] Setel Cron Job untuk menjalankan `php spark email:run` setiap menit.
3. [ ] Aktifkan HTTPS (`app.forceGlobalSecureRequests = true`).
4. [ ] Konfigurasi SMTP Sistem di `.env` (untuk Magic Link & Reset Password).
