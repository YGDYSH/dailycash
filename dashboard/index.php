<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">

    <h1>Dashboard DailyCash</h1>

    <div class="card">
        <h2>
            Selamat datang,
            <?= htmlspecialchars($_SESSION['user_name']) ?>! 👋
        </h2>

        <p>
            Kamu berhasil login ke DailyCash.
        </p>

        <p>
            Email:
            <?= htmlspecialchars($_SESSION['user_email']) ?>
        </p>
    </div>

    <div class="stats-cards">

        <div class="card">
            <h3>Total Pemasukan</h3>
            <p class="amount">Rp 0</p>
        </div>

        <div class="card">
            <h3>Total Pengeluaran</h3>
            <p class="amount">Rp 0</p>
        </div>

        <div class="card">
            <h3>Saldo</h3>
            <p class="amount">Rp 0</p>
        </div>

    </div>

    <div class="card">
        <h2>Menu</h2>

        <p>
            <a href="../transactions/index.php">
                Transaksi
            </a>
        </p>

        <p>
            <a href="../auth/logout.php">
                Logout
            </a>
        </p>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>