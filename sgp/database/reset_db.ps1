# SGP - Importacion de Base de Datos
# =====================================

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = "c:\xampp\htdocs\proyecto_sgp\sgp\database\sgp_master.sql"
$dbName = "proyecto_sgp"

Write-Host "Eliminando BD anterior..." -ForegroundColor Yellow
& $mysqlPath -u root -e "DROP DATABASE IF EXISTS $dbName;"

Write-Host "Creando BD limpia..." -ForegroundColor Yellow
& $mysqlPath -u root -e "CREATE DATABASE $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

Write-Host "Importando datos..." -ForegroundColor Yellow
Get-Content $sqlFile | & $mysqlPath -u root --default-character-set=utf8mb4 $dbName

Write-Host "Base de datos restaurada exitosamente" -ForegroundColor Green
Write-Host ""
Write-Host "Credenciales:" -ForegroundColor Cyan
Write-Host "admin@sgp.local / Sgp123*"
Write-Host "tutor@sgp.local / Sgp123*"
Write-Host "pasante@sgp.local / Sgp123*"
