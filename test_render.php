<?php
$_SERVER['HTTP_X_PJAX'] = '1';
session_start();
$_SESSION['role_id'] = 1;
$_SESSION['user_id'] = 1;
$_GET['vista'] = 'mensual';
$_GET['mes'] = '03';
$_GET['anio'] = '2026';
require 'public/index.php';
