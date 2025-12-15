# 📧 Panduan Setup Gmail SMTP untuk Notifikasi Email

## 🔍 Masalah
Aplikasi gagal mengirim notifikasi email karena konfigurasi SMTP Gmail belum diatur dengan benar.

## ✅ Solusi: Setup Gmail App Password

### Langkah 1: Aktifkan 2-Step Verification

1. Buka [Google Account Security](https://myaccount.google.com/security)
2. Di bagian "Signing in to Google", klik **2-Step Verification**
3. Ikuti petunjuk untuk mengaktifkan 2-Step Verification
4. Anda akan diminta memasukkan nomor telepon untuk verifikasi

### Langkah 2: Buat App Password

1. Setelah 2-Step Verification aktif, kembali ke [Google Account Security](https://myaccount.google.com/security)
2. Di bagian "Signing in to Google", klik **App passwords** (Sandi aplikasi)
   - Jika tidak melihat opsi ini, pastikan 2-Step Verification sudah aktif
3. Pilih aplikasi: **Mail**
4. Pilih perangkat: **Other (Custom name)**
5. Beri nama: **SmartHealthy App** atau **Healthy Food App**
6. Klik **Generate**
7. Google akan menampilkan **16-digit password** seperti: `abcd efgh ijkl mnop`
8. **PENTING**: Salin password ini (tanpa spasi)

### Langkah 3: Update File .env

1. Buka file `.env` di root folder project Anda
2. Update baris berikut dengan informasi Anda:

```env
SMTP_HOST=smtp.gmail.com
SMTP_USER=emailanda@gmail.com
SMTP_PASS=abcdefghijklmnop
```

**Contoh:**
```env
SMTP_HOST=smtp.gmail.com
SMTP_USER=johndoe@gmail.com
SMTP_PASS=xyzw abcd efgh ijkl
```

⚠️ **Catatan Penting:**
- Ganti `emailanda@gmail.com` dengan email Gmail Anda yang sebenarnya
- Ganti `abcdefghijklmnop` dengan 16-digit App Password yang baru saja dibuat
- App Password bisa ditulis dengan atau tanpa spasi (sistem akan menghapus spasi otomatis)
- **JANGAN** gunakan password Gmail biasa Anda!

### Langkah 4: Test Pengiriman Email

1. Buka aplikasi di browser: `http://localhost/yourproject/public/notifications.php`
2. Isi form:
   - **Judul Email**: Test Email
   - **Isi Pesan**: Ini adalah test email dari JawaHealthy App
3. Klik **Kirim Notifikasi**
4. Jika berhasil, Anda akan melihat pesan: ✅ Notifikasi berhasil dikirim
5. Cek inbox email Anda

## 🔧 Troubleshooting

### Error: "SMTP masih menggunakan placeholder"
**Solusi**: Anda belum mengupdate file `.env`. Pastikan sudah mengganti `your@gmail.com` dan `app_password_kamu` dengan kredensial yang sebenarnya.

### Error: "Authentication failed"
**Solusi**: 
- Pastikan App Password yang Anda masukkan benar
- Pastikan 2-Step Verification masih aktif
- Coba generate App Password baru

### Error: "Could not connect to SMTP host"
**Solusi**:
- Pastikan koneksi internet Anda stabil
- Pastikan firewall tidak memblokir port 587
- Coba restart XAMPP

### Email tidak masuk ke inbox
**Solusi**:
- Cek folder **Spam** atau **Junk**
- Pastikan email tujuan benar
- Tunggu beberapa menit (kadang ada delay)

## 📝 Informasi Tambahan

### Keamanan App Password
- App Password adalah password khusus untuk aplikasi, bukan password Gmail utama Anda
- Lebih aman karena bisa dihapus kapan saja tanpa mengubah password Gmail
- Jika App Password bocor, Anda bisa langsung revoke dari Google Account

### Cara Revoke App Password
1. Buka [Google Account Security](https://myaccount.google.com/security)
2. Klik **App passwords**
3. Klik ikon **trash/delete** di samping App Password yang ingin dihapus
4. Generate App Password baru jika diperlukan

## 🎯 Checklist Setup

- [ ] 2-Step Verification sudah aktif
- [ ] App Password sudah dibuat
- [ ] File `.env` sudah diupdate dengan email dan App Password yang benar
- [ ] Test pengiriman email berhasil
- [ ] Email diterima di inbox

## 📞 Bantuan Lebih Lanjut

Jika masih mengalami masalah setelah mengikuti panduan ini:
1. Cek file log error di `c:\xampp\apache\logs\error.log`
2. Pastikan PHPMailer sudah terinstall dengan benar
3. Coba gunakan email Gmail yang berbeda

---

**Dibuat untuk**: SmartHealthy App  
**Terakhir diupdate**: 3 Desember 2024
