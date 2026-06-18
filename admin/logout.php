<?php
require __DIR__ . '/../inc/auth.php';
admin_logout();
header('Location: login.php');
exit;
