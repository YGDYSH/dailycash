<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Tambah Transaksi</h1>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label for="type">Tipe</label>
            <select id="type" name="type" required>
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">Pilih Kategori</option>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div class="form-group">
            <label for="amount">Jumlah</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label for="date">Tanggal</label>
            <input type="date" id="date" name="date" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>