<?php
include 'db.php';

$id = $_GET['id'];
$conn->query("DELETE FROM bookings WHERE id = $id");

header("Location: index.php");
exit;