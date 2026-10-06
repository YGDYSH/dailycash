<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int) $_SESSION['user_id'];

/* =========================================
   PERIODE
   ========================================= */

$period = $_GET['period'] ?? 'month';
if (!in_array($period, ['month', 'year', 'all'], true)) {
    $period = 'month';
}

$periodLabels = [
    'month' => 'Bulan Ini',
    'year' => 'Tahun Ini',
    'all' => 'Semua Periode',
];

$where = 'user_id = ?';
$params = [$userId];

if ($period === 'month') {
    $where .= ' AND transaction_date BETWEEN ? AND ?';
    $params[] = date('Y-m-01');
    $params[] = date('Y-m-t');
} elseif ($period === 'year') {
    $where .= ' AND YEAR(transaction_date) = ?';
    $params[] = (int) date('Y');
}

/* =========================================
   RINGKASAN PERIODE
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS total_expense,
            COUNT(*) AS total_count
     FROM transactions
     WHERE $where"
);
$stmt->execute($params);
$summary = $stmt->fetch();

$totalIncome = (float) $summary['total_income'];
$totalExpense = (float) $summary['total_expense'];
$totalCount = (int) $summary['total_count'];
$balance = $totalIncome - $totalExpense;

/* =========================================
   ARUS KEUANGAN (sesuai periode)
   ========================================= */

$incomeSeries = [];
$expenseSeries = [];
$chartLabels = [];
$chartSubtitle = '';

if ($period === 'month') {

    $bulanPanjang = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $days = (int) date('t');
    $chartLabels = range(1, $days);
    $incomeSeries = array_fill(0, $days, 0);
    $expenseSeries = array_fill(0, $days, 0);
    $chartSubtitle = 'Per hari - ' . $bulanPanjang[(int) date('n') - 1] . ' ' . date('Y');

    $stmt = $pdo->prepare(
        "SELECT type, DAY(transaction_date) AS periode, COALESCE(SUM(amount), 0) AS total
         FROM transactions
         WHERE $where
         GROUP BY type, DAY(transaction_date)"
    );
    $stmt->execute($params);

    foreach ($stmt as $row) {
        $index = ((int) $row['periode']) - 1;
        if ($index < 0 || $index >= $days) {
            continue;
        }
        if ($row['type'] === 'income') {
            $incomeSeries[$index] = (float) $row['total'];
        } else {
            $expenseSeries[$index] = (float) $row['total'];
        }
    }

} elseif ($period === 'year') {

    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
              'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $chartLabels = $bulan;
    $incomeSeries = array_fill(0, 12, 0);
    $expenseSeries = array_fill(0, 12, 0);
    $chartSubtitle = 'Per bulan - ' . date('Y');

    $stmt = $pdo->prepare(
        "SELECT type, MONTH(transaction_date) AS periode, COALESCE(SUM(amount), 0) AS total
         FROM transactions
         WHERE $where
         GROUP BY type, MONTH(transaction_date)"
    );
    $stmt->execute($params);

    foreach ($stmt as $row) {
        $index = ((int) $row['periode']) - 1;
        if ($index < 0 || $index > 11) {
            continue;
        }
        if ($row['type'] === 'income') {
            $incomeSeries[$index] = (float) $row['total'];
        } else {
            $expenseSeries[$index] = (float) $row['total'];
        }
    }

} else {

    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
              'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $chartSubtitle = 'Per bulan - semua periode';

    $stmt = $pdo->prepare(
        "SELECT type, YEAR(transaction_date) AS thn, MONTH(transaction_date) AS bln,
                COALESCE(SUM(amount), 0) AS total
         FROM transactions
         WHERE $where
         GROUP BY type, YEAR(transaction_date), MONTH(transaction_date)
         ORDER BY thn, bln"
    );
    $stmt->execute($params);

    $labelMap = [];
    $incomeMap = [];
    $expenseMap = [];

    foreach ($stmt as $row) {
        $key = $row['thn'] . '-' . str_pad((string) $row['bln'], 2, '0', STR_PAD_LEFT);

        if (!isset($labelMap[$key])) {
            $labelMap[$key] = $bulan[((int) $row['bln']) - 1] . ' ' . substr((string) $row['thn'], 2);
        }

        if ($row['type'] === 'income') {
            $incomeMap[$key] = ($incomeMap[$key] ?? 0) + (float) $row['total'];
        } else {
            $expenseMap[$key] = ($expenseMap[$key] ?? 0) + (float) $row['total'];
        }
    }

    if (empty($labelMap)) {
        $chartLabels = $bulan;
        $incomeSeries = array_fill(0, 12, 0);
        $expenseSeries = array_fill(0, 12, 0);
        $chartSubtitle = 'Belum ada data transaksi';
    } else {
        foreach (array_keys($labelMap) as $key) {
            $chartLabels[] = $labelMap[$key];
            $incomeSeries[] = $incomeMap[$key] ?? 0;
            $expenseSeries[] = $expenseMap[$key] ?? 0;
        }
    }
}

