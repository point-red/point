# RETUR PEMBELIAN


Retur Pembelian merupakan pengembalian barang atas barang yang telah dibeli karena alasan-alasan tertentu sehingga dapat mengurangi jumlah nominal hutang kepada supplier yang bersangkutan.

Retur Pembelian memiliki fungsi untuk melakukan input transaksi retur pembelian sehingga dapat dilihat daftar rekamannya dan dapat mengurangi nominal hutang saat akan dilakukan pembayaran dalam sistem kas / bank

Contoh kasus : Perusahaan mengembalikan barang kepada supplier sebesar Rp.800.000 karena barang yang dikirimkan rusak. Padahal invoice telah diterima dan barang telah diakui jumlahnya.

Jurnal yang akan terjadi saat user melakukan input transaksi :

Pembelian tanpa PPN

| No. Akun | Nama Akun             | Debit      | Kredit     |
| -------- | --------------------- | ---------- | ---------- |
| 20201    | Hutang Usaha          | Rp.800.000 |            |
| 10401    | Persediaan Bahan Baku |            | Rp.800.000 |

Pembelian dengan PPN

| No. Akun | Nama Akun             | Debit      | Kredit     |
| -------- | --------------------- | ---------- | ---------- |
| 20201    | Hutang Usaha          | Rp.727.273 |            |
| 10701    | PPN Masukan           | Rp.72.727  |            |
| 10401    | Persediaan Bahan Baku |            | Rp.800.000 |


Retur dibebankan langsung pada Persediaan karena sistem mengunakan metode Perpectual