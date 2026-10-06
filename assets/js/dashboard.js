// Dashboard JavaScript for DailyCash

document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initCharts();
    initTransactionForm();
});

/* =========================================
   FORM TRANSAKSI (CREATE / EDIT)
   ========================================= */

function initTransactionForm() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category');

    if (!typeSelect || !categorySelect) {
        return;
    }

    function syncCategories() {
        const activeType = typeSelect.value;

        categorySelect.querySelectorAll('optgroup').forEach(function (group) {
            group.hidden = group.dataset.type !== activeType;
        });

        const selected = categorySelect.selectedOptions[0];
        if (selected && selected.value && selected.dataset.type !== activeType) {
            categorySelect.value = '';
        }
    }

    typeSelect.addEventListener('change', syncCategories);
    syncCategories();
}

/* =========================================
   SIDEBAR TOGGLE (MOBILE)
   ========================================= */

function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggle || !sidebar) {
        return;
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        if (overlay) {
            overlay.classList.remove('show');
        }
    }

    toggle.addEventListener('click', function () {
        const isOpen = sidebar.classList.toggle('open');
        if (overlay) {
            overlay.classList.toggle('show', isOpen);
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
}

/* =========================================
   CHARTS
   ========================================= */

const CATEGORY_COLORS = [
    '#10B981',
    '#3B82F6',
    '#8B5CF6',
    '#F59E0B',
    '#EF4444',
    '#06B6D4',
    '#64748B'
];

function initCharts() {
    if (typeof Chart === 'undefined') {
        return;
    }

    const data = window.DAILYCASH_DATA || {};

    renderFlowChart(data);
    renderCategoryChart(data);
}

function renderFlowChart(data) {
    const ctx = document.getElementById('incomeExpenseChart');
    if (!ctx) {
        return;
    }

    const defaultLabels = [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
    ];

    const labels = (Array.isArray(data.labels) && data.labels.length)
        ? data.labels
        : defaultLabels;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: padSeries(data.income, labels.length),
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: '#10B981',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 22
                },
                {
                    label: 'Pengeluaran',
                    data: padSeries(data.expense, labels.length),
                    backgroundColor: 'rgba(248, 113, 113, 0.85)',
                    borderColor: '#F87171',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 22
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 7,
                        boxHeight: 7,
                        color: '#64748B',
                        font: { size: 11.5, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: '#172033',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return ' ' + context.dataset.label + ': ' + formatRupiah(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#94A3B8',
                        font: { size: 11 }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#EEF2F7' },
                    border: { display: false },
                    ticks: {
                        color: '#94A3B8',
                        font: { size: 11 },
                        callback: function (value) {
                            return shortRupiah(value);
                        }
                    }
                }
            }
        }
    });
}

function renderCategoryChart(data) {
    const ctx = document.getElementById('categoryChart');
    const categories = data.categories || {};
    const labels = categories.labels || [];
    const values = categories.values || [];

    if (!ctx || !labels.length) {
        return;
    }

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: labels.map(function (_, i) {
                    return CATEGORY_COLORS[i % CATEGORY_COLORS.length];
                }),
                borderColor: '#FFFFFF',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#172033',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return ' ' + context.label + ': ' + formatRupiah(context.parsed);
                        }
                    }
                }
            }
        }
    });

    renderCategoryLegend(labels, values);
}

function renderCategoryLegend(labels, values) {
    const container = document.getElementById('categoryLegend');
    if (!container) {
        return;
    }

    container.innerHTML = '';

    labels.forEach(function (label, index) {
        const item = document.createElement('li');
        item.className = 'legend-item';

        const dot = document.createElement('span');
        dot.className = 'legend-dot';
        dot.style.backgroundColor = CATEGORY_COLORS[index % CATEGORY_COLORS.length];

        const name = document.createElement('span');
        name.className = 'legend-name';
        name.textContent = label;

        const value = document.createElement('span');
        value.className = 'legend-val';
        value.textContent = formatRupiah(values[index] || 0);

        item.appendChild(dot);
        item.appendChild(name);
        item.appendChild(value);
        container.appendChild(item);
    });
}

/* =========================================
   HELPERS
   ========================================= */

function padSeries(series, length) {
    const result = Array.isArray(series) ? series.slice() : [];
    while (result.length < length) {
        result.push(0);
    }
    return result;
}

function formatRupiah(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
}

function shortRupiah(value) {
    const number = Number(value || 0);

    if (Math.abs(number) >= 1e9) {
        return 'Rp ' + (number / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M';
    }
    if (Math.abs(number) >= 1e6) {
        return 'Rp ' + (number / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
    }
    if (Math.abs(number) >= 1e3) {
        return 'Rp ' + (number / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
    }

    return 'Rp ' + number.toLocaleString('id-ID');
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}
