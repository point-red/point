# FAKTUR PEMBELIAN


Faktur Pembelian merupakan penerimaan sejumlah tagihan yang harus dibayarkan oleh perusahaan atas barang yang telah diterima. 

Faktur Pembelian memiliki fungsi untuk input transaksi hutang perusahaan berdasarkan faktur yang diterima. Sehingga perusahaan dapat melihat jumlah daftar hutang yang masih harus dibayar kepada supplier

Contoh : Perusahaan menerima Faktur Pembelian atas barang yang telah diterima pada tanggal 1 dan 5 Januari 2020 sejumlah Rp. 45.000.000

Jurnal yang akan terjadi saat user melakukan input transaksi :

Pembelian Bahan Baku tanpa PPN

|No. Akun | Nama Akun             | Debit        | Kredit        |
|-------- | --------------------- | -------------| ------------- |
|10401    | Persediaan Bahan Baku | Rp.45.000.000|               |
|20201    | Hutang Usaha          |              | Rp.45.000.000 |

Pembelian Bahan Baku dengan PPN

| No. Akun | Nama Akun             | Debit         | Kredit        |
| -------- | --------------------- | ------------- | ------------- |
| 10401    | Persediaan Bahan Baku | Rp.45.000.000 |               |
| 10701    | PPN Masukan           | Rp.4.500.000  |               |
| 20201    | Hutang Usaha          |               | Rp.49.500.000 |

Pembelian Bahan Pembantu tanpa PPN

| No. Akun | Nama Akun                 | Debit         | Kredit        |
| -------- | ------------------------- | ------------- | ------------- |
| 10402    | Persediaan Bahan Pembantu | Rp.45.000.000 |               |
| 20201    | Hutang Usaha              |               | Rp.45.000.000 |

Pembelian Bahan Pembantu dengan PPN

| No. Akun | Nama Akun                 | Debit         | Kredit        |
| -------- | ------------------------- | ------------- | ------------- |
| 10402    | Persediaan Bahan Pembantu | Rp.45.000.000 |               |
| 10701    | PPN masukan               | Rp.4.500.000  |               |
| 20201    | Hutang Usaha              |               | Rp.49.500.000 |

