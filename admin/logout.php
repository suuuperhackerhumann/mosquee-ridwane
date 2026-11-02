<?php
require_once __DIR__ . '/../config/config.php';
$_SESSION = [];
session_destroy();
redirect('/admin/index.php');
