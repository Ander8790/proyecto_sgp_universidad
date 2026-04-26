<?php
// Mock session
session_name('sgp_session');
session_start();
$_SESSION['role_id'] = 1;
$_SESSION['user_id'] = 1;

// Simulate request
$_SERVER['HTTP_X_PJAX'] = '1';
$_GET['url'] = 'asistencias';
$_GET['vista'] = 'mensual';
$_GET['mes'] = '03';
$_GET['anio'] = '2026';

// Run app
chdir('public');
require 'index.php';
