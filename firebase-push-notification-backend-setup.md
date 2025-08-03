# Setup Firebase Push Notification (Backend) di Project Point

Berikut adalah langkah-langkah setup backend untuk mengirim push notification menggunakan Firebase Cloud Messaging (FCM) di project Point:

## 1. Buat Project & Service Account di Firebase
- Buka https://console.firebase.google.com/
- Pilih project yang sama dengan frontend.
- Masuk ke Project Settings > Service Accounts.
- Klik "Generate new private key" untuk Firebase Admin SDK.
- Simpan file JSON (misal: `firebase-service-account.json`).
- Tambahkan file ini ke folder `storage/app/firebase` dan pastikan sudah di-.gitignore.


### Contoh PHP (Laravel):
Gunakan package seperti kreait/firebase-php:
```bash
composer require kreait/firebase-php
```
Lihat dokumentasi: https://github.com/kreait/firebase-php

## 2. Cara Kirim Notifikasi dari Backend
- Ambil FCM token user dari database.
- Panggil fungsi `sendPushNotification(token, payload)`
- Contoh payload:
```js
const payload = {
  notification: {
    title: 'Judul Notifikasi',
    body: 'Isi pesan notifikasi',
  },
  data: {
    customKey: 'customValue',
  },
};