<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$config = $config ?? require __DIR__ . '/../config/app.php';
$pdo = db($config);

$pricesPath = $config['storage']['prices_path'];
$prices = [];
if (file_exists($pricesPath)) {
    $prices = json_decode(file_get_contents($pricesPath), true) ?: [];
}

$stmt = $pdo->query("SELECT * FROM signals WHERE status = 'open' AND monitoring_enabled = 1");
$signals = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($signals as $signal) {
    $symbol = strtoupper($signal['pair']);
    if (!isset($prices[$symbol])) {
        continue;
    }

    $current = (float)$prices[$symbol];
    $entry = (float)$signal['entry_price'];
    $target = (float)$signal['target_price'];
    $stop = (float)$signal['stop_price'];

    $progress = null;
    if ($signal['position_type'] === 'long') {
        $progress = ($current - $entry) / max(($target - $entry), 0.000001) * 100;
        if ($current >= $target) {
            close_signal($pdo, $signal['id'], 'target');
            continue;
        }
        if ($current <= $stop) {
            close_signal($pdo, $signal['id'], 'stop');
            continue;
        }
    } else {
        $progress = ($entry - $current) / max(($entry - $target), 0.000001) * 100;
        if ($current <= $target) {
            close_signal($pdo, $signal['id'], 'target');
            continue;
        }
        if ($current >= $stop) {
            close_signal($pdo, $signal['id'], 'stop');
            continue;
        }
    }

    $progress = max(0, min(100, $progress));

    $stmt = $pdo->prepare("UPDATE signals SET progress_percent = :progress, last_price = :price, updated_at = :updated_at WHERE id = :id");
    $stmt->execute([
        'progress' => $progress,
        'price' => $current,
        'updated_at' => date('c'),
        'id' => $signal['id'],
    ]);
}

function close_signal(PDO $pdo, int $signalId, string $reason): void
{
    $stmt = $pdo->prepare("UPDATE signals SET status = 'closed', close_reason = :reason, end_at = :end_at, updated_at = :updated_at WHERE id = :id");
    $stmt->execute([
        'reason' => $reason,
        'end_at' => date('c'),
        'updated_at' => date('c'),
        'id' => $signalId,
    ]);
}
