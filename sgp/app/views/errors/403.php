<?php
require_once __DIR__ . '/_error_helpers.php';
$errCode   = '403';
$errIcon   = 'ti-lock';
$errAccent = '#dc2626';
$errBg     = '#fef2f2';
$errTitle  = 'Acceso Denegado';
$errSub    = 'No tienes permisos para acceder a este módulo. Si crees que es un error, contacta al administrador del sistema.';
$errHint   = 'Tu rol actual no tiene autorización para ver este recurso.';
$errActions = [
    ['label' => 'Mi Dashboard', 'href' => $_homeLink,                  'icon' => 'ti-layout-dashboard', 'primary' => true],
    ['label' => 'Volver Atrás', 'href' => 'javascript:history.back()', 'icon' => 'ti-arrow-left',       'primary' => false],
];
require __DIR__ . '/_error_layout.php';
