# FAKTUR PENJUALAN


Faktur Penjualan merupakan pengakuan sejumlah tagihan atas customer yang barang pesanannya telah dikirimkan oleh perusahaan. 

Faktur Penjualan memiliki fungsi untuk input transaksi piutang atas customer berdasarkan pesanan yang telah dikirimkan. Sehingga perusahaan dapat melihat jumlah daftar piutang yang masih harus diterima dari customer serta masa jatuh temponya. 

Dalam Faktur Penjualan piutang akan diakui sehingga dalam sistem ini akan terjadi jurnal. 

Contoh : Perusahaan menarik faktur atas barang yang telah dikirimkan kepada Customer dengan nilai nominal Rp.900.000 belum termasuk PPN. dan diketahui HPP atas barang Rp.750.000

Jurnal yang akan terjadi :

Penjualan menggunakan PPN

| No. Akun | Nama Akun     | Debit      | Kredit     |
| -------- | ------------- | ---------- | ---------- |
| 10502    | Piutang Usaha | Rp.990.000 |            |
| 20401    | PPN Keluaran  |            | Rp.90.000  |
| 40101    | Penjualan     |            | Rp.900.000 |


Penjualan tanpa PPN

| No. Akun | Nama Akun     | Debit      | Kredit     |
| -------- | ------------- | ---------- | ---------- |
| 10502    | Piutang Usaha | Rp.900.000 |            |
| 40101    | Penjualan     |            | Rp.900.000 |


Nilai HPP diabaikan karena HPP sudah diakui saat barang pesanan dikirimkan. 