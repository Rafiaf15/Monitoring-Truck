# PANDUAN DEPLOY KE INFINITYFREE (100% GRATIS)

Langkah-langkah untuk meng-onlinekan aplikasi Monitoring Truk Pipe ke InfinityFree:

---

### Langkah 1: Buat Akun & Hosting di InfinityFree
1. Buka [https://www.infinityfree.com](https://www.infinityfree.com) lalu daftar (Sign Up).
2. Di dashboard, klik **Create Account**.
3. Pilih domain gratis (contoh: `monitoring-pipa.infinityfreeapp.com`).
4. Tunggu beberapa detik sampai status akun menjadi **Active**.

---

### Langkah 2: Buat Database MySQL di InfinityFree
1. Masuk ke **Control Panel (cPanel)** akun InfinityFree Anda.
2. Cari menu **MySQL Databases**.
3. Masukkan nama database (misal: `trucking`) lalu klik **Create Database**.
4. Catat informasi koneksi yang muncul:
   - **MySQL Hostname** (biasanya format: `sqlXXX.infinityfree.com`)
   - **MySQL Database Name** (format: `epiz_XXXXXXX_trucking`)
   - **MySQL Username** (format: `epiz_XXXXXXX`)
   - **MySQL Password** (password cPanel Anda)
5. Klik tombol **phpMyAdmin** di sebelah database yang baru dibuat.
6. Pilih menu **Import**, upload file:
   `database_monitoring.sql`
   (File ini sudah disiapkan di folder proyek Anda dengan 26 truk, 0 pipa, dan status BPI semua).
7. Klik **Go** / **Kirim**. Database Anda sekarang sudah terisi!

---

### Langkah 3: Siapkan File Proyek untuk Diunggah
1. Di komputer lokal Anda, buka file `.env` di proyek ini, sesuaikan:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://nama-domain-anda.infinityfreeapp.com

   DB_CONNECTION=mysql
   DB_HOST=sqlXXX.infinityfree.com (sesuai data InfinityFree)
   DB_PORT=3306
   DB_DATABASE=epiz_XXXXXXX_trucking
   DB_USERNAME=epiz_XXXXXXX
   DB_PASSWORD=password_cpanel_anda
   ```
2. Kompres/Zip seluruh folder `monitoring-trucking` menjadi file `.zip` (Kecuali folder `.git` tidak perlu di-zip jika ada).

---

### Langkah 4: Upload File ke Hosting
1. Di cPanel InfinityFree, buka **File Manager** (atau gunakan FileZilla via FTP).
2. Masuk ke folder: `htdocs`
3. Upload seluruh isi proyek Anda ke dalam folder `htdocs`.
   *(Catatan: File `.htaccess` di root sudah dibuatkan agar otomatis mengarahkan ke folder `public/`)*
4. Pastikan folder `storage` dan `bootstrap/cache` memiliki permission `775` atau `777` agar aplikasi bisa menulis log/session.

---

### Langkah 5: Buka di HP!
Buka URL domain Anda (misal `https://monitoring-pipa.infinityfreeapp.com`) langsung dari browser handphone Anda.
Aplikasi sudah siap digunakan untuk update real-time via smartphone!
