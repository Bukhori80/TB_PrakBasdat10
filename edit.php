<?php
include 'db.php';

$id = intval($_GET['id']);
$booking = $conn->query("SELECT * FROM bookings WHERE id = $id")->fetch_assoc();

$users = $conn->query("SELECT * FROM users ORDER BY nama");
$lapanganList = $conn->query("SELECT * FROM lapangan WHERE status = 'aktif'");

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $lapangan_id = $_POST['lapangan_id'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $durasi = $_POST['durasi'];
    $status = $_POST['status'];
    $nominal_bayar = $_POST['nominal_bayar'];

    if (empty($user_id) || empty($lapangan_id) || empty($tanggal) || empty($jam_mulai) || empty($durasi) || empty($status)) {
        $error = "Semua data harus diisi. Silakan lengkapi formulir.";
    } else {
        $start = new DateTime($jam_mulai);
        $end = clone $start;
        $end->modify("+$durasi hour");
        $jam_selesai = $end->format("H:i:s");

        // Ambil harga
        $harga_per_jam = 0;
        $resultHarga = $conn->query("SELECT harga_per_jam FROM lapangan WHERE id = '$lapangan_id'");
        if ($rowHarga = $resultHarga->fetch_assoc()) {
            $harga_per_jam = $rowHarga['harga_per_jam'];
        }
        $total_bayar = $harga_per_jam * $durasi;

        $cek = $conn->query("
            SELECT * FROM bookings 
            WHERE lapangan_id = '$lapangan_id'
            AND tanggal = '$tanggal'
            AND id != '$id'
            AND status != 'dibatalkan'
            AND (
                jam_mulai < '$jam_selesai' AND 
                ADDTIME(jam_mulai, SEC_TO_TIME(durasi * 3600)) > '$jam_mulai'
            )
        ");

        if ($cek->num_rows > 0) {
            $error = "Gagal mengupdate! Jadwal untuk lapangan ini sudah terisi pada tanggal dan jam tersebut.";
        } else {
            $conn->query("UPDATE bookings SET 
                user_id='$user_id',
                lapangan_id='$lapangan_id',
                tanggal='$tanggal',
                jam_mulai='$jam_mulai',
                durasi='$durasi',
                status='$status',
                total_bayar='$total_bayar',
                nominal_bayar='$nominal_bayar'
                WHERE id=$id
            ");
            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Reservasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
        }

        form {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .error {
            background: #ffe6e6;
            color: #e74c3c;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .total {
            font-weight: bold;
            margin-top: 10px;
            color: #2c3e50;
        }

        button {
            margin-top: 20px;
            background: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }
    </style>
    <script>
    function updateHarga() {
        const lapangan = document.querySelector("select[name='lapangan_id']");
        const durasi = document.querySelector("input[name='durasi']").value;
        const hargaMap = {
            1: 150000,
            2: 125000
        };
        let total = 0;
        if (lapangan.value && durasi) {
            total = hargaMap[lapangan.value] * durasi;
        }
        document.getElementById("total_bayar").innerText = "Rp " + total.toLocaleString('id-ID');
    }
    </script>
</head>
<body>

<h2>Edit Reservasi</h2>

<form method="post">
    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <label>Nama Pengguna</label>
    <select name="user_id" required>
        <option value="">-- Pilih Pengguna --</option>
        <?php
        mysqli_data_seek($users, 0);
        while ($u = $users->fetch_assoc()):
        ?>
            <option value="<?= $u['id']; ?>" <?= $booking['user_id'] == $u['id'] ? 'selected' : '' ?>>
                <?= $u['nama']; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Lapangan</label>
    <select name="lapangan_id" onchange="updateHarga()" required>
        <option value="">-- Pilih Lapangan --</option>
        <?php
        mysqli_data_seek($lapanganList, 0);
        while ($l = $lapanganList->fetch_assoc()):
        ?>
            <option value="<?= $l['id']; ?>" <?= $booking['lapangan_id'] == $l['id'] ? 'selected' : '' ?>>
                <?= $l['nama']; ?> - <?= $l['jenis']; ?> (<?= $l['lokasi']; ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= $booking['tanggal']; ?>" required>

    <label>Jam Mulai</label>
    <input type="time" name="jam_mulai" value="<?= substr($booking['jam_mulai'], 0, 5); ?>" required>

    <label>Durasi (Jam)</label>
    <input type="number" name="durasi" min="1" value="<?= $booking['durasi']; ?>" onchange="updateHarga()" required>

    <div class="total">Total Bayar: <span id="total_bayar">
        Rp <?= number_format($booking['total_bayar'], 0, ',', '.'); ?>
    </span></div>

    <label>Nominal Bayar</label>
    <input type="number" name="nominal_bayar" min="0" value="<?= $booking['nominal_bayar']; ?>" required>

    <label>Status</label>
    <select name="status" required>
        <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="lunas" <?= $booking['status'] == 'lunas' ? 'selected' : '' ?>>Lunas</option>
        <option value="dibatalkan" <?= $booking['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
    </select>

    <button type="submit">Simpan Perubahan</button>
</form>

<script>
    window.onload = updateHarga;
</script>

</body>
</html>
