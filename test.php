<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db_connect.php';

if(isset($conn)) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?> 