<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}



$query = mysqli_query($koneksi, "
    SELECT
        pl.id_pelanggan,
        pl.nama_pelanggan,
        COUNT(p.id_penjualan) AS jumlah_transaksi,
        MAX(p.tanggal_penjualan) AS transaksi_terakhir
    FROM pelanggan pl
    LEFT JOIN penjualan p
        ON pl.id_pelanggan = p.id_pelanggan
    GROUP BY
        pl.id_pelanggan,
        pl.nama_pelanggan
    ORDER BY pl.nama_pelanggan ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Pelanggan</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <style>

        body {
            font-family: 'Poppins', sans-serif;
            background: #fff7fa;
            color: #444;
        }

        .navbar {
            background: #ff4f88;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
        }

        .container-box {
            max-width: 1100px;
            margin: 40px auto;
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow:
                0 5px 20px
                rgba(255, 79, 136, 0.10);
        }

        .card-header {
            background: #ff4f88;
            color: white;
            border-radius:
                18px 18px 0 0 !important;
            padding: 18px 22px;
            font-weight: 600;
        }

        table {
            vertical-align: middle !important;
        }

        thead th {
            background: #fff0f5 !important;
            color: #555;
            padding: 14px !important;
        }

        tbody td {
            padding: 14px !important;
        }

        .badge-pink {
            background: #ff8fab;
            color: white;
            padding: 7px 12px;
            border-radius: 20px;
        }

        .badge-zero {
            background: #eee;
            color: #777;
            padding: 7px 12px;
            border-radius: 20px;
        }

        .empty {
            padding: 50px;
            text-align: center;
            color: #999;
        }

        .btn-back {
            background: white;
            color: #ff4f88;
            border: none;
        }

        .btn-back:hover {
            background: #fff0f5;
            color: #ff4f88;
        }

    </style>

</head>


<body>


<nav class="navbar">

    <div class="container">

        <a
            href="index.php"
            class="navbar-brand"
        >
            💄 Admin Toko Kosmetik
        </a>


        <a
            href="index.php"
            class="btn btn-back btn-sm"
        >
            ← Dashboard
        </a>

    </div>

</nav>



<div class="container container-box">


    <div class="card">


        <div class="card-header">

            👥 Data Pelanggan

        </div>


        <div class="card-body p-4">


            <p class="text-muted mb-4">

                Data pelanggan otomatis berasal
                dari transaksi yang dilakukan oleh kasir.

            </p>


            <div class="table-responsive">


                <table class="table table-hover">


                    <thead>

                        <tr>

                            <th width="70">
                                No
                            </th>

                            <th>
                                Nama Pelanggan
                            </th>

                            <th>
                                Jumlah Transaksi
                            </th>

                            <th>
                                Transaksi Terakhir
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    if (
                        $query &&
                        mysqli_num_rows($query) > 0
                    ) {

                        while (
                            $data =
                            mysqli_fetch_assoc($query)
                        ) {

                    ?>


                        <tr>


                            <td>
                                <?= $no++ ?>
                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $data['nama_pelanggan']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?php
                                if (
                                    $data['jumlah_transaksi'] > 0
                                ) {
                                ?>

                                    <span class="badge-pink">

                                        <?= $data['jumlah_transaksi'] ?>

                                        transaksi

                                    </span>

                                <?php
                                } else {
                                ?>

                                    <span class="badge-zero">

                                        Belum transaksi

                                    </span>

                                <?php
                                }
                                ?>

                            </td>


                            <td>

                                <?php
                                if (
                                    !empty(
                                        $data['transaksi_terakhir']
                                    )
                                ) {

                                    echo date(
                                        'd-m-Y',
                                        strtotime(
                                            $data[
                                                'transaksi_terakhir'
                                            ]
                                        )
                                    );

                                } else {

                                    echo '-';

                                }
                                ?>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="4"
                                class="empty"
                            >

                                👤 Belum ada data pelanggan.

                                <br>

                                Data akan muncul setelah
                                ada transaksi dari kasir.

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


</body>

</html>