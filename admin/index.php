<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}


$nama_admin = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'Admin';

$produk = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM produk
");

$total_produk = mysqli_fetch_assoc($produk)['total'];

$kategori = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM kategori
");

$total_kategori = mysqli_fetch_assoc($kategori)['total'];

$pelanggan = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT nama_pelanggan) AS total
    FROM penjualan
    WHERE nama_pelanggan IS NOT NULL
    AND nama_pelanggan != ''
");

$total_pelanggan = mysqli_fetch_assoc($pelanggan)['total'];

$penjualan = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total
    FROM penjualan
");

$total_penjualan = mysqli_fetch_assoc($penjualan)['total'];

$transaksi = mysqli_query($koneksi, "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        p.nama_pelanggan,
        u.nama_user
    FROM penjualan p
    JOIN user u
        ON p.id_user = u.id_user
    ORDER BY p.id_penjualan DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard Admin - Toko Kosmetik</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect"
href="https://fonts.googleapis.com"
crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #fff7fa;
    color: #333;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    height: 100vh;
    background: white;
    border-right: 1px solid #f3dce5;
    z-index: 100;
}

.logo {
    padding: 28px 25px;
    border-bottom: 1px solid #f5e5eb;
}

.logo h2 {
    color: #e91e63;
    font-size: 22px;
    font-weight: 700;
}

.logo p {
    color: #777;
    font-size: 12px;
    margin-top: 3px;
}

.menu {
    padding: 25px 15px;
}

.menu a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #444;
    padding: 13px 18px;
    border-radius: 10px;
    margin-bottom: 7px;
    font-size: 14px;
    transition: .3s;
}

.menu a:hover,
.menu a.active {
    background: #fff0f5;
    color: #e91e63;
}

.menu .icon {
    width: 35px;
    font-size: 19px;
}

.main {
    margin-left: 260px;
    min-height: 100vh;
}

.topbar {
    height: 75px;

    background:
        linear-gradient(
            90deg,
            #fff1f6,
            #ffd8e8
        );

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 35px;

    border-bottom: 1px solid #f4d6e2;
}

.topbar-left {
    font-size: 24px;
    color: #e91e63;
}

.profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #e91e63;
    color: white;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 20px;
}

.profile-info strong {
    display: block;
    font-size: 14px;
}

.profile-info span {
    color: #777;
    font-size: 12px;
}

.content {
    padding: 35px;
}

.title h1 {
    font-size: 30px;
    margin-bottom: 5px;
}

.title p {
    color: #777;
    font-size: 14px;
    margin-bottom: 3px;
}

.line {
    height: 2px;
    background: #f8a9c5;
    margin: 25px 0;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 23px;

    display: flex;
    align-items: center;

    gap: 16px;

    box-shadow:
        0 4px 15px rgba(0,0,0,.06);
}

.stat-icon {
    width: 58px;
    height: 58px;

    border-radius: 15px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 25px;
}

.icon-pink {
    background: #ffe0eb;
}

.icon-purple {
    background: #eee0ff;
}

.icon-green {
    background: #ddf8e8;
}

.icon-orange {
    background: #fff0d5;
}

.stat-info p {
    color: #777;
    font-size: 12px;
}

.stat-info h2 {
    font-size: 21px;
    margin: 3px 0;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 25px;
}

.card {
    background: white;
    border-radius: 16px;
    padding: 25px;

    box-shadow:
        0 4px 15px rgba(0,0,0,.06);
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
}

