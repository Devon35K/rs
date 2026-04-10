/* ============================================================
   AcadPortal — Main JavaScript
   Handles: modals, expand-all toggle, flash dismiss,
            datatable search/sort/paginate
   ============================================================ */

'use strict';

/* ── Modal ── */
function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = 'none';
  document.body.style.overflow = '';
}

// Close on backdrop click
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});

// Close on Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
      modal.style.display = 'none';
    });
    document.body.style.overflow = '';
  }
});

/* ── Expand All / Collapse All ── */
function toggleAll(gridId) {
  const grid = document.getElementById(gridId);
  if (!grid) return;
  const btn = document.querySelector('[onclick="toggleAll(\'' + gridId + '\')"]');
  const isExpanded = grid.dataset.expanded === 'true';

  if (isExpanded) {
    grid.style.gridTemplateColumns = '';
    grid.dataset.expanded = 'false';
    if (btn) btn.textContent = 'Expand All';
  } else {
    grid.style.gridTemplateColumns = '1fr';
    grid.dataset.expanded = 'true';
    if (btn) btn.textContent = 'Collapse';
  }
}

/* ── Flash Message Auto-Dismiss ── */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.flash').forEach(function (flash) {
    setTimeout(function () {
      flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-8px)';
      setTimeout(function () { flash.remove(); }, 500);
    }, 4000);
  });
});

/* ── Simple DataTable ── */
document.addEventListener('DOMContentLoaded', function () {
  const tables = document.querySelectorAll('.datatable');
  tables.forEach(initDataTable);
});

function initDataTable(table) {
  const wrapper     = table.closest('.datatable-wrapper');
  if (!wrapper) return;

  const searchInput = wrapper.querySelector('.datatable-search-input');
  const showSelect  = wrapper.querySelector('.datatable-show-select');
  const info        = wrapper.querySelector('.datatable-info');
  const pagination  = wrapper.querySelector('.datatable-pagination');
  const headers     = table.querySelectorAll('thead th[data-sort]');

  let allRows     = Array.from(table.querySelectorAll('tbody tr'));
  let filtered    = allRows.slice();
  let currentPage = 1;
  let perPage     = parseInt(showSelect ? showSelect.value : 10);
  let sortCol     = -1;
  let sortAsc     = true;

  // ── Search ──
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = this.value.toLowerCase().trim();
      filtered = allRows.filter(function (row) {
        return row.textContent.toLowerCase().includes(q);
      });
      currentPage = 1;
      render();
    });
  }

  // ── Per-page select ──
  if (showSelect) {
    showSelect.addEventListener('change', function () {
      perPage = parseInt(this.value);
      currentPage = 1;
      render();
    });
  }

  // ── Sort ──
  headers.forEach(function (th) {
    th.addEventListener('click', function () {
      const col = parseInt(this.dataset.sort);
      if (sortCol === col) {
        sortAsc = !sortAsc;
      } else {
        sortCol = col;
        sortAsc = true;
      }

      // Update icons
      headers.forEach(function (h) {
        const icon = h.querySelector('.sort-icon');
        if (icon) icon.textContent = '⇅';
      });
      const myIcon = this.querySelector('.sort-icon');
      if (myIcon) myIcon.textContent = sortAsc ? '↑' : '↓';

      filtered.sort(function (a, b) {
        const aText = (a.cells[col] ? a.cells[col].textContent : '').trim().toLowerCase();
        const bText = (b.cells[col] ? b.cells[col].textContent : '').trim().toLowerCase();
        const aNum  = parseFloat(aText);
        const bNum  = parseFloat(bText);
        if (!isNaN(aNum) && !isNaN(bNum)) {
          return sortAsc ? aNum - bNum : bNum - aNum;
        }
        return sortAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
      });

      currentPage = 1;
      render();
    });
  });

  // ── Render ──
  function render() {
    const total = filtered.length;
    const pages = Math.max(1, Math.ceil(total / perPage));
    currentPage = Math.min(currentPage, pages);

    const start = (currentPage - 1) * perPage;
    const end   = start + perPage;

    // Show/hide rows
    allRows.forEach(function (row) { row.style.display = 'none'; });
    filtered.slice(start, end).forEach(function (row) { row.style.display = ''; });

    // Info text
    if (info) {
      if (total === 0) {
        info.textContent = 'No results found';
      } else {
        info.textContent = 'Showing ' + (start + 1) + ' to ' + Math.min(end, total) + ' of ' + total + ' entries';
      }
    }

    // Pagination
    if (pagination) {
      pagination.innerHTML = '';

      const prev = document.createElement('button');
      prev.textContent = '‹';
      prev.disabled = currentPage === 1;
      prev.addEventListener('click', function () {
        if (currentPage > 1) { currentPage--; render(); }
      });
      pagination.appendChild(prev);

      // Page number buttons (max 5 shown)
      const range = pageRange(currentPage, pages);
      range.forEach(function (p) {
        if (p === '…') {
          const dots = document.createElement('span');
          dots.textContent = '…';
          dots.style.cssText = 'padding:0.3rem 0.4rem;color:var(--mid-gray);font-size:0.78rem;';
          pagination.appendChild(dots);
        } else {
          const btn = document.createElement('button');
          btn.textContent = p;
          if (p === currentPage) btn.classList.add('active');
          btn.addEventListener('click', function () {
            currentPage = p;
            render();
          });
          pagination.appendChild(btn);
        }
      });

      const next = document.createElement('button');
      next.textContent = '›';
      next.disabled = currentPage === pages;
      next.addEventListener('click', function () {
        if (currentPage < pages) { currentPage++; render(); }
      });
      pagination.appendChild(next);
    }
  }

  function pageRange(current, total) {
    if (total <= 7) return Array.from({ length: total }, function (_, i) { return i + 1; });
    const pages = [];
    pages.push(1);
    if (current > 3) pages.push('…');
    for (let p = Math.max(2, current - 1); p <= Math.min(total - 1, current + 1); p++) {
      pages.push(p);
    }
    if (current < total - 2) pages.push('…');
    pages.push(total);
    return pages;
  }

  // Initial render
  render();
}

/* ── Confirm Delete ── */
function confirmDelete(message) {
  return confirm(message || 'Are you sure you want to delete this item?');
}

/* ── File input label update ── */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[type="file"]').forEach(function (input) {
    input.addEventListener('change', function () {
      const label = this.nextElementSibling;
      if (label && label.classList.contains('file-label')) {
        label.textContent = this.files.length ? this.files[0].name : 'No file chosen';
      }
    });
  });
});
