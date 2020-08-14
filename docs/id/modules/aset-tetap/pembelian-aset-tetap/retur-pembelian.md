# RETUR PEMBELIAN

![](_media/id/pembelian/retur-pembelian.jpg)

## DEFINISI
Pengembalian barang atas aset tetap yang dibeli karena alasan tertentu sehingga dapat mengurangi nominal hutang kepada supplier

## KEGUNAAN
untuk melakukan input transaksi retur pembelian sehingga dapat dilihat daftar rekamannya dan sebagai informasi nominal hutang saat akan dilakukan pembayaran dalam sistem kas / bank

## CONTOH
Tgl 7/4/20 Perusahaan mengembalikan 2 printer dengan harga 1 printer @Rp.600.000 Exclude PPN yang faturnya telah diterima dua hari lalu karena ada kendala pada printer

### JURNALNYA :

#### Pembelian tanpa PPN
##### Pengakuan dalam Master Aset Tetap

| No. Akun | Nama Akun           | Debit        | Kredit       |
| -------- | ------------------- | ------------ | ------------ |
| 11899    | Aktiva Dalam Proses | Rp.1.200.000 |              |
| 11401    | Mesin dan Peralatan |              | Rp.1.200.000 |

Pengakuan dalam Menu Retur Pembelian

| No. Akun | Nama Akun           | Debit        | Kredit       |
| -------- | ------------------- | ------------ | ------------ |
| 20502    | Hutang Aktiva Tetap | Rp.1.200.000 |              |
| 11899    | Aktiva Dalam Proses |              | Rp.1.200.000 |

#### Pembelian dengan PPN
##### Pengakuan dalam Master Aset Tetap

| No. Akun | Nama Akun           | Debit        | Kredit       |
| -------- | ------------------- | ------------ | ------------ |
| 11899    | Aktiva Dalam Proses | Rp.1.200.000 |              |
| 11401    | Mesin dan Peralatan |              | Rp.1.200.000 |

Pengakuan dalam Menu Retur Pembelian

| No. Akun | Nama Akun           | Debit        | Kredit       |
| -------- | ------------------- | ------------ | ------------ |
| 20502    | Hutang Aktiva Tetap | Rp.1.320.000 |              |
| 10701    | PPN Masukan         |              | Rp.120.000   |
| 11899    | Aktiva Dalam Proses |              | Rp.1.200.000 |