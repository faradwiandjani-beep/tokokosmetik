<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$query = mysqli_query($koneksi, "
    SELECT 
        penjualan.id_penjualan,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        penjualan.bayar,
        penjualan.kembalian,
        pelanggan.nama_pelanggan
    FROM penjualan
    LEFT JOIN pelanggan
        ON penjualan.id_pelanggan = pelanggan.id_pelanggan
    ORDER BY penjualan.id_penjualan DESC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Data Penjualan</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    color: #333;
}

.container {
    width: 95%;
    max-width: 1200px;
    margin: 30px auto;
}

.header {
    background: #222;
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.header h2 {
    margin: 0;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th,
td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}

th {
    background: #222;
    color: white;
}

.btn {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    font-size: 13px;
    margin: 2px;
}

.btn-detail {
    background: #007bff;
}

.btn-cetak {
    background: #28a745;
}

.kosong {
    padding: 20px;
    text-align: center;
    color: #777;
}

</style>

</head>

<body>

<div class="container">

    <div class="header">
        <h2>Data Penjualan</h2>
    </div>


    <div class="card">

        <table>

            <thead>

                <tr>

                    <th>No</th>
                    <th>ID Penjualan</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Kembalian</th>
                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $no = 1;

            if (mysqli_num_rows($query) > 0) {

                while ($data = mysqli_fetch_assoc($query)) {

            ?>

                <tr>

                    <td>
                        <?= $no++; ?>
                    </td>

                    <td>
                        <?= $data['id_penjualan']; ?>
                    </td>

                    <td>
                        <?= date(
                            'd-m-Y',
                            strtotime(
                                $data['tanggal_penjualan']
                            )
                        ); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $data['nama_pelanggan']
                            ?? '-'
                        ); ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $data['total_harga'],
                            0,
                            ',',
                            '.'
                        ); ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $data['bayar'],
                            0,
                            ',',
                            '.'
                        ); ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $data['kembalian'],
                            0,
                            ',',
                            '.'
                        ); ?>
                    </td>

                    <td>

                        <a
                            href="detail_penjualan.php?id=<?= $data['id_penjualan']; ?>"
                            class="btn btn-detail"
                        >
                            Detail
                        </a>


                        <a
                            href="struk.php?id=<?= $data['id_penjualan']; ?>"
                            target="_blank"
                            class="btn btn-cetak"
                        >
                            Cetak Struk
                        </a>

                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>

                    <td
                        colspan="8"
                        class="kosong"
                    >
                        Belum ada data penjualan.
                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>