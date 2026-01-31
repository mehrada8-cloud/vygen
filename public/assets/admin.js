const categoryForm = document.querySelector('[data-category-form]');
if (categoryForm) {
  const idInput = categoryForm.querySelector('input[name="id"]');
  const nameInput = categoryForm.querySelector('input[name="name"]');
  const slugInput = categoryForm.querySelector('input[name="slug"]');

  document.querySelectorAll('[data-edit-category]').forEach((button) => {
    button.addEventListener('click', () => {
      idInput.value = button.dataset.id;
      nameInput.value = button.dataset.name;
      slugInput.value = button.dataset.slug;
      nameInput.scrollIntoView({ behavior: 'smooth' });
    });
  });
}

const symbolsNote = document.querySelector('[data-available-symbols]');
if (symbolsNote) {
  const symbols = JSON.parse(symbolsNote.dataset.availableSymbols || '[]');
  const warning = symbolsNote.querySelector('[data-symbol-warning]');
  const pairInput = document.querySelector('input[name=\"pair\"]');
  const monitoringCheckbox = document.querySelector('input[name=\"monitoring_enabled\"]');

  const updateWarning = () => {
    if (!monitoringCheckbox?.checked || !pairInput) {
      warning.textContent = '';
      return;
    }
    const pair = pairInput.value.trim().toUpperCase();
    if (pair && !symbols.includes(pair)) {
      warning.textContent = 'این نماد در فایل قیمت وجود ندارد؛ سیگنال ثبت می‌شود اما بروزرسانی خودکار ندارد.';
    } else {
      warning.textContent = '';
    }
  };

  pairInput?.addEventListener('input', updateWarning);
  monitoringCheckbox?.addEventListener('change', updateWarning);
  updateWarning();
}
