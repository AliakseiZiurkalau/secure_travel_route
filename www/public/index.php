<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Перенаправление на страницу настройки подключения
header('Location: connect.php');
exit;
