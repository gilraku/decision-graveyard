(() => {
  const modal = document.getElementById('modal');
  const confirmDlg = document.getElementById('confirm');
  const toastEl = document.getElementById('toast');
  const announcer = document.getElementById('announcer');
  const canPopover = Object.hasOwn(HTMLElement.prototype, 'popover');

  let toastTimer;
  function toast(message) {
    if (!message) return;
    toastEl.textContent = message;
    announcer.textContent = '';
    requestAnimationFrame(() => { announcer.textContent = message; });

    if (canPopover) {
      if (toastEl.matches(':popover-open')) toastEl.hidePopover();
      toastEl.showPopover();
    }
    toastEl.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastEl.classList.remove('is-visible');
      if (canPopover && toastEl.matches(':popover-open')) setTimeout(() => toastEl.hidePopover(), 300);
    }, 3200);
  }
  toast(announcer.dataset.flash);

  if (!('closedBy' in HTMLDialogElement.prototype)) {
    const outside = (e, d) => {
      const r = d.getBoundingClientRect();
      return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
    };
    for (const d of document.querySelectorAll('dialog[closedby="any"]')) {
      let pressedOutside = false;
      d.addEventListener('pointerdown', (e) => { pressedOutside = outside(e, d); });
      d.addEventListener('pointerup', (e) => { if (pressedOutside && outside(e, d)) d.close(); });
    }
  }

  const openModal = () => { if (!modal.open) modal.showModal(); };
  const closeModal = () => { if (modal.open) modal.close(); };

  if (modal.hasAttribute('data-server-open')) {
    modal.removeAttribute('open');
    openModal();
    const url = new URL(location.href);
    url.searchParams.delete('edit');
    history.replaceState(null, '', url);
  }
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-close-modal]')) closeModal();
  });
  modal.addEventListener('close', () => {
    document.getElementById('modal-body').replaceChildren();
  });

  function askConfirm(text) {
    return new Promise((resolve) => {
      document.getElementById('confirm-text').textContent = text;
      confirmDlg.returnValue = '';
      confirmDlg.addEventListener('close', () => {
        const ok = confirmDlg.returnValue === 'ok';
        confirmDlg.returnValue = '';
        resolve(ok);
      }, { once: true });
      confirmDlg.showModal();
    });
  }

  document.body.addEventListener('htmx:confirm', (e) => {
    const question = e.detail.ctx?.confirm;
    if (!question) return;
    e.preventDefault();
    askConfirm(question).then((ok) => (ok ? e.detail.issueRequest() : e.detail.dropRequest()));
  });

  document.body.addEventListener('toast', (e) => toast(e.detail.value));
  document.body.addEventListener('closeModal', closeModal);
  document.addEventListener('htmx:after:swap', (e) => {
    const ctx = e.detail.ctx;
    if (ctx?.target?.id === 'modal-body') {
      openModal();
      modal.querySelector('[autofocus]')?.focus();
    }
    if (ctx?.hx?.retarget) {
      document.querySelector(`${ctx.hx.retarget} [aria-invalid="true"]`)?.focus();
    }
  });

  const syncAria = (el) => {
    if (!el.matches?.('input, select, textarea')) return;
    if (el.matches(':user-invalid')) el.setAttribute('aria-invalid', 'true');
    else if (!el.closest('.field')?.querySelector('.field__error')) el.setAttribute('aria-invalid', 'false');
  };
  document.querySelectorAll('input, select, textarea').forEach(syncAria);
  document.addEventListener('blur', (e) => syncAria(e.target), true);
  document.addEventListener('input', (e) => {
    const err = e.target.closest?.('.field')?.querySelector('.field__error');
    if (err && e.target.validity?.valid) {
      err.remove();
      e.target.removeAttribute('aria-describedby');
    }
    syncAria(e.target);
  });
  document.addEventListener('invalid', (e) => syncAria(e.target), true);

  document.body.addEventListener('animationend', (e) => {
    if (e.target.classList?.contains('is-new')) e.target.classList.remove('is-new');
  });

  document.addEventListener('htmx:response:error', () => {
    toast('Server menolak permintaan. Muat ulang halaman, lalu coba lagi.');
  });
  document.addEventListener('htmx:error', () => {
    toast('Tidak bisa terhubung ke server. Periksa koneksi, lalu coba lagi.');
  });
})();
