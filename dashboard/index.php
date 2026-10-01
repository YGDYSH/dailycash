<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <h1>Dashboard</h1>
    <div class="stats-cards">
        <div class="card">
            <h3>Total Pemasukan</h3>
            <p class="amount income">Rp 0</p>
        </div>
        <div class="card">
            <h3>Total Pengeluaran</h3>
            <p class="amount expense">Rp 0</p>
        </div>
        <div class="card">
            <h3>Saldo</h3>
            <p class="amount balance">Rp 0</p>
        </div>
    </div>
    <div class="charts-section">
        <div class="chart-container">
            <canvas id="incomeExpenseChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
    <div class="recent-transactions">
        <h2>Transaksi Terbaru</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                    <th>Tipe</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">Belum ada transaksi</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>