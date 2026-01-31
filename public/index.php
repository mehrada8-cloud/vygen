<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$config = $config ?? require __DIR__ . '/../config/app.php';
$pdo = db($config);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

function render_public(string $title, string $template, array $vars = []): void
{
    global $config;
    extract($vars);
    ob_start();
    require __DIR__ . '/../src/templates/' . $template;
    $content = ob_get_clean();
    require __DIR__ . '/../src/templates/public_layout.php';
    exit;
}

function render_admin(string $title, string $template, array $vars = []): void
{
    global $config;
    extract($vars);
    ob_start();
    require __DIR__ . '/../src/templates/' . $template;
    $content = ob_get_clean();
    require __DIR__ . '/../src/templates/admin_layout.php';
    exit;
}

if ($path === '/' || $path === '') {
    header('Location: /categories');
    exit;
}

if ($path === '/api/categories') {
    $stmt = $pdo->query("SELECT id, name, slug, updated_at FROM categories WHERE deleted_at IS NULL ORDER BY created_at DESC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $etagSource = json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $etag = 'W/"' . sha1($etagSource) . '"';

    if (client_etag_matches($etag)) {
        http_response_code(304);
        exit;
    }

    json_response(['data' => $categories], $etag);
}

if ($path === '/api/signals') {
    $slug = $_GET['category'] ?? '';
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = :slug AND deleted_at IS NULL");
    $stmt->execute(['slug' => $slug]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        json_response(['data' => []]);
    }

    $stmt = $pdo->prepare(
        "SELECT s.* FROM signals s
         INNER JOIN signal_categories sc ON sc.signal_id = s.id
         WHERE sc.category_id = :category_id
         ORDER BY s.start_at DESC"
    );
    $stmt->execute(['category_id' => $category['id']]);
    $signals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($signals as &$signal) {
        $signal['start_at_display'] = format_datetime($signal['start_at'], $config);
        $signal['end_at_display'] = $signal['end_at'] ? format_datetime($signal['end_at'], $config) : null;
    }

    $etagSource = json_encode($signals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $etag = 'W/"' . sha1($etagSource) . '"';

    if (client_etag_matches($etag)) {
        http_response_code(304);
        exit;
    }

    json_response(['data' => $signals], $etag);
}

if ($path === '/categories') {
    render_public('دسته‌بندی سیگنال‌ها', 'public_categories.php');
}

if (preg_match('#^/signals/([\w\-\p{Arabic}]+)$#u', $path, $matches)) {
    $slug = $matches[1];
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE slug = :slug AND deleted_at IS NULL");
    $stmt->execute(['slug' => $slug]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        http_response_code(404);
        render_public('دسته‌بندی یافت نشد', 'public_not_found.php');
    }

    render_public('سیگنال‌های ' . $category['name'], 'public_signals.php', ['category' => $category, 'slug' => $slug]);
}

if ($path === '/admin/login') {
    session_start();
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === $config['admin']['username'] && $password === $config['admin']['password']) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: /admin/categories');
            exit;
        }
        $error = 'نام کاربری یا رمز عبور اشتباه است.';
    }

    render_admin('ورود به پنل', 'admin_login.php', ['error' => $error]);
}

if ($path === '/admin/logout') {
    session_start();
    session_destroy();
    header('Location: /admin/login');
    exit;
}

if ($path === '/admin/categories') {
    require_admin();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    render_admin('مدیریت دسته‌بندی‌ها', 'admin_categories.php', ['categories' => $categories]);
}

if ($path === '/admin/categories/save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $id = $_POST['id'] ?? null;

    if ($name === '') {
        flash('error', 'نام دسته‌بندی الزامی است.');
        header('Location: /admin/categories');
        exit;
    }

    if ($slug === '') {
        $slug = slugify($name);
    }

    $now = date('c');

    if ($id) {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name, slug = :slug, updated_at = :updated_at WHERE id = :id");
        $stmt->execute(['name' => $name, 'slug' => $slug, 'updated_at' => $now, 'id' => $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, created_at, updated_at) VALUES (:name, :slug, :created_at, :updated_at)");
        $stmt->execute(['name' => $name, 'slug' => $slug, 'created_at' => $now, 'updated_at' => $now]);
    }

    flash('success', 'دسته‌بندی ذخیره شد.');
    header('Location: /admin/categories');
    exit;
}

if ($path === '/admin/categories/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("UPDATE categories SET deleted_at = :deleted_at WHERE id = :id");
        $stmt->execute(['deleted_at' => date('c'), 'id' => $id]);
        flash('success', 'دسته‌بندی حذف شد.');
    }
    header('Location: /admin/categories');
    exit;
}

