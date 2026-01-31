<?php
/** @var array $categories */
$success = flash('success');
$error = flash('error');
?>
<section class="grid two">
    <div class="card admin-card">
        <h2>ایجاد / ویرایش دسته‌بندی</h2>
        <p class="muted">نام و اسلاگ یکتا برای نمایش عمومی و لینک اختصاصی.</p>
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/categories/save" class="form-grid" data-category-form>
            <input type="hidden" name="id" value="">
            <label>
                نام دسته‌بندی
                <input type="text" name="name" required>
            </label>
            <label>
                اسلاگ (اختیاری)
                <input type="text" name="slug" placeholder="auto">
            </label>
            <button class="primary" type="submit">ذخیره</button>
        </form>
    </div>
    <div class="card admin-card">
        <h2>لیست دسته‌بندی‌ها</h2>
        <p class="muted">حذف امن بدون تداخل با سیگنال‌های قبلی.</p>
        <div class="table admin-table">
            <div class="table-row header">
                <span>نام</span>
                <span>اسلاگ</span>
                <span>وضعیت</span>
                <span>عملیات</span>
            </div>
            <?php foreach ($categories as $category): ?>
                <div class="table-row">
                    <span><?= htmlspecialchars($category['name']) ?></span>
                    <span class="mono"><?= htmlspecialchars($category['slug']) ?></span>
                    <span><?= $category['deleted_at'] ? 'حذف شده' : 'فعال' ?></span>
                    <span class="row-actions">
                        <?php if (!$category['deleted_at']): ?>
                            <button class="link" data-edit-category
                                    data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>"
                                    data-slug="<?= htmlspecialchars($category['slug'], ENT_QUOTES) ?>">ویرایش</button>
                            <form method="post" action="/admin/categories/delete">
                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                <button class="link danger" type="submit">حذف</button>
                            </form>
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
