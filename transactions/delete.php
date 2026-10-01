<?php

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // TODO: Delete transaction logic
    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;