if ($path === '/admin/signals') {
    require_admin();
    $stmt = $pdo->query("SELECT * FROM signals ORDER BY start_at DESC");
    $signals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoryMap = [];
    $stmt = $pdo->query("SELECT sc.signal_id, c.name FROM signal_categories sc JOIN categories c ON c.id = sc.category_id");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $categoryMap[$row['signal_id']][] = $row['name'];
    }

    render_admin('مدیریت سیگنال‌ها', 'admin_signals.php', [
        'signals' => $signals,
        'categoryMap' => $categoryMap,
    ]);
}

if ($path === '/admin/signals/new' || $path === '/admin/signals/edit') {
    require_admin();
    $id = $_GET['id'] ?? null;
    $signal = null;

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM signals WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $signal = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $categories = $pdo->query("SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

    $selectedCategories = [];
    if ($signal) {
        $stmt = $pdo->prepare("SELECT category_id FROM signal_categories WHERE signal_id = :signal_id");
        $stmt->execute(['signal_id' => $signal['id']]);
        $selectedCategories = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
    }

    render_admin('فرم سیگنال', 'admin_signal_form.php', [
        'signal' => $signal,
        'categories' => $categories,
        'selectedCategories' => $selectedCategories,
    ]);
}

if ($path === '/admin/signals/save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $id = $_POST['id'] ?? null;
    $payload = [
        'title' => trim($_POST['title'] ?? ''),
        'pair' => trim($_POST['pair'] ?? ''),
        'position_type' => $_POST['position_type'] ?? 'long',
        'market_type' => $_POST['market_type'] ?? 'spot',
        'entry_price' => (float)($_POST['entry_price'] ?? 0),
        'target_price' => (float)($_POST['target_price'] ?? 0),
        'stop_price' => (float)($_POST['stop_price'] ?? 0),
        'start_at' => normalize_datetime($_POST['start_at'] ?? null, $config),
        'end_at' => ($_POST['end_at'] ?? '') !== '' ? normalize_datetime($_POST['end_at'], $config) : null,
        'monitoring_enabled' => isset($_POST['monitoring_enabled']) ? 1 : 0,
    ];

    $categories = $_POST['categories'] ?? [];
    $now = date('c');

    if ($payload['title'] === '' || $payload['pair'] === '' || empty($categories)) {
        flash('error', 'عنوان، جفت ارز و حداقل یک دسته‌بندی الزامی است.');
        $redirect = $id ? '/admin/signals/edit?id=' . $id : '/admin/signals/new';
        header('Location: ' . $redirect);
        exit;
    }

    if ($id) {
        $stmt = $pdo->prepare(
            "UPDATE signals SET title = :title, pair = :pair, position_type = :position_type, market_type = :market_type,
                entry_price = :entry_price, target_price = :target_price, stop_price = :stop_price, start_at = :start_at,
                end_at = :end_at, monitoring_enabled = :monitoring_enabled, updated_at = :updated_at WHERE id = :id"
        );
        $stmt->execute(array_merge($payload, ['updated_at' => $now, 'id' => $id]));

        $pdo->prepare("DELETE FROM signal_categories WHERE signal_id = :signal_id")
            ->execute(['signal_id' => $id]);

        $signalId = (int)$id;
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO signals (title, pair, position_type, market_type, entry_price, target_price, stop_price, start_at,
                end_at, monitoring_enabled, created_at, updated_at) VALUES (:title, :pair, :position_type, :market_type,
                :entry_price, :target_price, :stop_price, :start_at, :end_at, :monitoring_enabled, :created_at, :updated_at)"
        );
        $stmt->execute(array_merge($payload, ['created_at' => $now, 'updated_at' => $now]));
        $signalId = (int)$pdo->lastInsertId();
    }

    foreach ($categories as $categoryId) {
        $stmt = $pdo->prepare("INSERT INTO signal_categories (signal_id, category_id) VALUES (:signal_id, :category_id)");
        $stmt->execute(['signal_id' => $signalId, 'category_id' => (int)$categoryId]);
    }

    flash('success', 'سیگنال ذخیره شد.');
    header('Location: /admin/signals');
    exit;
}

if ($path === '/admin/signals/close' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $id = $_POST['id'] ?? null;
    $reason = $_POST['close_reason'] ?? 'manual';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE signals SET status = 'closed', close_reason = :reason, end_at = :end_at, updated_at = :updated_at WHERE id = :id");
        $stmt->execute([
            'reason' => $reason,
            'end_at' => date('c'),
            'updated_at' => date('c'),
            'id' => $id,
        ]);
    }

    flash('success', 'سیگنال بسته شد.');
    header('Location: /admin/signals');
    exit;
}

http_response_code(404);
render_public('صفحه یافت نشد', 'public_not_found.php');
