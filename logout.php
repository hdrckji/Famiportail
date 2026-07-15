<?php
require_once __DIR__ . '/auth.php';
deconnecter();
header('Location: login.php');
exit;
