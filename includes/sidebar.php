<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePage = $activePage ?? 'dashboard';
?>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <a href="/dashboard/index.php">
            <span class="sidebar-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/>
                    <path d="M16 12h3"/>
                    <path d="M3 9h16"/>
                </svg>
            </span>
            <span class="brand-text">DailyCash</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="/dashboard/index.php"
                   class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>"
                   title="Dashboard">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/transactions/index.php"
                   class="nav-link <?= $activePage === 'transactions' ? 'active' : '' ?>"
                   title="Transaksi">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 7h13l-3.2-3.2"/>
                        <path d="M20 17H7l3.2 3.2"/>
                    </svg>
                    <span class="nav-label">Transaksi</span>
                </a>
            </li>
            <li>
                <a href="/reports/index.php"
                   class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>"
                   title="Laporan">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18"/>
                        <path d="M6 21v-8"/>
                        <path d="M12 21V5"/>
                        <path d="M18 21v-5"/>
                    </svg>
                    <span class="nav-label">Laporan</span>
                </a>
            </li>
            <li>
                <a href="/settings/index.php"
                   class="nav-link <?= $activePage === 'settings' ? 'active' : '' ?>"
                   title="Pengaturan">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 8h9"/>
                        <path d="M17 8h3"/>
                        <path d="M4 16h4"/>
                        <path d="M12 16h8"/>
                        <circle cx="15" cy="8" r="2.2"/>
                        <circle cx="10" cy="16" r="2.2"/>
                    </svg>
                    <span class="nav-label">Pengaturan</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="/auth/logout.php" class="nav-link nav-logout" title="Logout">
                <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="M16 17l5-5-5-5"/>
                    <path d="M21 12H9"/>
                </svg>
                <span class="nav-label">Logout</span>
            </a>
        </div>
    </nav>

</aside>
