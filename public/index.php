<?php
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(url('dashboard.php'));
} else {
    redirect(url('login.php'));
}
