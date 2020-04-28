# FAKTUR PEMBELIAN ASET TETAP

![](_media/id/aset-tetap/pembelian/faktur-pembelian.jpg)

## DEFINISI
Penerimaan sejumlah tagihan yang harus dibayarkan perusahaan atas aset tetap yang telah diterima. 

## KEGUNAAN
untuk input transaksi hutang perusahaan berdasarkan faktur yang diterima. Sehingga perusahaan dapat melihat jumlah daftar hutang yang masih harus dibayar kepada supplier serta masa jatuh temponya.

## CONTOH
Tgl 44/20 Perusahaan menerima faktur pembelian atas 5 buah printer yang telah diterima pada tgl 2/4/20 dengan harga satu printernya @Rp.600.000 exclude PPN

### JURNALNYA :

#### Pembelian Aset Tetap tanpa PPN
##### Pengakuan dalam menu Faktur Pembelian Aset Tetap

|No. Akun| Nama Akun                  | Debit       | Kredit       |
|--------| -------------------------- | ------------| ------------ |
|11899   | Aktiva Tetap Dalam Proses  | Rp.3.000.000|              |
|20502   | Hutang Aktiva Tetap        |             | Rp.3.000.000 |

##### Pengakuan dalam menu Master Aset Tetap

|No. Akun| Nama Akun                   | Debit       | Kredit       |
|--------| --------------------------- | ------------| ------------ |
|11401   | Mesin dan Peralatan         | Rp.3.000.000|              |
|11899   | Aktiva Tetap Dalam Proses   |             | Rp.3.000.000 |

#### Pembelian Aset Tetap dengan PPN
##### Pengakuan dalam menu Faktur Pembelian Aset Tetap

|No. Akun| Nama Akun                  | Debit       | Kredit       |
|--------| -------------------------- | ------------| ------------ |
|11899   | Aktiva Tetap Dalam Proses  | Rp.3.000.000|              |
|10701   | ppn Masukan                | Rp.300.000  |              |
|20502   | Hutang Aktiva Tetap        |             | Rp.3.300.000 |

##### Pengakuan dalam menu Master Aset Tetap

|No. Akun| Nama Akun                   | Debit       | Kredit       |
|--------| --------------------------- | ------------| ------------ |
|11899   | Mesin dan Peralatan         | Rp.3.000.000|              |
|20201   | Aktiva Tetap Dalam Proses   |             | Rp.3.000.000 |

!> **Catatan** pada menu faktur pembelian pengakuan nominal aset masuk dalam akun transaksi aset tetap. Karena nilai akan diakui akun aset tetap saat menambahkan barang pada master aset tetap.