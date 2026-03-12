<?php
$file = 'c:\\xampp\\htdocs\\proyecto_sgp\\sgp\\app\\views\\asignaciones\\index.php';
$content = file($file);

$style_chunk = array_slice($content, 27, 77); // lines 28-104 (index 27 size 77)
$html_js_chunk = array_slice($content, 384, 656); // lines 385-1040 (index 384 size 656)

// Build modal_asignacion.php
$modal_content = "<style>\n" . implode("", $style_chunk) . "</style>\n\n" . implode("", $html_js_chunk);
file_put_contents('c:\\xampp\\htdocs\\proyecto_sgp\\sgp\\app\\views\\inc\\modal_asignacion.php', $modal_content);

// Modify index.php
array_splice($content, 384, 656, ["<!-- COMPONENTE: MODAL DE ASIGNACIONES -->\n", "<?php require APPROOT . '/views/inc/modal_asignacion.php'; ?>\n\n"]);
array_splice($content, 28, 76, []); // remove CSS rules from index.php

file_put_contents($file, implode("", $content));
echo "Extraccion exitosa.";
