<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, nama, email, password FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$profileErrors = [];
$passwordErrors = [];

/* =========================================
   UPDATE PROFIL
   ========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'profile') {

    $nama = trim((string) ($_POST['nama'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($nama === '') {
        $profileErrors[] = 'Nama wajib diisi.';
    } elseif (mb_strlen($nama) > 100) {
        $profileErrors[] = 'Nama maksimal 100 karakter.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileErrors[] = 'Format email tidak valid.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $profileErrors[] = 'Email sudah digunakan akun lain.';
        }
    }

    if (empty($profileErrors)) {

        $stmt = $pdo->prepare('UPDATE users SET nama = ?, email = ? WHERE id = ?');
        $stmt->execute([$nama, $email, $userId]);

        $_SESSION['user_name'] = $nama;
        $_SESSION['user_email'] = $email;

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => 'Profil berhasil diperbarui.',
        ];

        header('Location: index.php');
        exit;
    }
}

/* =========================================
   UBAH PASSWORD
   ========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {

    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($current === '') {
        $passwordErrors[] = 'Password saat ini wajib diisi.';
    } elseif (!password_verify($current, $user['password'])) {
        $passwordErrors[] = 'Password saat ini salah.';
    }

    if (strlen($new) < 6) {
        $passwordErrors[] = 'Password baru minimal 6 karakter.';
    }

    if ($new !== $confirm) {
        $passwordErrors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($passwordErrors)) {

        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => 'Password berhasil diubah.',
        ];

        header('Location: index.php');
        exit;
    }
}

/* =========================================
   FLASH MESSAGE
   ========================================= */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$activePage = 'settings';

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
            <h1>Pengaturan</h1>
            <p class="page-sub">Kelola profil dan keamanan akunmu.</p>
        </div>

    </div>

    <?php if ($flash): ?>
        <div class="alert <?= htmlspecialchars($flash['type']) ?>" style="margin-bottom:18px;">
            <?= htmlspecialchars($flash['text']) ?>
        </div>
    <?php endif; ?>


    <!-- =====================================
         GRID FORM
         ===================================== -->

    <section class="settings-grid">

        <!-- =====================================
             PROFIL
             ===================================== -->

        <div class="card">

            <div class="card-header">
                <div>
                    <h3>Profil</h3>
                    <span class="card-sub">Informasi akun kamu</span>
                </div>
            </div>

            <?php if (!empty($profileErrors)): ?>
                <div class="alert error">
                    <?php foreach ($profileErrors as $error): ?>
                        &bull; <?= htmlspecialchars($error) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="form" value="profile">

                <div class="form-grid">

                    <div class="form-group full">
                        <label for="nama">Nama Lengkap <span class="req">*</span></label>
                        <input type="text"
                               id="nama"
                               name="nama"
                               class="form-control"
                               maxlength="100"
                               placeholder="Nama kamu"
                               value="<?= htmlspecialchars($user['nama']) ?>"
                               required>
                    </div>

                    <div class="form-group full">
                        <label for="email">Email <span class="req">*</span></label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               placeholder="nama@email.com"
                               value="<?= htmlspecialchars($user['email']) ?>"
                               required>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Profil</button>
                </div>

            </form>

        </div>


        <!-- =====================================
             KEAMANAN
             ===================================== -->

        <div class="card">

            <div class="card-header">
                <div>
                    <h3>Ubah Password</h3>
                    <span class="card-sub">Perbarui password akun kamu</span>
                </div>
            </div>

            <?php if (!empty($passwordErrors)): ?>
                <div class="alert error">
                    <?php foreach ($passwordErrors as $error): ?>
                        &bull; <?= htmlspecialchars($error) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="form" value="password">

                <div class="form-grid">

                    <div class="form-group full">
                        <label for="current_password">Password Saat Ini <span class="req">*</span></label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="form-control"
                               placeholder="Masukkan password saat ini"
                               required>
                    </div>

                    <div class="form-group full">
                        <label for="new_password">Password Baru <span class="req">*</span></label>
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               class="form-control"
                               minlength="6"
                               placeholder="Minimal 6 karakter"
                               required>
                    </div>

                    <div class="form-group full">
                        <label for="confirm_password">Ulangi Password Baru <span class="req">*</span></label>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-control"
                               minlength="6"
                               placeholder="Ketik ulang password baru"
                               required>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </div>

            </form>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
