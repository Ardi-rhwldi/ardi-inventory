document.addEventListener('DOMContentLoaded', function() {
    
    initTooltips();
    initNumberFormatting();
    initConfirmDelete();
    
});

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initNumberFormatting() {
    const rupiahInputs = document.querySelectorAll('.rupiah-input');
    rupiahInputs.forEach(input => {
        input.addEventListener('blur', function() {
            formatRupiahInput(this);
        });
    });
}

function initConfirmDelete() {
    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
}

function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function formatRupiahInput(input) {
    let value = input.value.replace(/[^0-9]/g, '');
    input.value = formatRupiah(value);
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const main = document.querySelector('main');
    main.insertBefore(alertDiv, main.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
