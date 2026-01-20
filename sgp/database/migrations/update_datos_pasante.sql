-- Actualizar tabla datos_pasante
-- Eliminar columnas innecesarias y agregar termino_exposicion

-- Eliminar columnas antiguas
ALTER TABLE datos_pasante 
DROP COLUMN grado_anio,
DROP COLUMN mencion,
DROP COLUMN periodo_pasantias;

-- Agregar nueva columna
ALTER TABLE datos_pasante 
ADD COLUMN termino_exposicion DATE NOT NULL AFTER institucion_procedencia;
