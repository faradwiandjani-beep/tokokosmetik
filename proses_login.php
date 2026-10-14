<?php

session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM user 
     WHERE username='$username' 
     AND password='$password'"
);

$data = mysqli_fetch_assoc($query);

if ($data) {

    $_SESSION['login'] = true;
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['role'] = $data['role'];

    if ($data['role'] == 'admin') {

        header("Location: admin/index.php");

    } elseif ($data['role'] == 'kasir') {

        header("Location: kasir/index.php");

    }

    exit;

} else {

    echo "<script>
            alert('Username atau password salah!');
            window.location='login.php';
          </script>";
}
?>