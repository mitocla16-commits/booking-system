<?php
require __DIR__ . '/../helpers.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
