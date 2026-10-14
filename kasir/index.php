<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'kasir') {
    header("Location: ../login.php");
    exit;
}

$nama_kasir = $_SESSION['nama'];
$id_user = $_SESSION['id_user'];

$q_transaksi = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM penjualan
    WHERE id_user = '$id_user'
    AND DATE(tanggal_penjualan) = CURDATE()
");

$transaksi_hari_ini = 0;

if ($q_transaksi) {
    $data = mysqli_fetch_assoc($q_transaksi);
    $transaksi_hari_ini = $data['total'];
}

$q_total = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total
    FROM penjualan
    WHERE id_user = '$id_user'
    AND DATE(tanggal_penjualan) = CURDATE()
");

$total_penjualan = 0;

if ($q_total) {
    $data = mysqli_fetch_assoc($q_total);
    $total_penjualan = $data['total'];
}

$item = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(dp.jumlah_produk), 0) AS total
    FROM detail_penjualan dp
    JOIN penjualan p
        ON dp.id_penjualan = p.id_penjualan
    WHERE p.id_user = '$id_user'
    AND DATE(p.tanggal_penjualan) = CURDATE()
");

$total_item = 0;

if ($item) {
    $data = mysqli_fetch_assoc($item);
    $total_item = $data['total'];
}

$q_pelanggan = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM pelanggan
");

$total_pelanggan = 0;

if ($q_pelanggan) {
    $data = mysqli_fetch_assoc($q_pelanggan);
    $total_pelanggan = $data['total'];
}