.admin-menu {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.admin-menu a {
    text-decoration: none;
    color: #444;
    background: #fff7fa;
    padding: 18px;
    border-radius: 12px;
    text-align: center;
    transition: .3s;
}

.admin-menu a:hover {
    background: #ffe1ec;
    color: #e91e63;
    transform: translateY(-2px);
}

.admin-menu .menu-icon {
    display: block;
    font-size: 30px;
    margin-bottom: 8px;
}

.admin-menu span {
    font-size: 13px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #fff0f5;
    padding: 13px;
    text-align: left;
    font-size: 12px;
}

td {
    padding: 13px;
    border-bottom: 1px solid #f3e6eb;
    font-size: 12px;
}

.empty {
    text-align: center;
    padding: 45px 10px;
    color: #999;
}

.empty-icon {
    font-size: 40px;
    margin-bottom: 8px;
}

.footer {
    text-align: center;
    padding: 25px;
    color: #888;
    font-size: 12px;
    margin-top: 30px;
}

@media(max-width: 1100px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

}

@media(max-width: 700px) {

    .sidebar {
        width: 70px;
    }

    .logo h2,
    .logo p,
    .menu a span:not(.icon) {
        display: none;
    }

    .main {
        margin-left: 70px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .content {
        padding: 20px;
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

        <a href="index.php" class="active">

            <span class="icon">⌂</span>

            <span>Dashboard</span>

        </a>


        <a href="kategori.php">

            <span class="icon">🏷️</span>

            <span>Kategori</span>

        </a>


        <a href="produk.php">

            <span class="icon">💄</span>

            <span>Produk</span>

        </a>


        <a href="pelanggan.php">

            <span class="icon">👥</span>

            <span>Pelanggan</span>

        </a>


        <a href="laporan.php">

            <span class="icon">📊</span>

            <span>Laporan Penjualan</span>

        </a>


        <a href="../logout.php">

            <span class="icon">↪</span>

            <span>Logout</span>

        </a>

    </div>

</div>

<div class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div class="topbar-left">
            ☰
        </div>


        <div class="profile">

            <div class="profile-icon">
                👤
            </div>

            <div class="profile-info">

                <strong>
                    <?= htmlspecialchars($nama_admin); ?>
                </strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>

    </div>

    <div class="content">


        <div class="title">

            <h1>Dashboard Admin</h1>

            <p>
                Selamat datang,
                <b><?= htmlspecialchars($nama_admin); ?></b> 👋
            </p>

            <p>
                Kelola data toko kosmetik dengan mudah dan cepat.
            </p>

        </div>


        <div class="line"></div>

        <div class="stats">

            <div class="stat-card">

                <div class="stat-icon icon-pink">
                    💄
                </div>

                <div class="stat-info">

                    <p>Total Produk</p>

                    <h2>
                        <?= $total_produk; ?>
                    </h2>

                    <p>Produk</p>

                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon icon-purple">
                    🏷️
                </div>

                <div class="stat-info">

                    <p>Total Kategori</p>

                    <h2>
                        <?= $total_kategori; ?>
                    </h2>

                    <p>Kategori</p>

                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon icon-orange">
                    💰
                </div>

                <div class="stat-info">

                    <p>Total Penjualan</p>

                    <h2>
                        Rp <?= number_format(
                            $total_penjualan,
                            0,
                            ',',
                            '.'
                        ); ?>
                    </h2>

                    <p>Semua Transaksi</p>

                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon icon-green">
                    👥
                </div>

                <div class="stat-info">

                    <p>Total Pelanggan</p>

                    <h2>
                        <?= $total_pelanggan; ?>
                    </h2>

                    <p>Pelanggan</p>

                </div>

            </div>

        </div>

        <div class="dashboard-grid">

            <div class="card">

                <div class="card-title">
                    ⚙️ Menu Admin
                </div>


                <div class="admin-menu">


                    <a href="kategori.php">

                        <span class="menu-icon">
                            🏷️
                        </span>

                        <span>
                            Kategori
                        </span>

                    </a>


                    <a href="produk.php">

                        <span class="menu-icon">
                            💄
                        </span>

                        <span>
                            Produk
                        </span>

                    </a>


                    <a href="pelanggan.php">

                        <span class="menu-icon">
                            👥
                        </span>

                        <span>
                            Pelanggan
                        </span>

                    </a>


                    <a href="laporan.php">

                        <span class="menu-icon">
                            📊
                        </span>

                        <span>
                            Laporan
                        </span>

                    </a>

                </div>

            </div>

            <div class="card">

                <div class="card-title">

                    📋 Transaksi Terakhir

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>No. Nota</th>

                                <th>Tanggal</th>

                                <th>Total</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php

                        if ($transaksi && mysqli_num_rows($transaksi) > 0) {

                            while ($row = mysqli_fetch_assoc($transaksi)) {

                        ?>

                        <tr>

                            <td>
                                #<?= $row['id_penjualan']; ?>
                            </td>


                            <td>
                                <?= date(
                                    'd/m/Y',
                                    strtotime($row['tanggal_penjualan'])
                                ); ?>
                            </td>


                            <td>
                                Rp <?= number_format(
                                    $row['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </td>

                        </tr>

                        <?php

                            }

                        } else {

                        ?>

                        <tr>

                            <td
                                colspan="3"
                                class="empty"
                            >

                                <div class="empty-icon">
                                    📋
                                </div>

                                <strong>
                                    Belum ada transaksi
                                </strong>

                                <br>

                                Transaksi penjualan akan muncul di sini

                            </td>

                        </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </div>

    <div class="footer">

        © 2026 Toko Kosmetik. All rights reserved.

    </div>


</div>


</body>

</html>