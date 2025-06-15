<?php 
include 'db.php'; 

// Update otomatis status pending jika lebih dari 30 menit
$conn->query("
    UPDATE bookings
    SET status = 'dibatalkan'
    WHERE status = 'pending'
    AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Reservasi Lapangan Futsal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        a {
            text-decoration: none;
            color: #3498db;
            margin-right: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
        }
        th {
            background-color: #2980b9;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions a {
            color: #2980b9;
            font-weight: bold;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .pending { background-color: #f1c40f; color: #fff; }
        .lunas { background-color: #27ae60; color: #fff; }
        .dibatalkan { background-color: #e74c3c; color: #fff; }
    </style>
</head>
<body>

    <h2>Sistem Reservasi Lapangan Futsal</h2>
    <a href="create.php">➕ Tambah Reservasi Baru</a>
    <a href="jadwal.php">📅 Lihat Jadwal</a>

    <br><br>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama Pengguna</th>
            <th>Lapangan</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Durasi</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th>Kadaluarsa</th>
            <th>Total</th>
            <th>Bayar</th>
            <th>Sisa</th>
            <th>Aksi</th>
        </tr>

        <?php
        $result = $conn->query("
            SELECT 
                bookings.*, 
                users.nama AS nama_pengguna, 
                lapangan.nama AS nama_lapangan 
            FROM bookings
            LEFT JOIN users ON bookings.user_id = users.id
            LEFT JOIN lapangan ON bookings.lapangan_id = lapangan.id
            ORDER BY bookings.created_at DESC
        ");

        while ($row = $result->fetch_assoc()):
        ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
                <td><?= htmlspecialchars($row['nama_lapangan']); ?></td>
                <td><?= $row['tanggal']; ?></td>
                <td><?= substr($row['jam_mulai'], 0, 5); ?></td>
                <td><?= $row['durasi']; ?> jam</td>
                <td>
                    <span class="badge <?= $row['status']; ?>">
                        <?= ucfirst($row['status']); ?>
                    </span>
                </td>
                <td><?= $row['created_at']; ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <?php
                            $kadaluarsa = new DateTime($row['created_at']);
                            $kadaluarsa->modify('+30 minutes');
                            echo $kadaluarsa->format('Y-m-d H:i:s');
                        ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                <td>Rp <?= number_format($row['nominal_bayar'], 0, ',', '.'); ?></td>
                <td>
                    Rp <?= number_format($row['total_bayar'] - $row['nominal_bayar'], 0, ',', '.'); ?>
                </td>
                <td class="actions">
                    <a href="edit.php?id=<?= $row['id']; ?>">Edit</a> |
                    <a href="delete.php?id=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin hapus reservasi ini?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
