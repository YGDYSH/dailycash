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
   FILTER TIPE
   ========================================= */

$filter = $_GET['type'] ?? 'all';
if (!in_array($filter, ['all', 'income', 'expense'], true)) {
    $filter = 'all';
}

$filterLabels = [
    'all' => 'Semua',
    'income' => 'Pemasukan',
    'expense' => 'Pengeluaran',
];

/* =========================================
   RINGKASAN
   ========================================= */

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS total_expense,
            COUNT(*) AS total_count
     FROM transactions
     WHERE user_id = ?"
);
$stmt->execute([$userId]);
$summary = $stmt->fetch();

$totalIncome = (float) $summary['total_income'];
$totalExpense = (float) $summary['total_expense'];
$totalCount = (int) $summary['total_count'];
$balance = $totalIncome - $totalExpense;

/* =========================================
   DAFTAR TRANSAKSI
   ========================================= */

$sql = "SELECT id, type, category, amount, description, transaction_date
        FROM transactions
        WHERE user_id = ?";
$params = [$userId];

if ($filter !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $filter;
}

$sql .= " ORDER BY transaction_date DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$filteredCount = count($transactions);

/* =========================================
   FLASH MESSAGE
   ========================================= */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$activePage = 'transactions';

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
            <h1>Transaksi</h1>
            <p class="page-sub">Kelola pemasukan dan pengeluaranmu.</p>
        </div>

        <a href="create.php" class="btn btn-primary">
            + Tambah Transaksi
        </a>

    </div>

    <?php if ($flash): ?>
        <div class="alert <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
            <?= htmlspecialchars($flash['text']) ?>
        </div>
    <?php endif; ?>


    <!-- =====================================
         RINGKASAN
         ===================================== -->

    <section class="stats-grid">

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
        </article>

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
        </article>

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
            <span class="stat-meta"><?= $totalCount ?> transaksi tercatat</span>
        </article>

    </section>


    <!-- =====================================
         DAFTAR TRANSAKSI
         ===================================== -->

    <div class="card">

        <div class="card-header">
            <h3>Daftar Transaksi</h3>

            <nav class="filter-tabs" aria-label="Filter transaksi">
                <?php foreach ($filterLabels as $key => $label): ?>
                    <a href="?type=<?= $key ?>"
                       class="filter-chip <?= $filter === $key ? 'active' : '' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if ($totalCount === 0): ?>

            <!-- Empty state: belum ada transaksi sama sekali -->
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
                <a href="create.php" class="btn btn-primary">+ Tambah Transaksi</a>
            </div>

        <?php elseif ($filteredCount === 0): ?>

            <!-- Empty state: filter tidak cocok -->
            <div class="empty-state">
                <span class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 5h18l-7 8v6l-4 2v-8z"/>
                    </svg>
                </span>
                <h4>Tidak ada transaksi <?= htmlspecialchars($filterLabels[$filter]) ?></h4>
                <p>Coba filter lain atau tambahkan transaksi baru.</p>
                <a href="?type=all" class="btn btn-secondary">Tampilkan Semua</a>
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
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trx): ?>
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
                                        <span class="amount-in">+ <?= dc_rupiah($trx['amount']) ?></span>
                                    <?php else: ?>
                                        <span class="amount-out">- <?= dc_rupiah($trx['amount']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="action-group">

                                        <a href="edit.php?id=<?= (int) $trx['id'] ?>"
                                           class="action-btn edit" title="Edit transaksi"
                                           aria-label="Edit transaksi">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="1.9" stroke-linecap="round"
                                                 stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="delete.php"
                                              class="inline-form"
                                              onsubmit="return confirm('Hapus transaksi ini?');">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $trx['id'] ?>">
                                            <button type="submit"
                                                    class="action-btn delete"
                                                    title="Hapus transaksi"
                                                    aria-label="Hapus transaksi">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="1.9" stroke-linecap="round"
                                                     stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                    <path d="M10 11v6"/>
                                                    <path d="M14 11v6"/>
                                                </svg>
                                            </button>
                                        </form>

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
