<?php
/** @var string $title */
/** @var string $content */
/** @var array $config */
?>
<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <div class="app-shell">
        <header class="site-header">
            <div>
                <p class="app-label"><?= htmlspecialchars($config['app_name']) ?></p>
                <h1><?= htmlspecialchars($title) ?></h1>
                <p class="subtle">پلتفرم سیگنال حرفه‌ای با بروزرسانی هوشمند و طراحی لوکس.</p>
            </div>
            <div class="header-actions">
                <button class="theme-toggle" data-theme-toggle>تغییر تم</button>
                <a class="ghost-link" href="/categories">دسته‌بندی‌ها</a>
            </div>
        </header>
        <main>
            <?= $content ?>
        </main>
        <footer class="site-footer">
            <span>© <?= date('Y') ?> Vygen</span>
            <span class="footer-note">اطلاعات سیگنال‌ها صرفاً جهت نمایش بوده و توسط ادمین مدیریت می‌شود.</span>
        </footer>
    </div>
    <script src="/assets/app.js" defer></script>
</body>
</html>
