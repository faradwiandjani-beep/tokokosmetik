<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$data = mysqli_query($koneksi, "
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
    WHERE p.tanggal_penjualan = '$tanggal'
    ORDER BY p.id_penjualan DESC
");

$total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Laporan Penjualan</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family:Poppins,sans-serif;
    background:#fff7fa;
    color:#333;
}

.sidebar{
    position:fixed;
    width:260px;
    height:100vh;
    background:white;
    border-right:1px solid #f3dce5;
}

.logo{
    padding:28px 25px;
    border-bottom:1px solid #f5e5eb;
}

.logo h2{
    color:#e91e63;
    font-size:21px;
}

.logo p{
    color:#777;
    font-size:12px;
}

.menu{
    padding:25px 15px;
}

.menu a{
    display:block;
    padding:13px 18px;
    margin-bottom:7px;
    border-radius:10px;
    text-decoration:none;
    color:#444;
    font-size:14px;
}

.menu a:hover,
.menu .active{
    background:#fff0f5;
    color:#e91e63;
}

.main{
    margin-left:260px;
}

.topbar{
    height:75px;
    background:linear-gradient(90deg,#fff1f6,#ffd8e8);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 35px;
}

.content{
    padding:35px;
}

h1{
    font-size:28px;
}

.subtitle{
    color:#777;
    margin-top:5px;
}

.line{
    height:2px;
    background:#f8a9c5;
    margin:25px 0;
}

.card{
    background:white;
    padding:20px;
    border-radius:16px;
    box-shadow:0 4px 15px rgba(0,0,0,.06);
}

.form-cari{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.form-cari input{
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
}

.btn{
    border:0;
    padding:10px 18px;
    border-radius:8px;
    cursor:pointer;
    text-decoration:none;
}

.cari{
    background:#e91e63;
    color:white;
}

.cetak{
    background:#333;
    color:white;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#fff0f5;
    padding:13px;
    text-align:left;
    font-size:13px;
}

td{
    padding:13px;
    border-bottom:1px solid #f2e3e8;
    font-size:13px;
}

.total{
    color:#e91e63;
    font-weight:600;
}

.footer{
    text-align:center;
    padding:25px;
    color:#888;
    font-size:12px;
}

@media print{
    .sidebar,
    .topbar,
    .form-cari,
    .footer{
        display:none;
    }

    .main{
        margin:0;
    }

    .content{
        padding:20px;
    }

    body{
        background:white;
    }

    .card{
        box-shadow:none;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <div class="logo">
        <h2>💄 TOKO KOSMETIK</h2>
        <p>Sistem Penjualan</p>
    </div>

    <div class="menu">

        <a href="index.php">⌂ Dashboard</a>

        <a href="kategori.php">🏷️ Kategori</a>

        <a href="produk.php">💄 Produk</a>

        <a href="pelanggan.php">👥 Pelanggan</a>

        <a href="laporan.php" class="active">
            📊 Laporan Penjualan
        </a>

        <a href="../logout.php">↪ Logout</a>

    </div>

</div>

<div class="main">

    <div class="topbar">
        <b>📊 Laporan Penjualan</b>
        <span>👤 Administrator</span>
    </div>

    <div class="content">

        <h1>Laporan Penjualan</h1>

        <p class="subtitle">
            Laporan tanggal <?= date('d/m/Y', strtotime($tanggal)); ?>
        </p>

        <div class="line"></div>

        <div class="card">

            <form method="GET" class="form-cari">

                <input
                    type="date"
                    name="tanggal"
                    value="<?= htmlspecialchars($tanggal); ?>"
                >

                <button class="btn cari">
                    🔍 Cari
                </button>

                <button
                    type="button"
                    class="btn cetak"
                    onclick="window.print()"
                >
                    🖨️ Cetak
                </button>

            </form>

            <table>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Kembalian</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $no = 1;

                if (mysqli_num_rows($data) > 0) {

                    while ($row = mysqli_fetch_assoc($data)) {

                        $total += $row['total_harga'];
                ?>

                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            #<?= $row['id_penjualan']; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama_pelanggan'] ?? 'Umum'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama_user'] ?? '-'); ?>
                        </td>

                        <td class="total">
                            Rp <?= number_format($row['total_harga'],0,',','.'); ?>
                        </td>

                        <td>
                            Rp <?= number_format($row['bayar'],0,',','.'); ?>
                        </td>

                        <td>
                            Rp <?= number_format($row['kembalian'],0,',','.'); ?>
                        </td>
                    </tr>

                <?php
                    }
                } else {
                ?>

                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:#999;">
                            Belum ada transaksi pada tanggal ini.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

                <?php if (mysqli_num_rows($data) > 0) { ?>

                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:600;">
                            TOTAL PENJUALAN
                        </td>

                        <td class="total">
                            Rp <?= number_format($total,0,',','.'); ?>
                        </td>

                        <td colspan="2"></td>
                    </tr>
                </tfoot>

                <?php } ?>

            </table>

        </div>

    </div>

    <div class="footer">
        © 2026 Toko Kosmetik
    </div>

</div>

</body>
</html>