<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['tambah'])) {

    $nama_kategori = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama_kategori']
    );

    mysqli_query(
        $koneksi,
        "INSERT INTO kategori (nama_kategori)
         VALUES ('$nama_kategori')"
    );

    header("Location: kategori.php");
    exit;
}

if (isset($_POST['edit'])) {

    $id = (int) $_POST['id_kategori'];

    $nama_kategori = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama_kategori']
    );

    mysqli_query(
        $koneksi,
        "UPDATE kategori
         SET nama_kategori = '$nama_kategori'
         WHERE id_kategori = $id"
    );

    header("Location: kategori.php");
    exit;
}

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM kategori
         WHERE id_kategori = $id"
    );

    header("Location: kategori.php");
    exit;
}

$edit_data = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $query_edit = mysqli_query(
        $koneksi,
        "SELECT *
         FROM kategori
         WHERE id_kategori = $id"
    );

    $edit_data = mysqli_fetch_assoc($query_edit);
}

$data = mysqli_query(
    $koneksi,
    "SELECT *
     FROM kategori
     ORDER BY id_kategori DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Data Kategori</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff5f8;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
        }

        h1 {
            color: #e91e63;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,.08);
            margin-bottom: 25px;
        }

        input {
            width: 70%;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        button {
            padding: 11px 18px;
            border: none;
            border-radius: 7px;
            background: #e91e63;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #c2185b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #fce4ec;
            color: #ad1457;
        }

        .edit {
            background: #ffc107;
            color: #333;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 5px;
        }

        .edit:hover {
            background: #e0a800;
        }

        .hapus {
            background: #dc3545;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .hapus:hover {
            background: #b02a37;
        }

        .kembali {
            display: inline-block;
            margin-bottom: 20px;
            color: #e91e63;
            text-decoration: none;
        }

        .batal {
            display: inline-block;
            padding: 11px 18px;
            background: #777;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            margin-left: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <a href="index.php" class="kembali">
        ← Kembali ke Dashboard
    </a>

    <h1>Data Kategori Kosmetik</h1>

    <div class="card">

        <?php if ($edit_data) { ?>

            <h3>Edit Kategori</h3>

            <form method="POST">

                <input
                    type="hidden"
                    name="id_kategori"
                    value="<?= $edit_data['id_kategori']; ?>"
                >

                <input
                    type="text"
                    name="nama_kategori"
                    value="<?= htmlspecialchars(
                        $edit_data['nama_kategori']
                    ); ?>"
                    required
                >

                <button
                    type="submit"
                    name="edit"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="kategori.php"
                    class="batal"
                >
                    Batal
                </a>

            </form>

        <?php } else { ?>

            <h3>Tambah Kategori</h3>

            <form method="POST">

                <input
                    type="text"
                    name="nama_kategori"
                    placeholder="Contoh: Skincare"
                    required
                >

                <button
                    type="submit"
                    name="tambah"
                >
                    + Tambah
                </button>

            </form>

        <?php } ?>

    </div>

    <div class="card">

        <h3>Daftar Kategori</h3>

        <table>

            <tr>

                <th>No</th>

                <th>Nama Kategori</th>

                <th>Aksi</th>

            </tr>


            <?php

            $no = 1;

            while ($row = mysqli_fetch_assoc($data)) {

            ?>

            <tr>

                <td>
                    <?= $no++; ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $row['nama_kategori']
                    ); ?>
                </td>

                <td>

                    <a
                        href="?edit=<?= $row['id_kategori']; ?>"
                        class="edit"
                    >
                        Edit
                    </a>


                    <a
                        href="?hapus=<?= $row['id_kategori']; ?>"
                        class="hapus"
                        onclick="return confirm(
                            'Yakin ingin menghapus kategori ini?'
                        )"
                    >
                        Hapus
                    </a>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>