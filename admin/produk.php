<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['tambah'])) {

    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kategori = (int) $_POST['id_kategori'];
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    mysqli_query($koneksi, "INSERT INTO produk
        (id_kategori, nama_produk, harga, stok)
        VALUES
        ($id_kategori, '$nama_produk', $harga, $stok)
    ");

    header("Location: produk.php");
    exit;
}

if (isset($_POST['edit'])) {

    $id_produk = (int) $_POST['id_produk'];
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kategori = (int) $_POST['id_kategori'];
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    mysqli_query($koneksi, "UPDATE produk SET
        id_kategori = $id_kategori,
        nama_produk = '$nama_produk',
        harga = $harga,
        stok = $stok
        WHERE id_produk = $id_produk
    ");

    header("Location: produk.php");
    exit;
}

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query(
        $koneksi,
        "DELETE FROM produk WHERE id_produk = $id"
    );

    header("Location: produk.php");
    exit;
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

$data = mysqli_query(
    $koneksi,
    "SELECT produk.*, kategori.nama_kategori
     FROM produk
     JOIN kategori
     ON produk.id_kategori = kategori.id_kategori
     ORDER BY produk.id_produk DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Data Produk - Toko Kosmetik</title>

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
    width: 95%;
    max-width: 1200px;
    margin: 30px auto;
}

h1 {
    color: #e91e63;
}

.kembali {
    color: #e91e63;
    text-decoration: none;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,.08);
    margin-top: 20px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
}

input,
select {
    width: 100%;
    padding: 11px;
    border: 1px solid #ddd;
    border-radius: 7px;
}

button {
    padding: 11px 20px;
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
    background: #ff9800;
    color: white;
    padding: 7px 12px;
    text-decoration: none;
    border-radius: 5px;
}

.edit:hover {
    background: #e68900;
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

.stok-habis {
    color: red;
    font-weight: bold;
}

.stok-aman {
    color: green;
    font-weight: bold;
}

.batal {
    background: #777;
    text-decoration: none;
    color: white;
    padding: 11px 20px;
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

<h1>Data Produk Kosmetik</h1>


<?php

if (isset($_GET['edit'])) {

    $id_edit = (int) $_GET['edit'];

    $query_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM produk WHERE id_produk = $id_edit"
    );

    $edit_data = mysqli_fetch_assoc($query_edit);

    if ($edit_data) {
?>

<div class="card">

<h3>Edit Produk</h3>

<form method="POST">

<input
    type="hidden"
    name="id_produk"
    value="<?= $edit_data['id_produk']; ?>"
>


<div class="form-group">

<label>Nama Produk</label>

<input
    type="text"
    name="nama_produk"
    value="<?= htmlspecialchars($edit_data['nama_produk']); ?>"
    required
>

</div>


<div class="form-group">

<label>Kategori</label>

<select name="id_kategori" required>

<option value="">
    -- Pilih Kategori --
</option>

<?php

$kategori_edit = mysqli_query(
    $koneksi,
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

while ($kat = mysqli_fetch_assoc($kategori_edit)) {

?>

<option
    value="<?= $kat['id_kategori']; ?>"
    <?= ($kat['id_kategori'] == $edit_data['id_kategori']) ? 'selected' : ''; ?>
>
    <?= htmlspecialchars($kat['nama_kategori']); ?>
</option>

<?php } ?>

</select>

</div>


<div class="form-group">

<label>Harga</label>

<input
    type="number"
    name="harga"
    value="<?= $edit_data['harga']; ?>"
    min="0"
    required
>

</div>


<div class="form-group">

<label>Stok</label>

<input
    type="number"
    name="stok"
    value="<?= $edit_data['stok']; ?>"
    min="0"
    required
>

</div>


<button type="submit" name="edit">
    Simpan Perubahan
</button>

<a href="produk.php" class="batal">
    Batal
</a>

</form>

</div>

<?php
    }
}
?>

<?php if (!isset($_GET['edit'])) { ?>

<div class="card">

<h3>Tambah Produk</h3>

<form method="POST">

<div class="form-group">

<label>Nama Produk</label>

<input
    type="text"
    name="nama_produk"
    placeholder="Contoh: Wardah Facial Wash"
    required
>

</div>


<div class="form-group">

<label>Kategori</label>

<select name="id_kategori" required>

<option value="">
    -- Pilih Kategori --
</option>

<?php while ($kat = mysqli_fetch_assoc($kategori)) { ?>

<option value="<?= $kat['id_kategori']; ?>">
    <?= htmlspecialchars($kat['nama_kategori']); ?>
</option>

<?php } ?>

</select>

</div>


<div class="form-group">

<label>Harga</label>

<input
    type="number"
    name="harga"
    min="0"
    placeholder="Contoh: 25000"
    required
>

</div>


<div class="form-group">

<label>Stok</label>

<input
    type="number"
    name="stok"
    min="0"
    placeholder="Contoh: 10"
    required
>

</div>


<button type="submit" name="tambah">
    + Tambah Produk
</button>

</form>

</div>

<?php } ?>

<div class="card">

<h3>Daftar Produk</h3>

<table>

<tr>

<th>No</th>
<th>Nama Produk</th>
<th>Kategori</th>
<th>Harga</th>
<th>Stok</th>
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
    <?= htmlspecialchars($row['nama_produk']); ?>
</td>

<td>
    <?= htmlspecialchars($row['nama_kategori']); ?>
</td>

<td>
    Rp <?= number_format($row['harga'], 0, ',', '.'); ?>
</td>

<td>

<?php if ($row['stok'] <= 0) { ?>

<span class="stok-habis">
    Habis
</span>

<?php } else { ?>

<span class="stok-aman">
    <?= $row['stok']; ?>
</span>

<?php } ?>

</td>

<td>

<a
    href="?edit=<?= $row['id_produk']; ?>"
    class="edit"
>
    Edit
</a>

<a
    href="?hapus=<?= $row['id_produk']; ?>"
    class="hapus"
    onclick="return confirm('Yakin ingin menghapus produk ini?')"
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