$transaksi = mysqli_query($koneksi, "
    SELECT
        p.id_penjualan,
        p.tanggal_penjualan,
        p.total_harga,
        pl.nama_pelanggan
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan
    WHERE p.id_user = '$id_user'
    ORDER BY p.id_penjualan DESC
    LIMIT 5
");

?>


<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Kasir - Toko Kosmetik</title>

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

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

            display: flex;
            flex-direction: column;

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

            padding: 14px 18px;

            border-radius: 10px;

            margin-bottom: 8px;

            font-size: 14px;

            transition: .3s;
        }


        .menu a:hover,
        .menu a.active {
            background: #fff0f5;
            color: #e91e63;
        }


        .menu .icon {
            font-size: 20px;
            width: 35px;
        }

        .main {
            margin-left: 260px;

            min-height: 100vh;
        }

        .topbar {
            height: 75px;

            background: linear-gradient(
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
        }


        .line {
            height: 2px;

            background: #f8a9c5;

            margin: 25px 0;
        }

        .stats {
            display: grid;

            grid-template-columns:
            repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {
            background: white;

            border-radius: 16px;

            padding: 25px;

            display: flex;

            align-items: center;

            gap: 18px;

            box-shadow:
                0 4px 15px rgba(0,0,0,.06);
        }


        .stat-icon {
            width: 60px;
            height: 60px;

            border-radius: 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 27px;
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

            font-size: 13px;
        }


        .stat-info h2 {
            font-size: 23px;

            margin: 3px 0;
        }

        .dashboard-grid {
            display: grid;

            grid-template-columns:
            1fr 1.6fr;

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
            display: flex;

            align-items: center;

            gap: 10px;

            font-size: 18px;

            font-weight: 600;

            margin-bottom: 20px;
        }

        .transaction-box {
            text-align: center;

            padding: 30px 10px;
        }


        .cosmetic-icon {
            width: 120px;
            height: 120px;

            border-radius: 50%;

            background: #fff0f5;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 60px;

            margin: auto;

            margin-bottom: 20px;
        }


        .transaction-box h2 {
            font-size: 19px;

            margin-bottom: 8px;
        }


        .transaction-box p {
            color: #777;

            font-size: 13px;

            margin-bottom: 20px;
        }


        .btn-transaksi {
            display: inline-block;

            background: #e91e63;

            color: white;

            text-decoration: none;

            padding: 12px 25px;

            border-radius: 8px;

            font-size: 14px;

            transition: .3s;
        }


        .btn-transaksi:hover {
            background: #c2185b;
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

            color: #444;

            padding: 14px;

            text-align: left;

            font-size: 13px;
        }


        td {
            padding: 14px;

            border-bottom: 1px solid #f3e6eb;

            font-size: 13px;
        }


        .empty {
            text-align: center;

            padding: 50px 10px;

            color: #999;
        }


        .empty-icon {
            font-size: 45px;

            margin-bottom: 10px;
        }

        .footer {
            text-align: center;

            padding: 25px;

            color: #888;

            font-size: 12px;

            margin-top: 30px;
        }

        @media(max-width: 1000px) {

            .stats {
                grid-template-columns:
                repeat(2, 1fr);
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
            .menu span:not(.icon) {
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


            <a href="penjualan.php">

                <span class="icon">🛒</span>

                <span>Transaksi Penjualan</span>

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
                        <?= htmlspecialchars($nama_kasir); ?>
                    </strong>

                    <span>
                        Kasir
                    </span>

                </div>

            </div>

        </div>

        <div class="content">

            <div class="title">

                <h1>
                    Dashboard Kasir
                </h1>

                <p>
                    Selamat datang,
                    <b>
                        <?= htmlspecialchars($nama_kasir); ?>
                    </b>
                    👋
                </p>

                <p>
                    Kelola transaksi penjualan dengan mudah dan cepat.
                </p>

            </div>


            <div class="line"></div>

            <div class="stats">

                <div class="stat-card">

                    <div class="stat-icon icon-pink">
                        🛒
                    </div>

                    <div class="stat-info">

                        <p>
                            Transaksi Hari Ini
                        </p>

                        <h2>
                            <?= $transaksi_hari_ini; ?>
                        </h2>

                        <p>
                            Transaksi
                        </p>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon icon-purple">
                        💳
                    </div>

                    <div class="stat-info">

                        <p>
                            Total Penjualan
                        </p>

                        <h2>
                            Rp <?= number_format(
                                $total_penjualan,
                                0,
                                ',',
                                '.'
                            ); ?>
                        </h2>

                        <p>
                            Hari Ini
                        </p>

                    </div>

                </div>



                <!-- TOTAL ITEM -->

                <div class="stat-card">

                    <div class="stat-icon icon-green">
                        🛍️
                    </div>

                    <div class="stat-info">

                        <p>
                            Total Item Terjual
                        </p>

                        <h2>
                            <?= $total_item; ?>
                        </h2>

                        <p>
                            Item
                        </p>

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
                        🛍️ Menu Kasir
                    </div>


                    <div class="transaction-box">

                        <div class="cosmetic-icon">
                            💄
                        </div>


                        <h2>
                            Mulai Transaksi Penjualan
                        </h2>


                        <p>
                            Klik tombol di bawah untuk melakukan
                            transaksi penjualan baru.
                        </p>


                        <a
                            href="penjualan.php"
                            class="btn-transaksi"
                        >
                            🛒 Buat Transaksi Baru
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

                                    <th>
                                        No. Nota
                                    </th>

                                    <th>
                                        Tanggal
                                    </th>

                                    <th>
                                        Total
                                    </th>
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
                                            'd/m/Y H:i',
                                            strtotime(
                                                $row['tanggal_penjualan']
                                            )
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

                                    <td>

                                        <?= htmlspecialchars(
                                            $nama_kasir
                                        ); ?>

                                    </td>

                                </tr>


                            <?php

                                }

                            } else {

                            ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="empty"
                                    >

                                        <div class="empty-icon">
                                            📋
                                        </div>


                                        <strong>
                                            Belum ada transaksi
                                        </strong>


                                        <br>


                                        Transaksi penjualan akan muncul
                                        di sini

                                    </td>

                                </tr>

                            <?php

                            }

                            ?>


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