<?php require_once "C:/xampp/htdocs/proyecto_sgp/sgp/app/bootstrap.php"; try { $c = new UsersController(); $c->buscar(); } catch (Exception $e) { echo $e->getMessage(); }
