<?php
require_once __DIR__ . '/../config/config.php';

session_destroy();
redirect(url('login.php'));
