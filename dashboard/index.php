<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

/* =========================================
   HELPERS
   ========================================= */

function dc_rupiah($value): string
{
    $sign = ((float) $value) < 0 ? '-' : '';
    return $sign . 'Rp ' . number_format(abs((float) $value), 0, ',', '.');
}

function dc_tanggal($date): string
{
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
              'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

    $timestamp = strtotime((string) $date);
    if ($timestamp === false) {
        return '-';
    }

    return sprintf(
        '%02d %s %s',
        (int) date('d', $timestamp),
        $bulan[(int) date('n', $timestamp) - 1],
        date('Y', $timestamp)
    );
}

/* =========================================
   USER DARI SESSION
   ========================================= */

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Pengguna';

$stmt = $pdo->prepare('SELECT nama FROM users WHERE id = ?');
$stmt->execute([$userId]);
$userRow = $stmt->fetch();

if ($userRow && $userRow['nama'] !== '') {
    $userName = $userRow['nama'];
    $_SESSION['user_name'] = $userRow['nama'];
}

/* =========================================
   TOTAL PEMASUKAN
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
       AND type = 'income'"
);
$stmt->execute([$userId]);
$totalIncome = (float) $stmt->fetchColumn();

/* =========================================
   TOTAL PENGELUARAN
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
       AND type = 'expense'"
);
$stmt->execute([$userId]);
$totalExpense = (float) $stmt->fetchColumn();

$balance = $totalIncome - $totalExpense;

/* =========================================
   PERUBAHAN BULAN INI
   ========================================= */

$currentMonth = (int) date('n');
$currentYear = (int) date('Y');

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
       AND type = 'income'
       AND MONTH(transaction_date) = ?
       AND YEAR(transaction_date) = ?"
);
$stmt->execute([$userId, $currentMonth, $currentYear]);
$monthIncome = (float) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
       AND type = 'expense'
       AND MONTH(transaction_date) = ?
       AND YEAR(transaction_date) = ?"
);
$stmt->execute([$userId, $currentMonth, $currentYear]);
$monthExpense = (float) $stmt->fetchColumn();

/* =========================================
   ARUS KEUANGAN PER BULAN (TAHUN BERJALAN)
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT type,
            MONTH(transaction_date) AS bulan,
            COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
       AND YEAR(transaction_date) = ?
     GROUP BY type, MONTH(transaction_date)"
);
$stmt->execute([$userId, $currentYear]);

$incomeMonthly = array_fill(0, 12, 0);
$expenseMonthly = array_fill(0, 12, 0);

foreach ($stmt as $row) {
    $index = ((int) $row['bulan']) - 1;

    if ($index < 0 || $index > 11) {
        continue;
    }

    if ($row['type'] === 'income') {
        $incomeMonthly[$index] = (float) $row['total'];
    } elseif ($row['type'] === 'expense') {
        $expenseMonthly[$index] = (float) $row['total'];
    }
}

/* =========================================
   PENGELUARAN BERDASARKAN KATEGORI
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT category,
            COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
       AND type = 'expense'
     GROUP BY category
     ORDER BY total DESC"
);
$stmt->execute([$userId]);

$categoryLabels = [];
$categoryValues = [];

foreach ($stmt as $row) {
    $label = ($row['category'] !== null && $row['category'] !== '')
        ? $row['category']
        : 'Lainnya';

    $categoryLabels[] = $label;
    $categoryValues[] = (float) $row['total'];
}

/* =========================================
   5 TRANSAKSI TERBARU
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT id, type, category, amount, description, transaction_date
     FROM transactions
     WHERE user_id = ?
     ORDER BY transaction_date DESC, id DESC
     LIMIT 5"
);
$stmt->execute([$userId]);
$recentTransactions = $stmt->fetchAll();

/* =========================================
   TOTAL JUMLAH TRANSAKSI (EMPTY STATE)
   ========================================= */

$stmt = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE user_id = ?');
$stmt->execute([$userId]);
$transactionCount = (int) $stmt->fetchColumn();

/* =========================================
   DATA CHART UNTUK JAVASCRIPT
   ========================================= */

$chartData = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
    'income' => $incomeMonthly,
    'expense' => $expenseMonthly,
    'categories' => [
        'labels' => $categoryLabels,
        'values' => $categoryValues,
    ],
];

