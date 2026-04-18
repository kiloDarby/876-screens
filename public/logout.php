<?php

require_once __DIR__ . '/../app/session.php';

$logoutMessage = 'You’ve been logged out. See you soon!';

unset($_SESSION['user']);
unset($_SESSION['errors']);
unset($_SESSION['old_input']);

$_SESSION['success'] = $logoutMessage;

header('Location: index.php');
exit;