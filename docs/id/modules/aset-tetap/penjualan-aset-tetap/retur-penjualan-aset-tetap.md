# RETUR PENJUALAN ASET TETAP

![](_media/id/aset-tetap/penjualan/retur-penjualan.jpg)

## DEFINISI
Kegiatan pengembalian barang dari customer atas aset tetap yang telah dikirimkan oleh perusahaan dengan alasan tertentu.

## KEGUNAAN
untuk input transaksi atas retur agar dapat membiayakan atau dapat menambahkan kembali jumlah aset tetap perusahaan.

!> **Note** Karena dalam retur penjualan berupa pembebanan atau pengakuan kembali nilai aset tetap, maka dalam sistem ini akan terjadi jurnal.

## CONTOH TRANSAKSI
Tgl 13/4/20 Perusahaan menerima retur atas 1 aset tetap yang telah dikirimkan dengan nominal jual @Rp.900.000 exclude PPN, diketahui nilai barang Rp.1.500.000 dan biaya perolehan yang terjadi atas aktiva tersebut sebesar Rp.750.000 

### JURNALNYA :

#### Penjualannya menggunakan PPN

| No. Akun | Nama Akun                                   | Debit        | Kredit     |
| -------- | ------------------------------------------- | ------------ | ---------- |
| 40104    | Retur Penjualan Aktiva Tetap                | Rp.900.000   |            |
| 20401    | PPN Keluaran                                | Rp.90.000    |            |
| 10505    | Piutang Aktiva Tetap                        |              | Rp.990.000 |
| 11401    | Mesin dan Peralatan                         | Rp.1.500.000 |            |
| 11402    | Akumulasi Penyusutan Mesin dan Peralatan    |              | Rp.750.000 |
| 50102    | Biaya Perolehan Aktiva Tetap                |              | Rp.750.000 |


#### Penjualannya tanpa PPN

| No. Akun | Nama Akun                    | Debit       | Kredit     |
| -------- | ---------------------------- | ----------- | ---------- |
| 40104    | Retur Penjualan Aktiva Tetap | Rp.900.000  |            |
| 10502    | Piutang Usaha                |             | Rp.900.000 |
| 11401    | Mesin dan Peralatan                         | Rp.1.500.000 |            |
| 11402    | Akumulasi Penyusutan Mesin dan Peralatan    |              | Rp.750.000 |
| 50102    | Biaya Perolehan Aktiva Tetap                |              | Rp.750.000 |

