<?php
/* Log the current user out. */
require_once __DIR__ . '/includes/functions.php';
unset($_SESSION['user']);
set_flash('You have been logged out.');
redirect('index.php');