$activePage = 'dashboard';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main-content">

    <!-- =====================================
         DASHBOARD HEADER
         ===================================== -->

    <div class="page-header">

        <div>
            <h1>
                Selamat datang kembali,
                <?= htmlspecialchars($userName) ?> 👋
            </h1>
            <p class="page-sub">Berikut ringkasan keuanganmu.</p>
        </div>

        <a href="../transactions/create.php" class="btn btn-primary">
            + Tambah Transaksi
        </a>

    </div>


    <!-- =====================================
         STATISTIC CARDS
         ===================================== -->

    <section class="stats-grid">

        <!-- Total Pemasukan -->
        <article class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Pemasukan</span>
                <span class="stat-icon income">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 19V5"/>
                        <path d="M5 12l7-7 7 7"/>
                    </svg>
                </span>
            </div>
            <strong class="stat-value income"><?= dc_rupiah($totalIncome) ?></strong>
            <span class="stat-meta">
                Pemasukan bulan ini: <?= dc_rupiah($monthIncome) ?>
            </span>
        </article>

        <!-- Total Pengeluaran -->
        <article class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Pengeluaran</span>
                <span class="stat-icon expense">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14"/>
                        <path d="M19 12l-7 7-7-7"/>
                    </svg>
                </span>
            </div>
            <strong class="stat-value expense"><?= dc_rupiah($totalExpense) ?></strong>
            <span class="stat-meta">
                Pengeluaran bulan ini: <?= dc_rupiah($monthExpense) ?>
            </span>
        </article>

        <!-- Saldo -->
        <article class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Saldo</span>
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
            <span class="stat-meta">
                <?= $transactionCount ?> transaksi tercatat
            </span>
        </article>

    </section>


    <!-- =====================================
         CHARTS
         ===================================== -->

    <section class="charts-grid">

        <!-- Arus Keuangan -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Arus Keuangan</h3>
                    <span class="card-sub">Pemasukan vs pengeluaran tahun <?= $currentYear ?></span>
                </div>
            </div>
            <div class="chart-box">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>

        <!-- Kategori Pengeluaran -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Pengeluaran Berdasarkan Kategori</h3>
                    <span class="card-sub">Akumulasi seluruh periode</span>
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
                    <p>Belum ada data pengeluaran</p>
                    <span>Tambahkan transaksi pengeluaran untuk melihat grafik kategori.</span>
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
         TRANSAKSI TERBARU + QUICK ACTIONS
         ===================================== -->

    <section class="bottom-grid">

        <div class="card">
            <div class="card-header">
                <h3>Transaksi Terbaru</h3>
                <a href="../transactions/index.php" class="link-more">Lihat semua</a>
            </div>

            <?php if ($transactionCount === 0): ?>

                <!-- Empty State -->
                <div class="empty-state">
                    <span class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/>
                            <path d="M16 12h3"/>
                            <path d="M3 9h16"/>
                        </svg>
                    </span>
                    <h4>Belum ada transaksi</h4>
                    <p>Mulai catat pemasukan atau pengeluaranmu.</p>
                    <a href="../transactions/create.php" class="btn btn-primary">
                        + Tambah Transaksi
                    </a>
                </div>

            <?php else: ?>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th>Jenis</th>
                                <th class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $trx): ?>
                                <tr>
                                    <td class="cell-date">
                                        <?= dc_tanggal($trx['transaction_date']) ?>
                                    </td>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($trx['category'] ?: 'Lainnya') ?>
                                        </strong>
                                    </td>
                                    <td class="cell-desc">
                                        <?= htmlspecialchars($trx['description'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <?php if ($trx['type'] === 'income'): ?>
                                            <span class="badge badge-income">Pemasukan</span>
                                        <?php else: ?>
                                            <span class="badge badge-expense">Pengeluaran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if ($trx['type'] === 'income'): ?>
                                            <span class="amount-in">
                                                + <?= dc_rupiah($trx['amount']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="amount-out">
                                                - <?= dc_rupiah($trx['amount']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>

        <!-- Quick Actions -->
        <div class="card quick-card">
            <h3>Quick Actions</h3>

            <div class="quick-list">
                <a href="../transactions/create.php?type=income" class="quick-btn income">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14"/>
                        <path d="M5 12h14"/>
                    </svg>
                    Pemasukan
                </a>

                <a href="../transactions/create.php?type=expense" class="quick-btn expense">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"/>
                    </svg>
                    Pengeluaran
                </a>
            </div>

            <p class="quick-hint">
                Catat keuanganmu secara rutin agar laporan tetap akurat.
            </p>
        </div>

    </section>

</main>

<script>
    window.DAILYCASH_DATA = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
