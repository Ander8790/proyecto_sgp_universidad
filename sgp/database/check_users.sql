-- Verificar usuarios en la base de datos
SELECT 
    id,
    correo,
    rol_id,
    estado,
    created_at
FROM usuarios
ORDER BY id;
