<?php
include 'db.php';

$conn->query("
    UPDATE bookings 
    SET status = 'dibatalkan' 
    WHERE status = 'pending' 
    AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30
");

$users = $conn->query("SELECT * FROM users");
$lapangan = $conn->query("SELECT * FROM lapangan WHERE status = 'aktif'");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $lapangan_id = $_POST['lapangan_id'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $durasi = $_POST['durasi'];
    $status = $_POST['status'];
    $nominal_bayar = $_POST['nominal_bayar'];

    $start = new DateTime($jam_mulai);
    $end = clone $start;
    $end->modify("+$durasi hour");
    $jam_selesai = $end->format("H:i:s");

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
        AND status != 'dibatalkan'
        AND (
            jam_mulai < '$jam_selesai' AND 
            ADDTIME(jam_mulai, SEC_TO_TIME(durasi * 3600)) > '$jam_mulai'
        )
    ");

    if ($cek->num_rows > 0) {
        $error = "Gagal! Waktu sudah terisi untuk lapangan ini pada tanggal dan jam tersebut.";
    } else {
        $conn->query("INSERT INTO bookings 
            (user_id, lapangan_id, tanggal, jam_mulai, durasi, status, created_at, total_bayar, nominal_bayar) 
            VALUES 
            ('$user_id', '$lapangan_id', '$tanggal', '$jam_mulai', '$durasi', '$status', NOW(), '$total_bayar', '$nominal_bayar')");
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Reservasi</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9fb;
            padding: 40px;
            color: #333;
        }

        h2 {
            text-align: center;
            font-size: 26px;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        form {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }

        label {
            font-weight: 600;
            margin-top: 15px;
            display: block;
        }

        select, input[type="text"], input[type="number"], input[type="date"], input[type="time"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .total {
            font-size: 16px;
            margin-top: 8px;
            font-weight: bold;
            color: #2d3436;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error {
            background-color: #ffe6e6;
            color: #e74c3c;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: bold;
            text-align: center;
        }

        a.back-link {
            display: inline-block;
            margin: 20px auto 0;
            text-align: center;
            width: 100%;
            color: #555;
            text-decoration: none;
        }

        a.back-link:hover {
            text-decoration: underline;
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

    <h2>Tambah Reservasi</h2>

    <form method="post">
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="user_id">ID Pengguna</label>
            <select name="user_id" required>
                <option value="">-- Pilih Pengguna --</option>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>">
                        <?= $user['id'] ?> - <?= $user['nama'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="lapangan_id">Lapangan</label>
            <select name="lapangan_id" onchange="updateHarga()" required>
                <option value="">-- Pilih Lapangan --</option>
                <?php while ($row = $lapangan->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= $row['nama'] ?> - <?= $row['jenis'] ?> (<?= $row['lokasi'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" required>
        </div>

        <div class="form-group">
            <label>Jam Mulai</label>
            <input type="time" name="jam_mulai" required>
        </div>

        <div class="form-group">
            <label>Durasi (Jam)</label>
            <input type="number" name="durasi" min="1" onchange="updateHarga()" required>
        </div>

        <div class="form-group">
            <label>Total Bayar</label>
            <div class="total" id="total_bayar">Rp 0</div>
        </div>

        <div class="form-group">
            <label>Nominal Dibayar</label>
            <input type="number" name="nominal_bayar" min="0" required>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="pending">Pending</option>
                <option value="lunas">Lunas</option>
                <option value="dibatalkan">Dibatalkan</option>
            </select>
        </div>

        <button type="submit">Simpan Reservasi</button>
    </form>

    <a class="back-link" href="index.php">← Kembali ke Daftar Reservasi</a>

</body>
</html>
