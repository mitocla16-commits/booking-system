<?php
declare(strict_types=1);
require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';

$cfg = require __DIR__ . '/config.php';
$errors = [];
$success = false;
$old = ['name' => '', 'email' => '', 'service' => '', 'date' => '', 'time' => '', 'message' => ''];

// Spam protection: honeypot field + minimum time on page.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['form_started'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'service' => (string)($_POST['service'] ?? ''),
        'date' => (string)($_POST['date'] ?? ''),
        'time' => (string)($_POST['time'] ?? ''),
        'message' => trim((string)($_POST['message'] ?? '')),
    ];

    if (!csrf_check()) {
        $errors[] = 'Session expired. Please reload the page and try again.';
    } elseif (!empty($_POST['website']) || (time() - ($_SESSION['form_started'] ?? 0)) < 2) {
        $errors[] = 'Submission rejected.'; // honeypot filled or submitted too fast
    } else {
        if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
            $errors[] = 'Please enter your name (max 100 characters).';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!in_array($old['service'], $cfg['services'], true)) {
            $errors[] = 'Please choose a service.';
        }
        $d = DateTime::createFromFormat('Y-m-d', $old['date']);
        if (!$d || $d->format('Y-m-d') !== $old['date'] || $old['date'] < date('Y-m-d')) {
            $errors[] = 'Please choose a valid date that is not in the past.';
        }
        if (!preg_match('/^(09|1[0-6]):(00|30)$/', $old['time'])) {
            $errors[] = 'Please choose a time between 09:00 and 16:30.';
        }
        if (mb_strlen($old['message']) > 1000) {
            $errors[] = 'Message is too long (max 1000 characters).';
        }
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO bookings (name, email, service, booking_date, booking_time, message)
                 VALUES (:name, :email, :service, :d, :t, :m)'
            );
            $stmt->execute([
                ':name' => $old['name'], ':email' => $old['email'], ':service' => $old['service'],
                ':d' => $old['date'], ':t' => $old['time'], ':m' => $old['message'],
            ]);
            $success = true;
            $old = array_map(fn() => '', $old);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        } catch (PDOException $ex) {
            // Unique slot constraint -> friendly message; anything else is logged, not shown.
            if (in_array($ex->getCode(), ['23000', 23000, '23505'], true)) {
                $errors[] = 'Sorry, that time slot is already booked. Please pick another.';
            } else {
                error_log($ex->getMessage());
                $errors[] = 'Something went wrong. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book an appointment</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="card">
    <h1>Book an appointment</h1>
    <p class="muted">Choose a service and a time. We'll confirm by email.</p>

    <?php if ($success): ?>
        <div class="alert ok">Thank you! Your booking request has been received.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert err"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>

        <label>Name <input name="name" value="<?= e($old['name']) ?>" required maxlength="100"></label>
        <label>Email <input type="email" name="email" value="<?= e($old['email']) ?>" required maxlength="150"></label>
        <label>Service
            <select name="service" required>
                <option value="">Choose...</option>
                <?php foreach ($cfg['services'] as $s): ?>
                    <option <?= $old['service'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="row">
            <label>Date <input type="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= e($old['date']) ?>" required></label>
            <label>Time
                <select name="time" required>
                    <option value="">Choose...</option>
                    <?php for ($h = 9; $h <= 16; $h++): foreach (['00', '30'] as $m): $t = sprintf('%02d:%s', $h, $m); ?>
                        <option <?= $old['time'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; endfor; ?>
                </select>
            </label>
        </div>
        <label>Message (optional) <textarea name="message" rows="3" maxlength="1000"><?= e($old['message']) ?></textarea></label>
        <button type="submit">Request booking</button>
    </form>
    <p class="muted small"><a href="admin/">Admin area</a></p>
</main>
</body>
</html>
