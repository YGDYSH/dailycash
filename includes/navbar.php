<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navUserName = $_SESSION['user_name'] ?? 'Pengguna';

$navNameParts = preg_split('/\s+/', trim($navUserName));
$navInitials = '';
if ($navNameParts && $navNameParts[0] !== '') {
    $navInitials = function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($navNameParts[0], 0, 1))
        : strtoupper(substr($navNameParts[0], 0, 1));
    if (isset($navNameParts[1]) && $navNameParts[1] !== '') {
        $navInitials .= function_exists('mb_substr')
            ? mb_strtoupper(mb_substr($navNameParts[1], 0, 1))
            : strtoupper(substr($navNameParts[1], 0, 1));
    }
}
if ($navInitials === '') {
    $navInitials = 'U';
}
?>

<header class="navbar">
    <div class="navbar-container">

        <button id="sidebarToggle" class="sidebar-toggle" aria-label="Buka menu navigasi" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" aria-hidden="true">
                <path d="M4 7h16"/>
                <path d="M4 12h16"/>
                <path d="M4 17h16"/>
            </svg>
        </button>

        <span class="navbar-title">DailyCash</span>

        <div class="navbar-actions">
            <span class="user-greeting">
                Selamat datang, <strong><?= htmlspecialchars($navUserName) ?></strong>
            </span>
            <span class="avatar" aria-hidden="true">
                <?= htmlspecialchars($navInitials) ?>
            </span>
            <a href="/auth/logout.php" class="btn btn-ghost btn-sm">Logout</a>
        </div>

    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
