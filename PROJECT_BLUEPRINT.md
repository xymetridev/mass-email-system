# 🚀 Mass Email System - Project Blueprint

Sistem pengiriman email massal (Broadcasting) berbasis CodeIgniter 4 dengan fokus pada skalabilitas, keamanan, dan deliverability tinggi.

## 🛠 Tech Stack
- **Framework:** CodeIgniter 4.5+
- **Auth:** CodeIgniter Shield (Session & Magic Link)
- **UI:** Tabler Dashboard (Bootstrap 5) - Premium Aesthetics
- **Database:** MySQL / MariaDB (Optimized with Turbo Indexes)
- **Engine:** Adaptive Worker System (Hybrid Scheduler + Queue Processor)

---

## 🌟 Fitur Utama & Status Implementasi

### 1. Campaign Wizard (Manajemen Kampanye)
Alur pembuatan email massal dalam 5 langkah yang intuitif.
- [x] **Step 1: Info Dasar** - Nama kampanye & pemilihan akun pengirim.
- [x] **Step 2: Penerima** - **Unified Master Table** (CSV, DB Tags, Manual) dengan real-time validation & deduplication.
- [x] **Step 3: Konten** - Integrasi Template & Preview real-time.
- [x] **Step 4: Penjadwalan** - Opsi kirim sekarang atau jadwalkan di waktu mendatang.
- [x] **Step 5: Review** - Ringkasan akhir & peluncuran kampanye.
- [x] **Hardening:** Proteksi status DRAFT agar kampanye tidak jalan sebelum setup tuntas.

### 2. SMTP & Deliverability Management
Fitur pengelolaan pengirim email untuk menghindari SPAM.
- [x] **Multi-Account SMTP:** Mendukung banyak akun pengirim sekaligus.
- [x] **Gmail Smart Detection:** Deteksi otomatis akun Gmail dengan panduan *App Password*.
- [x] **Warm-up Mode:** Batasan pengiriman harian & per-jam (Throttling) untuk menjaga reputasi.
- [x] **Bounce Handling:** Deteksi otomatis email mental (Hard Bounce) via IMAP & otomatis masuk Suppression List.

### 3. Analytics & Tracking
- [x] **Open Tracking:** Melacak siapa yang membuka email via tracking pixel.
- [x] **Click Tracking:** Melacak link mana yang diklik (Link Wrapping).
- [x] **Unsubscribe System:** Link berhenti berlangganan otomatis & manual.
- [x] **Suppression List:** Database pusat email yang tidak boleh dikirimi lagi (Bounced/Unsubscribed).

### 4. Email Automation (Sequences)
- [x] **Trigger-Based:** Automation berjalan otomatis saat kontak mendapatkan Tag tertentu.
- [x] **Sequence Steps:** Membuat rangkaian email (Series) dengan jeda waktu (delay) hari.

### 5. Database & Worker Engine (Performance)
- [x] **Adaptive Worker:** Satu proses background (`email:worker`) yang menangani Scheduler & Antrean sekaligus.
- [x] **Turbo Indexes:** Optimasi database pada tabel `campaigns`, `email_queue`, `activity_logs`, dan `recipient_tags`.
- [x] **Atomic Locking:** Menjamin tidak ada double-send saat menggunakan banyak worker sekaligus.

---

## 🏗 Struktur Database Penting
- `campaigns`: Data utama kampanye dan statusnya (Indexed for performance).
- `email_queue`: Antrean email dengan log riwayat pengiriman (Indexed).
- `contacts` & `recipient_tags`: Database kontak dan segmentasi tag.
- `tracking_logs`: Catatan setiap event Open/Click.
- `suppression_list`: Daftar hitam email (Bounce/Unsubscribe).
- `activity_logs`: Catatan audit aktivitas pengguna (Indexed).

---

## 📅 Rencana Pengembangan (Roadmap)
- [ ] **A/B Testing:** Menguji dua subjek email berbeda dalam satu kampanye.
- [ ] **Email Builder (Drag & Drop):** Editor visual untuk membuat template email.
- [ ] **Advanced Analytics:** Heatmap klik dan statistik geolokasi penerima.

---

## 🛡 Checklist Produksi
1. [x] Pasang **Turbo Indexes** (`php spark migrate`).
2. [ ] Ubah `CI_ENVIRONMENT` ke `production` di file `.env`.
3. [ ] Jalankan Worker Daemon: `php spark email:worker` (Gunakan Supervisor/Systemd).
4. [ ] Aktifkan HTTPS (`app.forceGlobalSecureRequests = true`).
5. [ ] Konfigurasi SMTP Sistem di `.env` (untuk Magic Link & Reset Password).
