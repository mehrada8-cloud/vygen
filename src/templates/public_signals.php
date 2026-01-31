<?php
/** @var array $category */
/** @var string $slug */
?>
<section class="signals-header">
    <div>
        <h2><?= htmlspecialchars($category['name']) ?></h2>
        <p class="subtle">سیگنال‌های این دسته‌بندی با بروزرسانی هوشمند ارائه می‌شوند.</p>
    </div>
    <div class="signal-meta">
        <span class="tag">لینک اختصاصی</span>
        <span class="mono">/signals/<?= htmlspecialchars($slug) ?></span>
    </div>
</section>

<section id="signals" class="signal-list" data-endpoint="/api/signals?category=<?= htmlspecialchars($slug) ?>">
    <div class="signal-card loading">
        <h3>در حال دریافت سیگنال‌ها...</h3>
        <p>بروزرسانی بر اساس ETag فعال است.</p>
    </div>
</section>
