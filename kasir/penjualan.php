<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0;

if (isset($_POST['simpan'])) {

    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $bayar = (int)($_POST['bayar'] ?? 0);

    $id_produk = $_POST['id_produk'] ?? [];
    $jumlah_produk = $_POST['jumlah_produk'] ?? [];

    if ($nama_pelanggan == '') {
        echo "<script>
            alert('Nama pelanggan wajib diisi!');
            history.back();
        </script>";
        exit;
    }

    if (empty($id_produk)) {
        echo "<script>
            alert('Keranjang masih kosong!');
            history.back();
        </script>";
        exit;
    }

    $nama_pelanggan = mysqli_real_escape_string(
        $koneksi,
        $nama_pelanggan
    );

    mysqli_begin_transaction($koneksi);

    try {

        $cek_pelanggan = mysqli_query($koneksi, "
            SELECT id_pelanggan
            FROM pelanggan
            WHERE nama_pelanggan = '$nama_pelanggan'
            LIMIT 1
        ");

        if (mysqli_num_rows($cek_pelanggan) > 0) {

            $pelanggan = mysqli_fetch_assoc($cek_pelanggan);

            $id_pelanggan = $pelanggan['id_pelanggan'];

        } else {

            $buat_pelanggan = mysqli_query($koneksi, "
                INSERT INTO pelanggan
                (nama_pelanggan)
                VALUES
                ('$nama_pelanggan')
            ");

            if (!$buat_pelanggan) {
                throw new Exception(
                    mysqli_error($koneksi)
                );
            }

            $id_pelanggan = mysqli_insert_id($koneksi);
        }


        if (!$id_pelanggan) {
            throw new Exception(
                "Data pelanggan tidak ditemukan."
            );
        }

        $total_harga = 0;
        $detail = [];

        for ($i = 0; $i < count($id_produk); $i++) {

            $produk_id = (int)$id_produk[$i];
            $jumlah = (int)$jumlah_produk[$i];

            if ($jumlah <= 0) {
                throw new Exception(
                    "Jumlah produk tidak valid."
                );
            }

            $cek_produk = mysqli_query($koneksi, "
                SELECT
                    id_produk,
                    nama_produk,
                    harga,
                    stok
                FROM produk
                WHERE id_produk = '$produk_id'
                FOR UPDATE
            ");

            if (
                !$cek_produk ||
                mysqli_num_rows($cek_produk) == 0
            ) {
                throw new Exception(
                    "Produk tidak ditemukan."
                );
            }

            $produk = mysqli_fetch_assoc(
                $cek_produk
            );

            if ($jumlah > $produk['stok']) {
                throw new Exception(
                    "Stok {$produk['nama_produk']} tidak cukup. Sisa stok: {$produk['stok']}"
                );
            }

            $subtotal =
                $produk['harga'] * $jumlah;

            $total_harga += $subtotal;

            $detail[] = [
                'id_produk' => $produk_id,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal
            ];
        }

        if ($bayar < $total_harga) {
            throw new Exception(
                "Uang pembayaran kurang!"
            );
        }

        $kembalian =
            $bayar - $total_harga;

        $query_penjualan = mysqli_query(
            $koneksi,
            "
            INSERT INTO penjualan
            (
                id_user,
                id_pelanggan,
                tanggal_penjualan,
                total_harga,
                bayar,
                kembalian
            )
            VALUES
            (
                '$id_user',
                '$id_pelanggan',
                CURDATE(),
                '$total_harga',
                '$bayar',
                '$kembalian'
            )
            "
        );

        if (!$query_penjualan) {
            throw new Exception(
                mysqli_error($koneksi)
            );
        }

        $id_penjualan =
            mysqli_insert_id($koneksi);

        foreach ($detail as $d) {

            $id_produk_db =
                $d['id_produk'];

            $jumlah_db =
                $d['jumlah'];

            $subtotal_db =
                $d['subtotal'];


            $insert_detail = mysqli_query(
                $koneksi,
                "
                INSERT INTO detail_penjualan
                (
                    id_penjualan,
                    id_produk,
                    jumlah_produk,
                    subtotal
                )
                VALUES
                (
                    '$id_penjualan',
                    '$id_produk_db',
                    '$jumlah_db',
                    '$subtotal_db'
                )
                "
            );

            if (!$insert_detail) {
                throw new Exception(
                    mysqli_error($koneksi)
                );
            }


            $update_stok = mysqli_query(
                $koneksi,
                "
                UPDATE produk
                SET stok = stok - $jumlah_db
                WHERE id_produk = '$id_produk_db'
                "
            );

            if (!$update_stok) {
                throw new Exception(
                    mysqli_error($koneksi)
                );
            }
        }

        mysqli_commit($koneksi);

        header(
            "Location: struk.php?id=" .
            $id_penjualan
        );

        exit;


    } catch (Exception $e) {

        mysqli_rollback($koneksi);

        echo "<script>
            alert(" .
            json_encode(
                "Penjualan gagal: " .
                $e->getMessage()
            ) .
            ");
            history.back();
        </script>";

        exit;
    }
}

$produk = mysqli_query(
    $koneksi,
    "
    SELECT
        id_produk,
        nama_produk,
        harga,
        stok
    FROM produk
    WHERE stok > 0
    ORDER BY nama_produk ASC
    "
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Penjualan Kasir</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #fff7fa;
    color: #333;
}

.container {
    width: 95%;
    max-width: 1200px;
    margin: 30px auto;
}

.header {
    background: #ff4f88;
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
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
}

select,
input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.btn {
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn-tambah {
    background: #ff4f88;
    color: white;
}

.btn-simpan {
    background: #ff4f88;
    color: white;
    width: 100%;
    font-size: 16px;
    margin-top: 15px;
}

.btn-hapus {
    background: #dc3545;
    color: white;
    padding: 7px 12px;
}

.btn-dashboard {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 10px 16px;
    border-radius: 6px;
    background: white;
    color: #ff4f88;
    border: 1px solid #ff4f88;
    text-align: center;
    text-decoration: none;
    font-weight: bold;
}

.btn-dashboard:hover {
    background: #ff4f88;
    color: white;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th,
td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

th {
    background: #ff4f88;
    color: white;
}

.total-box {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
    margin-left: auto;
}

.total-box div {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-box strong {
    font-size: 20px;
}

.kosong {
    text-align: center;
    padding: 20px;
    color: #777;
}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h2>
            Penjualan Kasir
        </h2>

    </div>

    <div class="card">

        <h3>
            Data Pelanggan
        </h3>

        <div class="form-group">

            <label>
                Nama Pelanggan
            </label>

            <input
                type="text"
                name="nama_pelanggan"
                id="namaPelanggan"
                form="formPenjualan"
                placeholder="Ketik nama pelanggan..."
                required
            >

        </div>

    </div>

    <div class="card">

        <h3>
            Tambah Produk
        </h3>

        <div class="form-group">

            <label>
                Produk
            </label>

            <select id="produk">

                <option value="">
                    -- Pilih Produk --
                </option>

                <?php while ($pr = mysqli_fetch_assoc($produk)) { ?>

                    <option
                        value="<?= $pr['id_produk']; ?>"
                        data-nama="<?= htmlspecialchars($pr['nama_produk']); ?>"
                        data-harga="<?= $pr['harga']; ?>"
                        data-stok="<?= $pr['stok']; ?>"
                    >

                        <?= htmlspecialchars(
                            $pr['nama_produk']
                        ); ?>

                        -

                        Rp <?= number_format(
                            $pr['harga'],
                            0,
                            ',',
                            '.'
                        ); ?>

                        (Stok:
                        <?= $pr['stok']; ?>)

                    </option>

                <?php } ?>

            </select>

        </div>


        <div class="form-group">

            <label>
                Jumlah
            </label>

            <input
                type="number"
                id="jumlah"
                min="1"
                value="1"
            >

        </div>


        <button
            type="button"
            class="btn btn-tambah"
            onclick="tambahProduk()"
        >
            + Tambah ke Keranjang
        </button>

    </div>

    <div class="card">

        <h3>
            Keranjang Penjualan
        </h3>

        <form
            method="POST"
            id="formPenjualan"
        >

            <input
                type="hidden"
                name="total_harga"
                id="inputTotal"
                value="0"
            >


            <table>

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>

                </thead>


                <tbody id="keranjang">

                    <tr>

                        <td
                            colspan="6"
                            class="kosong"
                        >
                            Keranjang masih kosong
                        </td>

                    </tr>

                </tbody>

            </table>


            <div class="total-box">

                <div>

                    <span>
                        Total Harga
                    </span>

                    <strong id="totalHarga">
                        Rp 0
                    </strong>

                </div>


                <div>

                    <label style="width:100px;">
                        Bayar
                    </label>

                    <input
                        type="number"
                        name="bayar"
                        id="bayar"
                        min="0"
                        placeholder="Uang pembayaran"
                        oninput="hitungKembalian()"
                        required
                    >

                </div>


                <div>

                    <span>
                        Kembalian
                    </span>

                    <strong id="kembalian">
                        Rp 0
                    </strong>

                </div>

            </div>


            <button
                type="submit"
                name="simpan"
                class="btn btn-simpan"
                onclick="return validasi()"
            >
                💾 SIMPAN PENJUALAN
            </button>

        </form>

        <a
            href="index.php"
            class="btn-dashboard"
        >
            ← Kembali ke Dashboard
        </a>

    </div>

</div>


<script>

let keranjang = [];


/* FORMAT RUPIAH */

function rupiah(angka) {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }
    ).format(angka);

}

function tambahProduk() {

    const select =
        document.getElementById('produk');

    const option =
        select.options[select.selectedIndex];

    if (!select.value) {

        alert(
            'Pilih produk terlebih dahulu.'
        );

        return;
    }

    const id =
        select.value;

    const nama =
        option.dataset.nama;

    const harga =
        parseInt(option.dataset.harga);

    const stok =
        parseInt(option.dataset.stok);

    const jumlah =
        parseInt(
            document.getElementById(
                'jumlah'
            ).value
        );

    if (!jumlah || jumlah <= 0) {

        alert(
            'Jumlah tidak valid.'
        );

        return;
    }

    const index =
        keranjang.findIndex(
            item => item.id == id
        );

    if (index >= 0) {

        const jumlahBaru =
            keranjang[index].jumlah +
            jumlah;

        if (jumlahBaru > stok) {

            alert(
                'Jumlah melebihi stok.'
            );

            return;
        }

        keranjang[index].jumlah =
            jumlahBaru;

    } else {

        if (jumlah > stok) {

            alert(
                'Jumlah melebihi stok.'
            );

            return;
        }

        keranjang.push({

            id: id,
            nama: nama,
            harga: harga,
            stok: stok,
            jumlah: jumlah

        });

    }

    tampilkanKeranjang();

}

function tampilkanKeranjang() {

    const tbody =
        document.getElementById(
            'keranjang'
        );

    tbody.innerHTML = '';

    if (keranjang.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="kosong">
                    Keranjang masih kosong
                </td>
            </tr>
        `;

        hitungTotal();

        return;
    }

    keranjang.forEach(
        (item, index) => {

            const subtotal =
                item.harga *
                item.jumlah;

            const tr =
                document.createElement(
                    'tr'
                );

            tr.innerHTML = `

                <td>
                    ${index + 1}
                </td>

                <td>

                    ${item.nama}

                    <input
                        type="hidden"
                        name="id_produk[]"
                        value="${item.id}"
                    >

                </td>

                <td>
                    ${rupiah(item.harga)}
                </td>

                <td>

                    <input
                        type="number"
                        name="jumlah_produk[]"
                        value="${item.jumlah}"
                        min="1"
                        max="${item.stok}"
                        onchange="
                            ubahJumlah(
                                ${index},
                                this.value
                            )
                        "
                        style="width:80px;"
                    >

                </td>

                <td>
                    ${rupiah(subtotal)}
                </td>

                <td>

                    <button
                        type="button"
                        class="btn btn-hapus"
                        onclick="
                            hapusProduk(${index})
                        "
                    >
                        Hapus
                    </button>

                </td>
            `;

            tbody.appendChild(tr);

        }
    );

    hitungTotal();

}

function ubahJumlah(index, jumlah) {

    jumlah =
        parseInt(jumlah);

    if (jumlah < 1) {
        jumlah = 1;
    }

    if (
        jumlah >
        keranjang[index].stok
    ) {

        alert(
            'Jumlah melebihi stok.'
        );

        jumlah =
            keranjang[index].stok;
    }

    keranjang[index].jumlah =
        jumlah;

    tampilkanKeranjang();

}

function hapusProduk(index) {

    keranjang.splice(
        index,
        1
    );

    tampilkanKeranjang();

}

function hitungTotal() {

    let total = 0;

    keranjang.forEach(item => {

        total +=
            item.harga *
            item.jumlah;

    });

    document.getElementById(
        'totalHarga'
    ).innerText =
        rupiah(total);

    document.getElementById(
        'inputTotal'
    ).value =
        total;

    hitungKembalian();

}

function hitungKembalian() {

    const total =
        parseInt(
            document.getElementById(
                'inputTotal'
            ).value
        ) || 0;

    const bayar =
        parseInt(
            document.getElementById(
                'bayar'
            ).value
        ) || 0;

    let kembalian =
        bayar - total;

    if (kembalian < 0) {
        kembalian = 0;
    }

    document.getElementById(
        'kembalian'
    ).innerText =
        rupiah(kembalian);

}

function validasi() {

    const nama =
        document.getElementById(
            'namaPelanggan'
        ).value.trim();

    if (nama === '') {

        alert(
            'Nama pelanggan wajib diisi.'
        );

        return false;
    }

    if (keranjang.length === 0) {

        alert(
            'Keranjang masih kosong.'
        );

        return false;
    }

    const total =
        parseInt(
            document.getElementById(
                'inputTotal'
            ).value
        ) || 0;

    const bayar =
        parseInt(
            document.getElementById(
                'bayar'
            ).value
        ) || 0;

    if (bayar < total) {

        alert(
            'Uang pembayaran kurang.'
        );

        return false;
    }

    return true;

}

</script>

</body>
</html>