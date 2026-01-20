@echo off
echo ==========================================
echo Verificando usuarios en la base de datos
echo ==========================================
echo.

cd /d "c:\xampp\mysql\bin"
mysql -u root -e "USE proyecto_sgp; SELECT id, correo, rol_id, estado FROM usuarios ORDER BY id;"

echo.
echo ==========================================
echo Intentando actualizar contraseña admin
echo ==========================================
echo.

cd /d "c:\xampp\htdocs\proyecto_sgp\sgp\database"
c:\xampp\php\php.exe update_password.php

pause
