<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Registrasi berhasil. Silakan login.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = 'Email dan password wajib diisi.';

    } else {

        $stmt = $pdo->prepare("
            SELECT id, nama, email, password
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: ../dashboard/index.php');
            exit;

        } else {

            $error = 'Email atau password salah.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">

    <div class="auth-card">

        <!-- =====================================
             LEFT PANEL
             ===================================== -->

        <div class="auth-brand">

            <div class="brand-header">

                <div class="brand-icon">
                    ▣
                </div>

                <div>
                    <h2>DailyCash</h2>
                    <span>Smart Finance Platform</span>
                </div>

            </div>

            <p class="brand-description">
                Kelola keuanganmu dengan mudah dan capai
                kestabilan finansial setiap hari.
            </p>


            <!-- Balance Card -->

            <div class="finance-card balance-card">

                <div class="finance-card-header">
                    <span>Dompet Utama Aktif</span>

                    <span class="percentage">
                        ↗ +14.8%
                    </span>
                </div>

                <h3>Rp 14.250.000</h3>

                <div class="progress">
                    <div class="progress-value"></div>
                </div>

                <div class="finance-card-footer">
                    <span>Target tabungan: 75% tercapai</span>
                    <span>Rp 19.000.000</span>
                </div>

            </div>


            <!-- Salary Card -->

            <div class="finance-card mini-card">

                <div class="mini-icon">
                    ▣
                </div>

                <div class="mini-content">

                    <strong>Gaji Bulanan Masuk</strong>

                    <span>
                        1 Nov • Otomatis Tercatat
                    </span>

                </div>

                <div class="mini-value">
                    +Rp 8.500.000
                </div>

            </div>


            <!-- Report Card -->

            <div class="finance-card mini-card">

                <div class="mini-icon chart-icon">
                    ▥
                </div>

                <div class="mini-content">

                    <strong>Laporan Pengeluaran Realtime</strong>

                    <span>
                        Analisis kategori makan, tagihan & belanja
                    </span>

                </div>

            </div>


            <!-- Security -->

            <div class="security-info">

                <span>♢</span>

                <p>
                    Enkripsi data perbankan 256-bit
                    standar industri.
                </p>

            </div>

        </div>


        <!-- =====================================
             RIGHT PANEL
             ===================================== -->

        <div class="auth-form">

            <div class="login-content">

                <h1>Login</h1>

                <p class="login-subtitle">
                    Kelola keuanganmu dengan mudah
                </p>


                <?php if ($success): ?>

                    <div class="alert success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


                <?php if ($error): ?>

                    <div class="alert error">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <form method="POST" action="">

                    <!-- Email -->

                    <div class="input-group">

                        <label for="email">
                            Email
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                ✉
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="nama@email.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- Password -->

                    <div class="input-group">

                        <label for="password">
                            Password
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                ♙
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi Anda"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword()"
                                aria-label="Tampilkan password"
                            >
                                ◉
                            </button>

                        </div>

                    </div>


                    <!-- Remember / Forgot -->

                    <div class="form-options">

                        <label class="remember">

                            <input
                                type="checkbox"
                                name="remember"
                            >

                            <span>Ingat Saya</span>

                        </label>

                        <a href="#">
                            Lupa Password?
                        </a>

                    </div>


                    <!-- Login -->

                    <button
                        type="submit"
                        class="login-button"
                    >
                        Login
                        <span>→</span>
                    </button>

                </form>


                <!-- Divider -->

                <div class="divider">

                    <span></span>

                    <small>
                        ATAU MASUK DENGAN
                    </small>

                    <span></span>

                </div>


                <!-- Social buttons -->

                <div class="social-login">

                    <button type="button">
                        <strong>G</strong>
                        Google
                    </button>

                    <button type="button">
                        <strong>●</strong>
                        Apple
                    </button>

                </div>


                <!-- Register -->

                <p class="register-text">

                    Belum punya akun?

                    <a href="register.php">
                        Daftar di sini
                    </a>

                </p>

            </div>

        </div>

    </div>


    <!-- Footer -->

    <footer class="auth-footer">

        © 2025 DailyCash. All rights reserved.

    </footer>

</div>


<script>

function togglePassword() {

    const password =
        document.getElementById('password');

    const button =
        document.querySelector('.password-toggle');

    if (password.type === 'password') {

        password.type = 'text';
        button.textContent = '◉';

    } else {

        password.type = 'password';
        button.textContent = '◉';
    }
}

</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>