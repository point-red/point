# FAKTUR PENJUALAN

![](_media/id/penjualan/faktur-penjualan.jpg)

## DEFINISI
Pengakuan sejumlah tagihan atas customer yang barang pesanannya telah dikirimkan oleh perusahaan. 

## KEGUNAAN
untuk input transaksi piutang atas customer berdasarkan pesanan yang telah dikirimkan. Sehingga perusahaan dapat melihat jumlah daftar piutang yang masih harus diterima dari customer serta masa jatuh temponya. 

!> **Dalam Faktur Penjualan** piutang akan diakui sehingga dalam sistem ini akan terjadi jurnal. 

## CONTOH
Perusahaan menarik faktur atas barang yang telah dikirimkan kepada Customer dengan nilai nominal Rp.900.000 belum termasuk PPN. dan diketahui HPP atas barang Rp.750.000

### JURNALNYA : 

#### Penjualan menggunakan PPN

| No. Akun | Nama Akun     | Debit      | Kredit     |
| -------- | ------------- | ---------- | ---------- |
| 10502    | Piutang Usaha | Rp.990.000 |            |
| 20401    | PPN Keluaran  |            | Rp.90.000  |
| 40101    | Penjualan     |            | Rp.900.000 |


#### Penjualan tanpa PPN

| No. Akun | Nama Akun     | Debit      | Kredit     |
| -------- | ------------- | ---------- | ---------- |
| 10502    | Piutang Usaha | Rp.900.000 |            |
| 40101    | Penjualan     |            | Rp.900.000 |


!> **Note** Nilai HPP diabaikan karena HPP sudah diakui saat barang pesanan dikirimkan. 