# RETUR PENJUALAN

![](_media/id/penjualan/retur-penjualan.jpg)

## DEFINISI
Kegiatan pengembalian barang dari customer atas barang yang telah dikirimkan oleh perusahaan dengan alasan tertentu.

## KEGUNAAN
untuk input transaksi atas retur agar dapat membiayakan atau dapat menambahkan jumlah persediaan.

!> **Note** Karena dalam retur penjualan berupa pembebanan atau penambahan persediaan, maka dalam sistem ini akan terjadi jurnal.

## CONTOH APABILA BARANG DIRETUR KARENA RUSAK
Tgl 20/3/20 Perusahaan menerima pengembalian barang sejumlah Rp.99.000 dari nota Customer A atas barang yang telah dikirimkan pada tgl 07/03/20 karena barang tersebut rusak

### JURNALNYA :

#### Penjualannya menggunakan PPN

| No. Akun | Nama Akun                | Debit     | Kredit    |
| -------- | ------------------------ | --------- | --------- |
| 40102    | Retur Penjualan          | Rp.90.000 |           |
| 20401    | PPN Keluaran             | Rp.9.000  |           |
| 10502    | Piutang Usaha            |           | Rp.99.000 |
| 50112    | Beban Selisih Persediaan | Rp.90.000 |           |
| 50101    | Beban Pokok Penjualan    |           | Rp.90.000 |


#### Penjualannya tanpa PPN

| No. Akun | Nama Akun                | Debit     | Kredit    |
| -------- | ------------------------ | --------- | --------- |
| 40102    | Retur Penjualan          | Rp.99.000 |           |
| 10502    | Piutang Usaha            |           | Rp.99.000 |
| 50112    | Beban Selisih Persediaan | Rp.99.000 |           |
| 50101    | Beban Pokok Penjualan    |           | Rp.99.000 |

## CONTOH APABILA RETUR KARENA KELALAIAN PENGIRIMAN
Tgl 20/3/20 Perusahaan menerima pengembalian barang sejumlah Rp.99.000 dari nota Customer A atas barang yang telah dikirimkan pada tgl 07/03/20 karena barang tersebut salah warna.

### JURNALNYA :

#### Penjualannya menggunakan PPN

| No. Akun | Nama Akun              | Debit     | Kredit    |
| -------- | ---------------------- | --------- | --------- |
| 40102    | Retur Penjualan        | Rp.90.000 |           |
| 20401    | PPN Keluaran           | Rp.9.000  |           |
| 10502    | Piutang Usaha          |           | Rp.99.000 |
| 10404    | Persediaan Barang Jadi | Rp.90.000 |           |
| 50101    | Beban Pokok Penjualan  |           | Rp.90.000 |


#### Penjualannya tanpa PPN

| No. Akun | Nama Akun              | Debit     | Kredit    |
| -------- | ---------------------- | --------- | --------- |
| 40102    | Retur Penjualan        | Rp.99.000 |           |
| 10502    | Piutang Usaha          |           | Rp.99.000 |
| 10404    | Persediaan Barang Jadi | Rp.99.000 |           |
| 50101    | Beban Pokok Penjualan  |           | Rp.99.000 |

