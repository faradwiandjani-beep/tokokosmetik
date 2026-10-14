<?php
session_start();

if (!isset($_SESSION['login'])) {

    header("Location: login.php");
    exit;

}

if ($_SESSION['role'] == 'admin') {

    header("Location: admin/index.php");
    exit;

}

if ($_SESSION['role'] == 'kasir') {

    header("Location: kasir/index.php");
    exit;

}

session_destroy();

header("Location: login.php");
exit;
?>