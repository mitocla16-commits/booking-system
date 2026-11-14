<?php
declare(strict_types=1);
require __DIR__ . '/../helpers.php';
require __DIR__ . '/../db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    http_response_code(400);
    exit('Bad request');
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
if (!$id) {
    http_response_code(400);
    exit('Bad request');
}
if (in_array($action, ['confirmed', 'cancelled', 'pending'], true)) {
    db()->prepare('UPDATE bookings SET status = :s WHERE id = :id')->execute([':s' => $action, ':id' => $id]);
} elseif ($action === 'delete') {
    db()->prepare('DELETE FROM bookings WHERE id = :id')->execute([':id' => $id]);
}
header('Location: index.php?' . http_build_query(array_filter(['status' => $_POST['filter'] ?? ''])));
