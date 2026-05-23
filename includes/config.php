<?php
/* ---------------------------------------------------------------------
 *  Maheesha Jewels - Database configuration & connection
 *
 *  Uses the default XAMPP MySQL credentials:
 *      host     = localhost
 *      username = root
 *      password = (empty)
 *  Change the values below if your setup is different.
 * ------------------------------------------------------------------- */

define('DB_HOST', 'localhost');
define('DB_NAME', 'jewelry_store');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'Maheesha Jewels');
define('CURRENCY',  'Rs. ');

/* Create a single PDO connection used across the whole site. */
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please import database/jewelry_store.sql and check includes/config.php.');
}

/* Start a session for cart, login and flash messages. */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
