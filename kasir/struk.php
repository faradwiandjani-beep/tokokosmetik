<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$id_penjualan = (int)($_GET['id'] ?? 0);

if ($id_penjualan <= 0) {
    die("Data penjualan tidak ditemukan.");
}

$query = mysqli_query($koneksi, "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        p.bayar,
        p.kembalian,
        pl.nama_pelanggan,
        u.nama_user
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    LEFT JOIN user u
        ON p.id_user = u.id_user
    WHERE p.id_penjualan = '$id_penjualan'
    LIMIT 1
");

if (!$query || mysqli_num_rows($query) == 0) {
    die("Data penjualan tidak ditemukan.");
}

$penjualan = mysqli_fetch_assoc($query);

$detail = mysqli_query($koneksi, "
    SELECT
        dp.id_detail,
        dp.jumlah_produk,
        dp.subtotal,
        pr.nama_produk,
        pr.harga
    FROM detail_penjualan dp
    JOIN produk pr
        ON dp.id_produk = pr.id_produk
    WHERE dp.id_penjualan = '$id_penjualan'
    ORDER BY dp.id_detail ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Struk Penjualan</title>

<style>

body {
    margin: 0;
    padding: 30px;
    background: #fff7fa;
    font-family: Arial, sans-serif;
    color: #333;
}

.struk {
    width: 380px;
    margin: auto;
    background: white;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.judul {
    text-align: center;
    margin-bottom: 20px;
}

.judul h2 {
    margin: 0;
    color: #ff4f88;
}

.judul p {
    margin: 5px 0;
    font-size: 13px;
}

.garis {
    border-top: 1px dashed #999;
    margin: 15px 0;
}

.info {
    font-size: 14px;
    line-height: 1.8;
}

.info div {
    display: flex;
}

.info span:first-child {
    width: 120px;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

th,
td {
    padding: 8px 3px;
    border-bottom: 1px dashed #ddd;
}

th {
    text-align: left;
}

.angka {
    text-align: right;
}

.total {
    margin-top: 15px;
    font-size: 14px;
}

.total div {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
}

.total .grand {
    font-size: 18px;
    font-weight: bold;
    color: #ff4f88;
}

.footer {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
}

.tombol {
    text-align: center;
    margin-top: 20px;
}

button {
    border: none;
    background: #ff4f88;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
}

a {
    text-decoration: none;
    margin-left: 5px;
}

@media print {

    body {
        background: white;
        padding: 0;
    }

    .struk {
        width: 100%;
        box-shadow: none;
        padding: 10px;
    }

    .tombol {
        display: none;
    }

}

</style>

</head>

<body>

<div class="struk">

    <div class="judul">

        <h2>TOKO KOSMETIK</h2>

        <p>Struk Penjualan</p>

    </div>


    <div class="garis"></div>


    <!-- DATA TRANSAKSI -->

    <div class="info">

        <div>
            <span>No. Transaksi</span>
            <span>
                : <?= $penjualan['id_penjualan']; ?>
            </span>
        </div>

        <div>
            <span>Tanggal</span>
            <span>
                :
                <?= date(
                    'd/m/Y',
                    strtotime(
                        $penjualan['tanggal_penjualan']
                    )
                ); ?>
            </span>
        </div>

        <div>
            <span>Pelanggan</span>
            <span>
                :
                <?= htmlspecialchars(
                    $penjualan['nama_pelanggan']
                    ?? '-'
                ); ?>
            </span>
        </div>

        <div>
            <span>Kasir</span>
            <span>
                :
                <?= htmlspecialchars(
                    $penjualan['nama_user']
                    ?? '-'
                ); ?>
            </span>
        </div>

    </div>


    <div class="garis"></div>

    <table>

        <thead>

            <tr>

                <th>Produk</th>

                <th>Qty</th>

                <th class="angka">
                    Subtotal
                </th>

            </tr>

        </thead>

        <tbody>

        <?php while ($d = mysqli_fetch_assoc($detail)) { ?>

            <tr>

                <td>
                    <?= htmlspecialchars(
                        $d['nama_produk']
                    ); ?>
                </td>

                <td>
                    <?= $d['jumlah_produk']; ?>
                </td>

                <td class="angka">

                    Rp <?= number_format(
                        $d['subtotal'],
                        0,
                        ',',
                        '.'
                    ); ?>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>


    <div class="garis"></div>
    
    <div class="total">

        <div class="grand">

            <span>
                Total
            </span>

            <span>
                Rp <?= number_format(
                    $penjualan['total_harga'],
                    0,
                    ',',
                    '.'
                ); ?>
            </span>

        </div>


        <div>

            <span>
                Bayar
            </span>

            <span>
                Rp <?= number_format(
                    $penjualan['bayar'],
                    0,
                    ',',
                    '.'
                ); ?>
            </span>

        </div>


        <div>

            <span>
                Kembalian
            </span>

            <span>
                Rp <?= number_format(
                    $penjualan['kembalian'],
                    0,
                    ',',
                    '.'
                ); ?>
            </span>

        </div>

    </div>


    <div class="garis"></div>


    <div class="footer">

        <strong>
            Terima kasih sudah berbelanja 💗
        </strong>

        <br>

        Semoga harimu menyenangkan!

    </div>


    <!-- TOMBOL -->

    <div class="tombol">

        <button onclick="window.print()">
            🖨️ Cetak Struk
        </button>

        <a href="penjualan.php">
            <button type="button">
                Kembali
            </button>
        </a>

    </div>

</div>

</body>

</html>