<?php
/** @var array $signals */
/** @var array $categoryMap */
$success = flash('success');
?>
<section class="card admin-card">
    <div class="card-header">
        <div>
            <h2>سیگنال‌ها</h2>
            <p class="muted">ایجاد، ویرایش و بستن سریع سیگنال‌ها با کنترل کامل.</p>
        </div>
        <a class="primary-link" href="/admin/signals/new">ایجاد سیگنال جدید</a>
    </div>
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <div class="table admin-table">
        <div class="table-row header">
            <span>عنوان</span>
            <span>جفت ارز</span>
            <span>وضعیت</span>
            <span>دسته‌ها</span>
            <span>عملیات</span>
        </div>
        <?php foreach ($signals as $signal): ?>
            <div class="table-row">
                <span><?= htmlspecialchars($signal['title']) ?></span>
                <span class="mono"><?= htmlspecialchars($signal['pair']) ?></span>
                <span>
                    <span class="badge <?= $signal['status'] === 'open' ? 'badge-success' : 'badge-muted' ?>">
                        <?= $signal['status'] === 'open' ? 'فعال' : 'بسته' ?>
                    </span>
                </span>
                <span><?= htmlspecialchars(implode('، ', $categoryMap[$signal['id']] ?? [])) ?></span>
                <span class="row-actions">
                    <a class="link" href="/admin/signals/edit?id=<?= $signal['id'] ?>">ویرایش</a>
                    <?php if ($signal['status'] === 'open'): ?>
                        <form method="post" action="/admin/signals/close" class="inline-form">
                            <input type="hidden" name="id" value="<?= $signal['id'] ?>">
                            <select name="close_reason">
                                <option value="target">تارگت</option>
                                <option value="stop">استاپ</option>
                                <option value="manual" selected>بستن دستی</option>
                            </select>
                            <button class="link danger" type="submit">بستن</button>
                        </form>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