/* =========================================
   PENGELUARAN PER KATEGORI (PERIODE)
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT COALESCE(NULLIF(category, ''), 'Lainnya') AS category,
            COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE $where
       AND type = 'expense'
     GROUP BY category
     ORDER BY total DESC"
);
$stmt->execute($params);

$categoryLabels = [];
$categoryValues = [];

foreach ($stmt as $row) {
    $categoryLabels[] = $row['category'];
    $categoryValues[] = (float) $row['total'];
}

/* =========================================
   DATA CHART UNTUK JAVASCRIPT
   ========================================= */

$chartData = [
    'labels' => $chartLabels,
    'income' => $incomeSeries,
    'expense' => $expenseSeries,
    'categories' => [
        'labels' => $categoryLabels,
        'values' => $categoryValues,
    ],
];

$activePage = 'reports';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">

    <!-- =====================================
         PAGE HEADER
         ===================================== -->

    <div class="page-header">

        <div>
            <h1>Laporan</h1>
            <p class="page-sub">Analisis pemasukan dan pengeluaran berdasarkan periode.</p>
        </div>

        <nav class="filter-tabs" aria-label="Filter periode">
            <?php foreach ($periodLabels as $key => $label): ?>
                <a href="?period=<?= $key ?>"
                   class="filter-chip <?= $period === $key ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </nav>

    </div>


    <!-- =====================================
         RINGKASAN PERIODE
         ===================================== -->

    <section class="stats-grid">

        <article class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pemasukan</span>
                <span class="stat-icon income">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 19V5"/>
                        <path d="M5 12l7-7 7 7"/>
                    </svg>
                </span>
            </div>
            <strong class="stat-value income"><?= dc_rupiah($totalIncome) ?></strong>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pengeluaran</span>
                <span class="stat-icon expense">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14"/>
                        <path d="M19 12l-7 7-7-7"/>
                    </svg>
                </span>
            </div>
            <strong class="stat-value expense"><?= dc_rupiah($totalExpense) ?></strong>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Selisih</span>
                <span class="stat-icon balance">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="6" width="18" height="13" rx="2"/>
                        <path d="M3 10h18"/>
                        <path d="M16 15h2"/>
                    </svg>
                </span>
            </div>
            <strong class="stat-value <?= $balance < 0 ? 'expense' : '' ?>">
                <?= dc_rupiah($balance) ?>
            </strong>
            <span class="stat-meta"><?= $totalCount ?> transaksi pada periode ini</span>
        </article>

    </section>


    <!-- =====================================
         CHARTS
         ===================================== -->

    <section class="charts-grid">

        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Arus Keuangan</h3>
                    <span class="card-sub"><?= htmlspecialchars($chartSubtitle) ?></span>
                </div>
            </div>
            <div class="chart-box">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Pengeluaran per Kategori</h3>
                    <span class="card-sub"><?= $periodLabels[$period] ?></span>
                </div>
            </div>

            <?php if (empty($categoryLabels)): ?>

                <div class="chart-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18"/>
                        <path d="M6 21v-8"/>
                        <path d="M12 21V5"/>
                        <path d="M18 21v-5"/>
                    </svg>
                    <p>Belum ada pengeluaran</p>
                    <span>Tidak ada data pengeluaran pada periode ini.</span>
                </div>

            <?php else: ?>

                <div class="chart-box chart-box-doughnut">
                    <canvas id="categoryChart"></canvas>
                </div>
                <ul class="legend-list" id="categoryLegend"></ul>

            <?php endif; ?>

        </div>

    </section>


    <!-- =====================================
         RINCIAN KATEGORI
         ===================================== -->

    <div class="card">

        <div class="card-header">
            <h3>Rincian Pengeluaran</h3>
            <span class="card-sub"><?= $periodLabels[$period] ?></span>
        </div>

        <?php if (empty($categoryLabels)): ?>

            <div class="empty-state">
                <span class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18"/>
                        <path d="M6 21v-8"/>
                        <path d="M12 21V5"/>
                        <path d="M18 21v-5"/>
                    </svg>
                </span>
                <h4>Belum ada rincian pengeluaran</h4>
                <p>Tambahkan transaksi pengeluaran pada periode ini.</p>
                <a href="../transactions/create.php?type=expense" class="btn btn-primary">
                    + Tambah Pengeluaran
                </a>
            </div>

        <?php else: ?>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="text-right">Nominal</th>
                            <th style="width:34%;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoryLabels as $index => $cat): ?>
                            <?php
                            $value = $categoryValues[$index];
                            $percent = $totalExpense > 0
                                ? round(($value / $totalExpense) * 100, 1)
                                : 0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($cat) ?></strong>
                                </td>
                                <td class="text-right">
                                    <span class="amount-out"><?= dc_rupiah($value) ?></span>
                                </td>
                                <td>
                                    <div class="percent-wrap">
                                        <div class="percent-bar">
                                            <span style="width: <?= $percent ?>%;"></span>
                                        </div>
                                        <span class="percent-text"><?= $percent ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>

</main>

<script>
    window.DAILYCASH_DATA = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
