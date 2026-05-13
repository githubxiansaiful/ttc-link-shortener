(function () {
	'use strict';

	if (typeof window.TTCLS === 'undefined') return;

	const cfg = window.TTCLS;
	const i18n = cfg.i18n || {};

	// ---------- Helpers ----------
	function $(sel, root) { return (root || document).querySelector(sel); }
	function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

	function escapeHtml(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function siteHost() {
		try {
			return new URL(cfg.home_url).host;
		} catch (e) {
			return cfg.home_url || '';
		}
	}

	function isValidUrl(value) {
		if (!value) return false;
		try {
			const u = new URL(value);
			return u.protocol === 'http:' || u.protocol === 'https:';
		} catch (e) {
			return false;
		}
	}

	function ajax(action, data) {
		const body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', cfg.nonce);
		Object.keys(data || {}).forEach(function (k) {
			body.append(k, data[k]);
		});
		return fetch(cfg.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (r) { return r.json(); });
	}

	// ---------- Toast ----------
	let toastEl = null;
	let toastTimer = null;
	function toast(msg) {
		if (!toastEl) {
			toastEl = document.createElement('div');
			toastEl.className = 'ttcls-toast';
			document.body.appendChild(toastEl);
		}
		toastEl.textContent = msg;
		toastEl.classList.add('is-visible');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			toastEl.classList.remove('is-visible');
		}, 1600);
	}

	// ---------- Theme ----------
	function applyTheme(theme) {
		const root = document.documentElement;
		if (theme === 'light') {
			root.setAttribute('data-theme', 'light');
		} else {
			root.removeAttribute('data-theme');
		}
	}

	function initTheme() {
		const stored = localStorage.getItem('ttcls-theme') || 'dark';
		applyTheme(stored);
		document.addEventListener('click', function (e) {
			const btn = e.target.closest('[data-ttcls-toggle="theme"]');
			if (!btn) return;
			const next = (localStorage.getItem('ttcls-theme') || 'dark') === 'light' ? 'dark' : 'light';
			localStorage.setItem('ttcls-theme', next);
			applyTheme(next);
		});
	}

	// ---------- Sidebar ----------
	function initSidebar() {
		const stored = localStorage.getItem('ttcls-sidebar') === 'collapsed';
		const app = $('[data-ttcls-app]');
		if (!app) return;
		if (stored) app.classList.add('is-collapsed');

		const isMobile = function () { return window.matchMedia('(max-width: 768px)').matches; };

		const openMobile = function () {
			app.classList.add('is-open');
			document.body.classList.add('ttcls-no-scroll');
		};
		const closeMobile = function () {
			app.classList.remove('is-open');
			document.body.classList.remove('ttcls-no-scroll');
		};

		document.addEventListener('click', function (e) {
			const toggle = e.target.closest('[data-ttcls-toggle="sidebar"]');
			if (toggle) {
				if (isMobile()) {
					app.classList.contains('is-open') ? closeMobile() : openMobile();
				} else {
					app.classList.toggle('is-collapsed');
					localStorage.setItem('ttcls-sidebar', app.classList.contains('is-collapsed') ? 'collapsed' : 'open');
				}
				return;
			}

			if (e.target.closest('[data-ttcls-close="sidebar"]') || e.target.closest('[data-ttcls-backdrop]')) {
				closeMobile();
				return;
			}

			// Click outside sidebar while open on mobile
			if (isMobile() && app.classList.contains('is-open') && !e.target.closest('.ttcls-sidebar')) {
				closeMobile();
			}
		});

		// Close on nav link tap (mobile UX)
		$$('.ttcls-sidebar .ttcls-nav-link').forEach(function (a) {
			a.addEventListener('click', function () {
				if (isMobile()) closeMobile();
			});
		});

		// Escape key
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && app.classList.contains('is-open')) {
				closeMobile();
			}
		});

		// Resize: clear mobile state if upgrading to desktop
		window.addEventListener('resize', function () {
			if (!isMobile()) {
				app.classList.remove('is-open');
				document.body.classList.remove('ttcls-no-scroll');
			}
		});
	}

	// ---------- Copy ----------
	function copyText(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}
		return new Promise(function (resolve, reject) {
			const ta = document.createElement('textarea');
			ta.value = text;
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.select();
			try {
				document.execCommand('copy');
				resolve();
			} catch (e) {
				reject(e);
			}
			document.body.removeChild(ta);
		});
	}

	function initCopyButtons() {
		document.addEventListener('click', function (e) {
			const btn = e.target.closest('[data-ttcls-copy]');
			if (!btn) return;
			e.preventDefault();
			copyText(btn.getAttribute('data-ttcls-copy'))
				.then(function () { toast(i18n.copied || 'Copied!'); })
				.catch(function () { toast('Copy failed'); });
		});
	}

	// ---------- Stats ----------
	function updateStats(totals) {
		if (!totals) return;
		const links = $('[data-ttcls-stat="links"]');
		const clicks = $('[data-ttcls-stat="clicks"]');
		if (links && typeof totals.links !== 'undefined') links.textContent = totals.links;
		if (clicks && typeof totals.clicks !== 'undefined') clicks.textContent = totals.clicks;
	}

	// ---------- Render: recent row ----------
	function renderRecentRow(row) {
		const host = siteHost();
		const li = document.createElement('li');
		li.className = 'ttcls-link-row';
		li.setAttribute('data-ttcls-row', row.id);
		li.innerHTML =
			'<div class="ttcls-link-main">' +
				'<a class="ttcls-link-short" href="' + escapeHtml(row.short_url) + '" target="_blank" rel="noopener">' +
					escapeHtml(host + '/' + row.slug) +
				'</a>' +
				'<span class="ttcls-link-dest" title="' + escapeHtml(row.destination) + '">' +
					escapeHtml(row.destination) +
				'</span>' +
			'</div>' +
			'<div class="ttcls-link-meta">' +
				'<span class="ttcls-badge">' + row.clicks + ' ' + escapeHtml(i18n.clicks || 'clicks') + '</span>' +
				'<button type="button" class="ttcls-iconbtn" data-ttcls-copy="' + escapeHtml(row.short_url) + '" aria-label="Copy">' +
					'<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
						'<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>' +
						'<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>' +
					'</svg>' +
				'</button>' +
				'<button type="button" class="ttcls-iconbtn ttcls-iconbtn-danger" data-ttcls-delete="' + row.id + '" aria-label="Delete">' +
					'<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
						'<polyline points="3 6 5 6 21 6"></polyline>' +
						'<path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>' +
						'<path d="M10 11v6"></path>' +
						'<path d="M14 11v6"></path>' +
					'</svg>' +
				'</button>' +
			'</div>';
		return li;
	}

	// ---------- Create form ----------
	function initCreateForm() {
		const form = $('[data-ttcls-form="create"]');
		if (!form) return;
		const msgEl = $('[data-ttcls-form-msg]', form);
		const input = form.querySelector('input[name="url"]');
		const slugInput = form.querySelector('input[name="slug"]');
		const submitBtn = form.querySelector('button[type="submit"]');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			const url = (input.value || '').trim();
			const slug = slugInput ? (slugInput.value || '').trim() : '';

			msgEl.textContent = '';
			msgEl.classList.remove('is-error', 'is-success');

			if (!isValidUrl(url)) {
				msgEl.textContent = i18n.invalid_url || 'Please enter a valid URL';
				msgEl.classList.add('is-error');
				input.focus();
				return;
			}

			if (slug && !/^[A-Za-z0-9](?:[A-Za-z0-9_-]{1,62}[A-Za-z0-9])?$/.test(slug)) {
				msgEl.textContent = i18n.invalid_slug || 'Slug can contain letters, numbers, hyphens, underscores (3–64 chars).';
				msgEl.classList.add('is-error');
				slugInput.focus();
				return;
			}

			const payload = { url: url };
			if (slug) payload.slug = slug;

			submitBtn.disabled = true;
			ajax('ttcls_create', payload)
				.then(function (resp) {
					if (!resp || !resp.success) {
						const err = resp && resp.data && resp.data.message ? resp.data.message : (i18n.create_failed || 'Failed');
						msgEl.textContent = err;
						msgEl.classList.add('is-error');
						if (resp && resp.data && resp.data.field === 'slug' && slugInput) {
							slugInput.focus();
						}
						return;
					}
					const row = resp.data;
					msgEl.textContent = row.short_url;
					msgEl.classList.add('is-success');
					input.value = '';
					if (slugInput) slugInput.value = '';

					// Prepend to recent list
					const list = $('[data-ttcls-recent]');
					if (list) {
						const empty = $('[data-ttcls-empty]', list);
						if (empty) empty.remove();
						list.insertBefore(renderRecentRow(row), list.firstChild);
						// Keep only first 5
						while (list.children.length > 5) {
							list.removeChild(list.lastChild);
						}
					}

					updateStats(row.totals);

					// Auto-copy
					copyText(row.short_url)
						.then(function () { toast(i18n.copied || 'Copied!'); })
						.catch(function () {});
				})
				.catch(function () {
					msgEl.textContent = i18n.create_failed || 'Network error';
					msgEl.classList.add('is-error');
				})
				.then(function () {
					submitBtn.disabled = false;
				});
		});
	}

	// ---------- Delete ----------
	function initDelete() {
		document.addEventListener('click', function (e) {
			const btn = e.target.closest('[data-ttcls-delete]');
			if (!btn) return;
			e.preventDefault();
			const id = btn.getAttribute('data-ttcls-delete');
			if (!id) return;
			if (!window.confirm(i18n.confirm_del || 'Delete this link?')) return;

			btn.disabled = true;
			ajax('ttcls_delete', { id: id })
				.then(function (resp) {
					if (!resp || !resp.success) {
						toast((resp && resp.data && resp.data.message) || (i18n.delete_failed || 'Failed'));
						btn.disabled = false;
						return;
					}
					$$('[data-ttcls-row="' + id + '"]').forEach(function (el) { el.remove(); });
					updateStats(resp.data.totals);

					// If recent list empty, restore empty state
					const list = $('[data-ttcls-recent]');
					if (list && list.children.length === 0) {
						const li = document.createElement('li');
						li.className = 'ttcls-empty';
						li.setAttribute('data-ttcls-empty', '');
						li.textContent = i18n.no_links || 'No links yet.';
						list.appendChild(li);
					}

					// All-links table reload
					if ($('[data-ttcls-page="all-links"]')) {
						loadList();
					}
				})
				.catch(function () {
					toast(i18n.delete_failed || 'Network error');
					btn.disabled = false;
				});
		});
	}

	// ---------- All-links table ----------
	const listState = { page: 1, perPage: 25, search: '' };
	let searchTimer = null;

	function renderTableRows(rows) {
		const tbody = $('[data-ttcls-tbody]');
		if (!tbody) return;
		if (!rows || !rows.length) {
			tbody.innerHTML = '<tr><td colspan="5" class="ttcls-empty">' + escapeHtml(i18n.no_links || 'No links yet.') + '</td></tr>';
			return;
		}
		const host = siteHost();
		const html = rows.map(function (row) {
			const dateStr = (row.created_at || '').replace('T', ' ').slice(0, 16);
			return '<tr data-ttcls-row="' + row.id + '">' +
				'<td><a class="ttcls-link-short" href="' + escapeHtml(row.short_url) + '" target="_blank" rel="noopener">' +
					escapeHtml(host + '/' + row.slug) + '</a></td>' +
				'<td class="ttcls-cell-dest" title="' + escapeHtml(row.destination) + '">' + escapeHtml(row.destination) + '</td>' +
				'<td class="ttcls-num">' + row.clicks + '</td>' +
				'<td>' + escapeHtml(dateStr) + '</td>' +
				'<td class="ttcls-actions-col">' +
					'<button type="button" class="ttcls-iconbtn" data-ttcls-copy="' + escapeHtml(row.short_url) + '" aria-label="Copy">' +
						'<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
							'<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>' +
							'<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>' +
						'</svg></button>' +
					'<button type="button" class="ttcls-iconbtn ttcls-iconbtn-danger" data-ttcls-delete="' + row.id + '" aria-label="Delete">' +
						'<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
							'<polyline points="3 6 5 6 21 6"></polyline>' +
							'<path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>' +
							'<path d="M10 11v6"></path><path d="M14 11v6"></path>' +
						'</svg></button>' +
				'</td>' +
			'</tr>';
		}).join('');
		tbody.innerHTML = html;
	}

	function loadList() {
		if (!$('[data-ttcls-page="all-links"]')) return;
		const tbody = $('[data-ttcls-tbody]');
		if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="ttcls-empty">' + escapeHtml(i18n.loading || 'Loading…') + '</td></tr>';

		ajax('ttcls_list', {
			page: listState.page,
			per_page: listState.perPage,
			search: listState.search
		}).then(function (resp) {
			if (!resp || !resp.success) {
				if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="ttcls-empty">Error</td></tr>';
				return;
			}
			renderTableRows(resp.data.rows);
			const info = $('[data-ttcls-pager-info]');
			if (info) {
				const start = resp.data.total ? (resp.data.page - 1) * resp.data.per_page + 1 : 0;
				const end = Math.min(resp.data.page * resp.data.per_page, resp.data.total);
				info.textContent = start + '–' + end + ' / ' + resp.data.total;
			}
			const prev = $('[data-ttcls-pager="prev"]');
			const next = $('[data-ttcls-pager="next"]');
			if (prev) prev.disabled = resp.data.page <= 1;
			if (next) next.disabled = resp.data.page >= resp.data.pages;
		});
	}

	function initAllLinks() {
		if (!$('[data-ttcls-page="all-links"]')) return;

		document.addEventListener('click', function (e) {
			const pagerBtn = e.target.closest('[data-ttcls-pager]');
			if (!pagerBtn) return;
			e.preventDefault();
			if (pagerBtn.getAttribute('data-ttcls-pager') === 'prev') {
				if (listState.page > 1) { listState.page--; loadList(); }
			} else {
				listState.page++;
				loadList();
			}
		});

		const searchInput = $('[data-ttcls-search]');
		if (searchInput) {
			searchInput.addEventListener('input', function () {
				clearTimeout(searchTimer);
				searchTimer = setTimeout(function () {
					listState.search = searchInput.value.trim();
					listState.page = 1;
					loadList();
				}, 280);
			});
		}

		loadList();
	}

	// ---------- Login form (logged-out view) ----------
	function initLoginForm() {
		const form = $('[data-ttcls-form="login"]');
		if (!form) return;
		const msg = $('[data-ttcls-form-msg]', form);
		const submitBtn = form.querySelector('button[type="submit"]');
		const submitLabel = submitBtn ? submitBtn.textContent : 'Sign In';

		// Password show/hide
		const pwToggle = $('[data-ttcls-pw-toggle]', form);
		const pwInput = form.querySelector('input[name="password"]');
		if (pwToggle && pwInput) {
			pwToggle.addEventListener('click', function () {
				const show = pwInput.type === 'password';
				pwInput.type = show ? 'text' : 'password';
				pwToggle.classList.toggle('is-on', show);
			});
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			const username = (form.querySelector('[name="username"]').value || '').trim();
			const password = form.querySelector('[name="password"]').value || '';

			msg.textContent = '';
			msg.classList.remove('is-error', 'is-success');

			if (!username || !password) {
				msg.textContent = i18n.invalid_url ? 'Username and password required' : 'Username and password required';
				msg.classList.add('is-error');
				return;
			}

			submitBtn.disabled = true;
			submitBtn.textContent = i18n.signing_in || 'Signing in…';

			const body = new URLSearchParams();
			body.append('action', 'ttcls_login');
			body.append('nonce', cfg.login_nonce || '');
			body.append('username', username);
			body.append('password', password);

			fetch(cfg.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			})
				.then(function (r) { return r.json(); })
				.then(function (resp) {
					if (resp && resp.success && resp.data && resp.data.redirect) {
						window.location.href = resp.data.redirect;
						return;
					}
					const err = (resp && resp.data && resp.data.message) || (i18n.login_failed || 'Sign-in failed');
					msg.textContent = err;
					msg.classList.add('is-error');
					submitBtn.disabled = false;
					submitBtn.textContent = submitLabel;
				})
				.catch(function () {
					msg.textContent = i18n.login_failed || 'Network error';
					msg.classList.add('is-error');
					submitBtn.disabled = false;
					submitBtn.textContent = submitLabel;
				});
		});
	}

	// ---------- Init ----------
	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	ready(function () {
		initTheme();
		initSidebar();
		initCopyButtons();
		initCreateForm();
		initDelete();
		initAllLinks();
		initLoginForm();
	});
})();
