# RETUR PEMBELIAN

![](_media/id/pembelian/retur-pembelian.jpg)

## DEFINISI
Pengembalian barang atas barang yang dibeli karena alasan-alasan tertentu sehingga dapat mengurangi nominal hutang kepada supplier yang bersangkutan.

## KEGUNAAN
untuk melakukan input transaksi retur pembelian sehingga dapat dilihat daftar rekamannya dan dapat mengurangi nominal hutang saat akan dilakukan pembayaran dalam sistem kas / bank

## CONTOH
Perusahaan mengembalikan barang kepada supplier sebesar Rp.800.000 karena barang yang dikirimkan rusak. Padahal invoice telah diterima dan barang telah diakui jumlahnya.

### JURNALNYA :

#### Pembelian tanpa PPN

| No. Akun | Nama Akun             | Debit      | Kredit     |
| -------- | --------------------- | ---------- | ---------- |
| 20201    | Hutang Usaha          | Rp.800.000 |            |
| 10401    | Persediaan Bahan Baku |            | Rp.800.000 |

#### Pembelian dengan PPN

| No. Akun | Nama Akun             | Debit      | Kredit     |
| -------- | --------------------- | ---------- | ---------- |
| 20201    | Hutang Usaha          | Rp.727.273 |            |
| 10701    | PPN Masukan           | Rp.72.727  |            |
| 10401    | Persediaan Bahan Baku |            | Rp.800.000 |


!> **Note** Retur dibebankan langsung pada Persediaan karena sistem mengunakan metode Perpectual