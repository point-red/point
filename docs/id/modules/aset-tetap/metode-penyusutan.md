# METODE PENYUSUTAN


Metode Penyusutan merupakan Metode yang dipilih untuk memperhitungkan jumlah nominal penyusutan atas aset tetap pada satu periode.

Metode Penyusutan memiliki fungsi untuk memberikan pilihan kepada pengguna dalam menentukan tarif penyusutan tiap satu periode hingga nominal aset tetap mencapai nilai residu.

Properti : 

| Column            | Type      | Description | Relationship |
| ----------------- | --------- | --- | --- |
| Nama Tipe Aset    | varchar   | | |
| Metode Penyusutan | int       |     |     |
| Masa Manfaat      | int       |     |     |
| Tarif Penyusutan  | decimal   |     |     |
| created_at        | timestamp | | |
| updated_at        | timestamp | | &nbsp; |

Mengapa diperlukan membuat metode penyusutannya terlebih dahulu? karena ada kemungkinan satu tipe aset tetap memiliki umur manfaat yang berbeda

Contoh : Perusahaan memiliki 5 printer yang digunakan dalam satu kantor. Printer ini diperkirakan memiliki masa manfaat selama 4 tahun. Dan perusahaan juga memiliki Meja dan Kursi yang perkiraan masa manfaatnya dapat digunakan selama 8 tahun.

Kedua barang tersebut merupakan satu tipe aset dalam neraca yaitu Peralatan. Maka cara inputnya:

| Column            | Type          | Description |
| ----------------- | ------------- | ----------- |
| Nama Tipe Aset    | Peralatan 1   |             |
| Metode Penyusutan | Garis Lurus   |             |
| Masa Manfaat      | 4 thn         |             |
| Tarif Penyusutan  | 25%           |             |

| Column            | Type          | Description |
| ----------------- | ------------- | ----------- |
| Nama Tipe Aset    | Peralatan 2   |             |
| Metode Penyusutan | Garis Lurus   |             |
| Masa Manfaat      | 8 thn         |             |
| Tarif Penyusutan  | 12,5%         |             |

Apabila akan melakukan input aset tetap namun metode penyusutan sudah pernah dibuat sebelumnya. maka tidak perlu membuat lagi.