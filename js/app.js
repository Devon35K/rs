/**
 * AcadPortal - Reading Skills Platform JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initUserDropdown();
    initCardToggles();
    initFlashMessages();
});

/**
 * User Dropdown
 */
function initUserDropdown() {
    const avatar = document.querySelector('.user-avatar');
    const dropdown = document.querySelector('.user-dropdown');
    
    if (avatar && dropdown) {
        avatar.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        document.addEventListener('click', function() {
            dropdown.style.display = 'none';
        });
    }
}

/**
 * Card Expand/Collapse
 */
function initCardToggles() {
    // Cards with onclick already handled by inline functions
}

function toggleCard(card) {
    const expanded = card.querySelector('.doc-card-expanded');
    if (expanded) {
        const isVisible = expanded.style.display === 'block';
        expanded.style.display = isVisible ? 'none' : 'block';
        card.classList.toggle('expanded', !isVisible);
    }
}

function toggleAll(gridId = 'announcementGrid') {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    
    const cards = grid.querySelectorAll('.doc-card');
    const anyExpanded = Array.from(cards).some(c => 
        c.querySelector('.doc-card-expanded')?.style.display === 'block'
    );
    
    cards.forEach(card => {
        const expanded = card.querySelector('.doc-card-expanded');
        if (expanded) {
            expanded.style.display = anyExpanded ? 'none' : 'block';
            card.classList.toggle('expanded', !anyExpanded);
        }
    });
}

/**
 * Flash Messages
 */
function initFlashMessages() {
    const flashes = document.querySelectorAll('.flash');
    flashes.forEach(flash => {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(100%)';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    });
}

/**
 * Modal Functions
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
    }
});

/**
 * Form Validation
 */
function validateForm(form) {
    const required = form.querySelectorAll('[required]');
    let valid = true;
    
    required.forEach(field => {
        if (!field.value.trim()) {
            valid = false;
            field.style.borderColor = '#dc3545';
        } else {
            field.style.borderColor = '';
        }
    });
    
    return valid;
}

/**
 * File Upload Preview
 */
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Confirm Delete
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * Table Sorting (for memo table)
 */
function sortTable(table, column, asc = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aVal = a.cells[column].textContent.trim();
        const bVal = b.cells[column].textContent.trim();
        
        if (asc) {
            return aVal.localeCompare(bVal);
        } else {
            return bVal.localeCompare(aVal);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}
