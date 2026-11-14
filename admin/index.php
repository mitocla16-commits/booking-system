<?php
declare(strict_types=1);
require __DIR__ . '/../helpers.php';
require __DIR__ . '/../db.php';
require_admin();

$filter = $_GET['status'] ?? '';
$allowed = ['pending', 'confirmed', 'cancelled'];
$sql = 'SELECT * FROM bookings';
$params = [];
if (in_array($filter, $allowed, true)) {
    $sql .= ' WHERE status = :s';
    $params[':s'] = $filter;
} else {
    $filter = '';
}
$sql .= ' ORDER BY booking_date ASC, booking_time ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
foreach (db()->query('SELECT status, COUNT(*) c FROM bookings GROUP BY status') as $r) {
    $counts[$r['status']] = (int)$r['c'];
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bookings dashboard</title><link rel="stylesheet" href="../assets/style.css"></head>
<body><main class="card wide">
<div class="topbar"><h1>Bookings</h1><a href="logout.php">Log out</a></div>
<p>
    <a href="index.php">All (<?= array_sum($counts) ?>)</a> ·
    <?php foreach ($allowed as $s): ?><a href="?status=<?= $s ?>"><?= ucfirst($s) ?> (<?= $counts[$s] ?>)</a> · <?php endforeach; ?>
</p>
<?php if (!$rows): ?><p class="muted">No bookings found.</p><?php else: ?>
<table>
<tr><th>Date / time</th><th>Customer</th><th>Service</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($rows as $b): ?>
<tr>
    <td><?= e($b['booking_date']) ?><br><?= e(substr($b['booking_time'], 0, 5)) ?></td>
    <td><strong><?= e($b['name']) ?></strong><br><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a>
        <?php if ($b['message']): ?><br><span class="muted small"><?= e($b['message']) ?></span><?php endif; ?></td>
    <td><?= e($b['service']) ?></td>
    <td><span class="badge <?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
    <td>
        <?php foreach (['confirmed' => 'Confirm', 'cancelled' => 'Cancel'] as $act => $label): ?>
            <form method="post" action="update.php" class="inline"><?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <button class="small" name="action" value="<?= $act ?>"><?= $label ?></button></form>
        <?php endforeach; ?>
        <form method="post" action="update.php" class="inline" onsubmit="return confirm('Delete this booking?')"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <input type="hidden" name="filter" value="<?= e($filter) ?>">
            <button class="small danger" name="action" value="delete">Delete</button></form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</main></body></html>
