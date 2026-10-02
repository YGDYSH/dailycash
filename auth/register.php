<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($nama === '' || $email === '' || $password === '' || $password_confirm === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar.';
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (nama, email, password)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $nama,
                $email,
                $hashedPassword
            ]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">

    <h1>Registrasi</h1>

    <?php if ($error): ?>
        <div class="alert error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label for="nama">Nama</label>
            <input
                type="text"
                id="nama"
                name="nama"
                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <div class="form-group">
            <label for="password_confirm">Konfirmasi Password</label>
            <input
                type="password"
                id="password_confirm"
                name="password_confirm"
                required
            >
        </div>

        <button type="submit">Daftar</button>

    </form>

    <p>
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </p>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>