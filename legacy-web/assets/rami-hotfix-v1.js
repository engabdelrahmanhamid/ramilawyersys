(function () {
  'use strict';

  const API_BASE = 'https://back.ramilawyersys.com/apiAdmin';
  const SETTINGS_KEY = 'settings';

  function parseMaybeJson(value) {
    let result = value;
    for (let index = 0; index < 3 && typeof result === 'string'; index += 1) {
      try {
        result = JSON.parse(result);
      } catch (_) {
        break;
      }
    }
    return result;
  }

  function getPersistedUser() {
    const persisted = parseMaybeJson(localStorage.getItem('persist:root')) || {};
    return parseMaybeJson(persisted.user) || {};
  }

  function getToken() {
    return getPersistedUser().token || null;
  }

  function getThemeMode() {
    const settings = parseMaybeJson(localStorage.getItem(SETTINGS_KEY)) || {};
    if (settings.themeMode === 'dark' || settings.themeMode === 'light') {
      return settings.themeMode;
    }
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
      ? 'dark'
      : 'light';
  }

  function applyThemeMode() {
    document.documentElement.setAttribute('data-rami-theme', getThemeMode());
  }

  function installThemeSync() {
    applyThemeMode();
    window.addEventListener('storage', function (event) {
      if (event.key === SETTINGS_KEY) applyThemeMode();
    });
    window.setInterval(applyThemeMode, 750);
  }

  async function apiGet(path) {
    const token = getToken();
    if (!token) {
      throw new Error('انتهت جلسة الدخول. سجل الدخول مرة أخرى.');
    }

    const response = await fetch(API_BASE + path, {
      headers: {
        Accept: 'application/json',
        Authorization: 'Bearer ' + token,
        lang: 'ar',
      },
    });

    if (!response.ok) {
      throw new Error('تعذر تحميل البيانات (' + response.status + ').');
    }

    const payload = await response.json();
    if (!payload || payload.status !== 1) {
      throw new Error((payload && payload.message) || 'تعذر تحميل البيانات.');
    }
    return payload.data;
  }

  function text(value, fallback) {
    if (value === null || value === undefined || value === '') return fallback || '—';
    return String(value);
  }

  function createElement(tag, className, content) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (content !== undefined) element.textContent = content;
    return element;
  }

  function addDetail(container, label, value, wide) {
    const item = createElement('div', 'rami-detail' + (wide ? ' rami-detail--wide' : ''));
    item.appendChild(createElement('span', 'rami-detail__label', label));
    item.appendChild(createElement('div', 'rami-detail__value', text(value)));
    container.appendChild(item);
  }

  function closeModal() {
    const backdrop = document.querySelector('.rami-modal-backdrop');
    if (backdrop) backdrop.remove();
    document.body.style.overflow = '';
  }

  function createModal() {
    closeModal();
    const backdrop = createElement('div', 'rami-modal-backdrop');
    const modal = createElement('section', 'rami-modal');
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'rami-contact-title');

    const header = createElement('header', 'rami-modal__header');
    const title = createElement('h2', '', 'تفاصيل التواصل');
    title.id = 'rami-contact-title';
    const close = createElement('button', 'rami-modal__close', '×');
    close.type = 'button';
    close.setAttribute('aria-label', 'إغلاق');
    close.addEventListener('click', closeModal);
    header.append(title, close);

    const body = createElement('div', 'rami-modal__body');
    body.appendChild(createElement('div', 'rami-loading', 'جاري تحميل تفاصيل التواصل…'));
    modal.append(header, body);
    backdrop.appendChild(modal);
    backdrop.addEventListener('click', function (event) {
      if (event.target === backdrop) closeModal();
    });
    document.body.appendChild(backdrop);
    document.body.style.overflow = 'hidden';
    close.focus();
    return body;
  }

  async function showContactDetails(contactId) {
    const body = createModal();
    try {
      const contact = await apiGet('/opponent/contact?contact_id=' + encodeURIComponent(contactId));
      body.textContent = '';
      addDetail(body, 'رقم التواصل', contact.id);
      addDetail(body, 'العميل', contact.client && contact.client.name);
      addDetail(body, 'الموظف المختص', contact.admin && contact.admin.name);
      addDetail(body, 'وسيلة التواصل', Number(contact.method) === 2 ? 'من طرف العميل' : 'من طرفنا');
      addDetail(body, 'سبب التواصل', contact.contact_reason && contact.contact_reason.name);
      addDetail(body, 'نوع التواصل', contact.type && contact.type.name);
      addDetail(body, 'التاريخ', contact.date);
      addDetail(body, 'الوصف', contact.description, true);
    } catch (error) {
      body.textContent = '';
      body.appendChild(createElement('div', 'rami-error', error.message));
    }
  }

  function installContactDetailsInterceptor() {
    // Let React choose the exact contact id, then intercept only the router
    // navigation. This avoids reading duplicated ids from the rendered table.
    if (!history.__ramiContactPreviewInstalled) {
      const originalPushState = history.pushState.bind(history);
      history.pushState = function (state, title, url) {
        const target = url === undefined || url === null
          ? null
          : new URL(String(url), location.href);
        const match = target && target.pathname.match(/^\/contact\/(\d+)\/?$/);

        if (match && location.pathname.startsWith('/contact/list')) {
          showContactDetails(match[1]);
          return;
        }

        return originalPushState(state, title, url);
      };
      history.__ramiContactPreviewInstalled = true;
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && document.querySelector('.rami-modal-backdrop')) closeModal();
    });
  }

  function injectOpponentsLink() {
    if (document.querySelector('.rami-opponents-link')) return true;
    const navigation = document.querySelector('nav');
    if (!navigation) return false;

    const labels = Array.from(navigation.querySelectorAll('button, a, [role="button"], .label'));
    const casesLabel = labels.find(function (element) {
      return element.textContent.trim() === 'القضايا';
    });
    if (!casesLabel) return false;

    const casesControl = casesLabel.closest('button, a, [role="button"], .MuiListItemButton-root') || casesLabel;

    const anchor = createElement('a', 'rami-opponents-link');
    anchor.href = '/case/list?rami_view=opponents';
    anchor.setAttribute('aria-label', 'قائمة الخصوم');
    anchor.append(
      createElement('span', 'rami-opponents-link__icon', '⚖'),
      createElement('span', '', 'الخصوم')
    );

    const insertionPoint = casesControl.closest('li') || casesControl;
    insertionPoint.insertAdjacentElement('afterend', anchor);
    return true;
  }

  function installOpponentsLinkObserver() {
    const observer = new MutationObserver(function () {
      injectOpponentsLink();
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
    injectOpponentsLink();
  }

  function opponentsViewRequested() {
    return location.pathname.startsWith('/case/list') &&
      new URLSearchParams(location.search).get('rami_view') === 'opponents';
  }

  function createEmbeddedOpponentsView() {
    const main = document.querySelector('main');
    if (!main || main.querySelector('.rami-opponents-app-view')) return false;

    const view = createElement('section', 'rami-opponents-app-view');
    view.innerHTML = [
      '<header class="rami-embedded-page-header">',
      '  <h1>قائمة الخصوم</h1>',
      '  <nav class="rami-breadcrumbs" aria-label="مسار الصفحة">',
      '    <a href="/dashboard">لوحة التحكم</a>',
      '    <span aria-hidden="true">•</span>',
      '    <a href="/case/list">القضايا</a>',
      '    <span aria-hidden="true">•</span>',
      '    <span>قائمة الخصوم</span>',
      '  </nav>',
      '</header>',
      '<section class="rami-card" aria-label="قائمة الخصوم">',
      '  <div class="rami-toolbar">',
      '    <input id="opponent-search" class="rami-search" type="search" placeholder="ابحث باسم الخصم…" autocomplete="off" aria-label="البحث عن خصم" />',
      '    <span id="opponents-count" class="rami-count"></span>',
      '  </div>',
      '  <div class="rami-table-wrap">',
      '    <table class="rami-table">',
      '      <thead><tr><th>اسم الخصم</th><th>اسم العميل</th><th>نوع الخصم</th><th>الفرع</th><th>عدد القضايا</th><th>عدد العملاء</th><th>الحالة</th></tr></thead>',
      '      <tbody id="opponents-body"></tbody>',
      '    </table>',
      '  </div>',
      '  <p id="opponents-status" class="rami-loading" aria-live="polite"></p>',
      '  <footer class="rami-pagination">',
      '    <button id="opponents-previous" type="button">السابق</button>',
      '    <span id="opponents-page-label">صفحة 1 من 1</span>',
      '    <button id="opponents-next" type="button">التالي</button>',
      '  </footer>',
      '</section>',
    ].join('');

    main.classList.add('rami-opponents-host');
    main.appendChild(view);
    initOpponentsPage();
    return true;
  }

  function installEmbeddedOpponentsView() {
    if (!opponentsViewRequested()) return;

    const observer = new MutationObserver(function () {
      if (opponentsViewRequested()) { createEmbeddedOpponentsView(); } else { document.querySelectorAll('.rami-opponents-app-view').forEach(function (view) { view.remove(); }); document.querySelectorAll('main.rami-opponents-host').forEach(function (main) { main.classList.remove('rami-opponents-host'); }); }
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
    if (opponentsViewRequested()) { createEmbeddedOpponentsView(); } else { document.querySelectorAll('.rami-opponents-app-view').forEach(function (view) { view.remove(); }); document.querySelectorAll('main.rami-opponents-host').forEach(function (main) { main.classList.remove('rami-opponents-host'); }); }
  }

  async function initOpponentsPage() {
    const search = document.getElementById('opponent-search');
    const tbody = document.getElementById('opponents-body');
    const count = document.getElementById('opponents-count');
    const status = document.getElementById('opponents-status');
    const previous = document.getElementById('opponents-previous');
    const next = document.getElementById('opponents-next');
    const pageLabel = document.getElementById('opponents-page-label');
    if (!search || !tbody || !count || !status || !previous || !next || !pageLabel) return;
    let page = 1;
    let totalPages = 1;
    let debounceTimer;

    if (!getToken()) {
      location.replace('/login');
      return;
    }

    function renderRows(items) {
      tbody.textContent = '';
      items.forEach(function (opponent) {
        const row = document.createElement('tr');
        const values = [
          opponent.name,
          Array.isArray(opponent.client_names) ? opponent.client_names.join('، ') : '',
          opponent.opponent_type && opponent.opponent_type.name,
          opponent.branch && opponent.branch.name,
          opponent.cases_count,
          opponent.clients_count,
        ];
        values.forEach(function (value) {
          row.appendChild(createElement('td', '', text(value)));
        });

        const registeredCell = document.createElement('td');
        registeredCell.appendChild(createElement('span', 'rami-chip', 'مسجل في قضية'));
        row.appendChild(registeredCell);

        tbody.appendChild(row);
      });
    }

    async function load() {
      status.textContent = 'جاري تحميل قائمة الخصوم…';
      tbody.textContent = '';
      try {
        const query = new URLSearchParams({
          page: String(page),
          limit: '10',
          search_text: search.value.trim(),
        });
        const response = await apiGet('/opponent/get?' + query.toString());
        const items = response.data || [];
        const pagination = response.pagination || {};
        totalPages = Math.max(1, Number(pagination.total_pages) || 1);
        renderRows(items);
        count.textContent = 'إجمالي الخصوم: ' + (Number(pagination.total) || 0);
        pageLabel.textContent = 'صفحة ' + page + ' من ' + totalPages;
        previous.disabled = page <= 1;
        next.disabled = page >= totalPages;
        status.textContent = items.length
          ? ''
          : search.value.trim()
            ? 'الخصم غير مسجل في القضايا الحالية.'
            : 'لا توجد أسماء خصوم مسجلة في القضايا.';
      } catch (error) {
        status.textContent = error.message;
      }
    }

    search.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        page = 1;
        load();
      }, 400);
    });
    previous.addEventListener('click', function () {
      if (page > 1) {
        page -= 1;
        load();
      }
    });
    next.addEventListener('click', function () {
      if (page < totalPages) {
        page += 1;
        load();
      }
    });
    load();
  }

  installThemeSync();

  if (document.body.classList.contains('rami-opponents-page')) {
    initOpponentsPage();
  } else {
    installContactDetailsInterceptor();
    installOpponentsLinkObserver();
    installEmbeddedOpponentsView();
  }
})();
