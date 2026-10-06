<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $trxId = (int) $_POST['id'];
    $userId = (int) $_SESSION['user_id'];

    if ($trxId > 0) {

        $stmt = $pdo->prepare(
            "DELETE FROM transactions
             WHERE id = ?
               AND user_id = ?"
        );
        $stmt->execute([$trxId, $userId]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => 'Transaksi berhasil dihapus.',
        ];
    }

    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
