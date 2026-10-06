<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int) $_SESSION['user_id'];
$categories = dc_categories();

$selectedType = (($_GET['type'] ?? '') === 'expense') ? 'expense' : 'income';

$old = [
    'type' => $selectedType,
    'category' => '',
    'description' => '',
    'amount' => '',
    'date' => date('Y-m-d'),
];

$errors = [];

/* =========================================
   PROSES SIMPAN
   ========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['type'] = $_POST['type'] ?? $selectedType;
    $old['category'] = trim((string) ($_POST['category'] ?? ''));
    $old['description'] = trim((string) ($_POST['description'] ?? ''));
    $old['amount'] = trim((string) ($_POST['amount'] ?? ''));
    $old['date'] = trim((string) ($_POST['date'] ?? ''));

    if (!in_array($old['type'], ['income', 'expense'], true)) {
        $errors[] = 'Tipe transaksi tidak valid.';
    }

    if ($old['category'] === '') {
        $errors[] = 'Kategori wajib dipilih.';
    }

    if ($old['amount'] === '' || !is_numeric($old['amount']) || (float) $old['amount'] <= 0) {
        $errors[] = 'Jumlah harus berupa angka lebih dari 0.';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $old['date']) || strtotime($old['date']) === false) {
        $errors[] = 'Tanggal tidak valid.';
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare(
            "INSERT INTO transactions (user_id, type, category, amount, description, transaction_date, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $userId,
            $old['type'],
            $old['category'],
            (float) $old['amount'],
            $old['description'],
            $old['date'],
        ]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => 'Transaksi berhasil ditambahkan.',
        ];

        header('Location: index.php');
        exit;
    }
}

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
            <h1>Tambah Transaksi</h1>
            <p class="page-sub">Catat pemasukan atau pengeluaran barumu.</p>
        </div>

        <a href="index.php" class="btn btn-secondary">Kembali</a>

    </div>

    <?php if (!empty($errors)): ?>

        <div class="alert error" style="margin-bottom:18px;">
            <strong>Transaksi gagal disimpan.</strong><br>
            <?php foreach ($errors as $error): ?>
                &bull; <?= htmlspecialchars($error) ?><br>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>


    <!-- =====================================
         FORM
         ===================================== -->

    <div class="card form-card">

        <form method="POST" action="">

            <div class="form-grid">

                <!-- Tipe -->
                <div class="form-group">
                    <label for="type">Tipe Transaksi <span class="req">*</span></label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="income" <?= $old['type'] === 'income' ? 'selected' : '' ?>>
                            Pemasukan
                        </option>
                        <option value="expense" <?= $old['type'] === 'expense' ? 'selected' : '' ?>>
                            Pengeluaran
                        </option>
                    </select>
                </div>

                <!-- Kategori -->
                <div class="form-group">
                    <label for="category">Kategori <span class="req">*</span></label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Pilih Kategori</option>

                        <optgroup label="Pemasukan" data-type="income">
                            <?php foreach ($categories['income'] as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"
                                    data-type="income"
                                    <?= $old['category'] === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>

                        <optgroup label="Pengeluaran" data-type="expense">
                            <?php foreach ($categories['expense'] as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"
                                    data-type="expense"
                                    <?= $old['category'] === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>

                    </select>
                    <span class="form-hint">Kategori otomatis menyesuaikan tipe transaksi.</span>
                </div>

                <!-- Jumlah -->
                <div class="form-group">
                    <label for="amount">Jumlah (Rp) <span class="req">*</span></label>
                    <input type="number"
                           id="amount"
                           name="amount"
                           class="form-control amount-input"
                           step="0.01"
                           min="1"
                           placeholder="contoh: 25000"
                           value="<?= htmlspecialchars($old['amount']) ?>"
                           required>
                </div>

                <!-- Tanggal -->
                <div class="form-group">
                    <label for="date">Tanggal <span class="req">*</span></label>
                    <input type="date"
                           id="date"
                           name="date"
                           class="form-control"
                           value="<?= htmlspecialchars($old['date']) ?>"
                           required>
                </div>

                <!-- Deskripsi -->
                <div class="form-group full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description"
                              name="description"
                              class="form-control"
                              placeholder="contoh: Makan siang bersama tim"
                              rows="3"><?= htmlspecialchars($old['description']) ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>

        </form>

    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
