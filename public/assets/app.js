const themeToggle = document.querySelector('[data-theme-toggle]');
const root = document.documentElement;

const storedTheme = localStorage.getItem('vygen-theme');
if (storedTheme) {
  root.setAttribute('data-theme', storedTheme);
}

themeToggle?.addEventListener('click', () => {
  const current = root.getAttribute('data-theme') || 'light';
  const next = current === 'light' ? 'dark' : 'light';
  root.setAttribute('data-theme', next);
  localStorage.setItem('vygen-theme', next);
});

async function pollWithEtag(url, onData) {
  let etag = null;
  async function fetchData() {
    const headers = {};
    if (etag) {
      headers['If-None-Match'] = etag;
    }
    const response = await fetch(url, { headers });
    if (response.status === 304) {
      return;
    }
    etag = response.headers.get('ETag');
    const json = await response.json();
    onData(json.data || []);
  }

  await fetchData();
  setInterval(fetchData, 5000);
}

const categoryContainer = document.getElementById('categories');
if (categoryContainer) {
  const endpoint = categoryContainer.dataset.endpoint;
  pollWithEtag(endpoint, (data) => {
    if (!data.length) {
      categoryContainer.innerHTML = '<div class="empty-state"><h3>دسته‌بندی ثبت نشده</h3><p>ادمین هنوز دسته‌بندی اضافه نکرده است.</p></div>';
      return;
    }
    categoryContainer.innerHTML = data.map((category) => `
      <a class="card" href="/signals/${category.slug}">
        <div>
          <span class="tag">ویژه</span>
          <h3>${category.name}</h3>
          <p class="subtle">مشاهده سیگنال‌های مرتبط با این دسته‌بندی</p>
        </div>
        <span class="arrow">→</span>
      </a>
    `).join('');
  });
}

const signalContainer = document.getElementById('signals');
if (signalContainer) {
  const endpoint = signalContainer.dataset.endpoint;
  pollWithEtag(endpoint, (data) => {
    if (!data.length) {
      signalContainer.innerHTML = '<div class="empty-state"><h3>سیگنالی موجود نیست</h3><p>سیگنال‌های این دسته‌بندی به‌زودی ثبت می‌شوند.</p></div>';
      return;
    }

    signalContainer.innerHTML = data.map((signal) => {
      const statusClass = signal.status === 'open' ? 'status-open' : 'status-closed';
      const progress = signal.progress_percent !== null ? Math.round(signal.progress_percent) : null;
      const progressText = progress !== null ? `${progress}%` : '—';
      const closeLabels = {
        target: 'بسته با تارگت',
        stop: 'بسته با استاپ',
        manual: 'بسته دستی',
      };
      const closeText = signal.status === 'closed'
        ? (closeLabels[signal.close_reason] || 'بسته شده')
        : 'فعال';

      return `
        <article class="signal-card ${statusClass}">
          <header>
            <div>
              <h3>${signal.title}</h3>
              <p class="subtle mono">${signal.pair}</p>
            </div>
            <span class="badge">${signal.position_type.toUpperCase()} • ${signal.market_type.toUpperCase()}</span>
          </header>
          <div class="signal-grid">
            <div>
              <span class="label">ورود</span>
              <strong>${signal.entry_price}</strong>
            </div>
            <div>
              <span class="label">تارگت</span>
              <strong>${signal.target_price}</strong>
            </div>
            <div>
              <span class="label">استاپ</span>
              <strong>${signal.stop_price}</strong>
            </div>
            <div>
              <span class="label">پیشرفت</span>
              <strong>${progressText}</strong>
            </div>
          </div>
          <div class="signal-footer">
            <div>
              <span class="label">شروع</span>
              <span class="mono">${signal.start_at_display}</span>
            </div>
            <div>
              <span class="label">پایان</span>
              <span class="mono">${signal.end_at_display ?? 'در حال اجرا'}</span>
            </div>
            <div>
              <span class="label">وضعیت</span>
              <span class="status">${closeText}</span>
            </div>
          </div>
        </article>
      `;
    }).join('');
  });
}
