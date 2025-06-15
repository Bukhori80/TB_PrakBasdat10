<?php
include 'db.php';

$tanggal_awal = date('Y-m-d');
$tanggal_akhir = date('Y-m-d', strtotime('+29 days')); // 30 hari

$lapangan = $conn->query("SELECT * FROM lapangan WHERE status = 'aktif'");
$daftar_lapangan = [];
while ($row = $lapangan->fetch_assoc()) {
    $daftar_lapangan[$row['id']] = $row['nama'];
}

$bookings = $conn->query("
    SELECT * FROM bookings 
    WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");

$data_booking = [];
while ($b = $bookings->fetch_assoc()) {
    $data_booking[$b['tanggal']][$b['lapangan_id']][] = [
        'jam_mulai' => $b['jam_mulai'],
        'durasi' => $b['durasi'],
        'status' => $b['status']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Jadwal 30 Hari Lapangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8;
            margin: 0; padding: 20px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
            font-weight: 600;
            color: #222;
        }
        a.back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #3498db;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        a.back-link:hover {
            color: #2169b5;
        }

        /* Table styling */
        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgb(0 0 0 / 0.1);
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            min-width: 700px;
        }
        thead tr th {
            background: #3498db;
            color: white;
            padding: 12px 15px;
            font-weight: 600;
            border-radius: 8px 8px 0 0;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        tbody tr {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgb(0 0 0 / 0.05);
            transition: transform 0.15s ease;
        }
        tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgb(0 0 0 / 0.12);
        }
        tbody tr td {
            padding: 12px 15px;
            vertical-align: top;
            text-align: center;
            border-bottom: none;
            border-left: 1px solid #e0e6ed;
            font-size: 14px;
        }
        tbody tr td:first-child {
            font-weight: 600;
            color: #555;
            background: #f9fafb;
            border-left: none;
            border-radius: 8px 0 0 8px;
            white-space: nowrap;
        }
        tbody tr td:last-child {
            border-radius: 0 8px 8px 0;
        }

        /* Status styles */
        .terisi {
            background-color: #fce4e4;
            color: #b42318;
            border-radius: 6px;
            padding: 6px 8px;
            font-weight: 600;
            line-height: 1.3;
        }
        .kosong {
            background-color: #e0f7e9;
            color: #217a3b;
            font-weight: 600;
            border-radius: 6px;
            padding: 6px 8px;
        }

        /* Booking time badges */
        .booking-time {
            background: #ff6b6b;
            color: white;
            font-size: 12px;
            border-radius: 12px;
            padding: 4px 8px;
            margin: 2px 0;
            display: inline-block;
            min-width: 75px;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.2);
        }

        /* Responsive */
        @media (max-width: 600px) {
            thead tr th, tbody tr td {
                padding: 8px 6px;
                font-size: 12px;
            }
            .booking-time {
                min-width: 60px;
                padding: 3px 6px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <h2>Jadwal 30 Hari Lapangan (<?= $tanggal_awal; ?> s.d <?= $tanggal_akhir; ?>)</h2>
    <a href="index.php" class="back-link">← Kembali ke Dashboard</a>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <?php foreach ($daftar_lapangan as $nama): ?>
                        <th><?= htmlspecialchars($nama) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 30; $i++):
                    $tgl = date('Y-m-d', strtotime("+$i days"));
                    $tglFormatted = date('D, d M Y', strtotime($tgl));
                ?>
                <tr>
                    <td title="<?= $tgl ?>"><?= $tglFormatted ?></td>
                    <?php foreach ($daftar_lapangan as $lap_id => $nama): ?>
                        <?php if (isset($data_booking[$tgl][$lap_id])): ?>
                            <td class="terisi" title="Sudah dipesan">
                                <?php foreach ($data_booking[$tgl][$lap_id] as $b): ?>
                                    <span class="booking-time"><?= substr($b['jam_mulai'], 0, 5) ?> (<?= $b['durasi'] ?> jam)</span><br>
                                <?php endforeach; ?>
                            </td>
                        <?php else: ?>
                            <td class="kosong" title="Lapangan tersedia">Tersedia</td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
