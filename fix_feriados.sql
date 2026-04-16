-- ============================================================
-- FIX: Corregir nombres corruptos en tabla dias_feriados
-- Causa: caracteres UTF-8 guardados con encoding incorrecto
-- Solucion: reemplazar con nombres limpios sin acentos
-- ============================================================

UPDATE dias_feriados SET nombre = 'Dia de la Resistencia Indigena'              WHERE id = 1;
UPDATE dias_feriados SET nombre = 'Aniversario de Ciudad Bolivar'               WHERE id = 2;
UPDATE dias_feriados SET nombre = 'Inmaculada Concepcion'                        WHERE id = 3;
UPDATE dias_feriados SET nombre = 'Muerte del Libertador Simon Bolivar'          WHERE id = 4;
UPDATE dias_feriados SET nombre = 'Nochebuena'                                   WHERE id = 5;
UPDATE dias_feriados SET nombre = 'Navidad'                                      WHERE id = 6;
UPDATE dias_feriados SET nombre = 'Fin de Ano'                                   WHERE id = 7;
UPDATE dias_feriados SET nombre = 'Ano Nuevo'                                    WHERE id = 8;
UPDATE dias_feriados SET nombre = 'Dia de Reyes'                                 WHERE id = 9;
UPDATE dias_feriados SET nombre = 'Dia de la Juventud'                           WHERE id = 10;
UPDATE dias_feriados SET nombre = 'Carnaval - Lunes'                             WHERE id = 11;
UPDATE dias_feriados SET nombre = 'Carnaval - Martes'                            WHERE id = 12;
UPDATE dias_feriados SET nombre = 'San Jose'                                     WHERE id = 13;
UPDATE dias_feriados SET nombre = 'Lunes Santo - Semana Santa'                   WHERE id = 14;
UPDATE dias_feriados SET nombre = 'Martes Santo - Semana Santa'                  WHERE id = 15;
UPDATE dias_feriados SET nombre = 'Miercoles Santo - Semana Santa'               WHERE id = 16;
UPDATE dias_feriados SET nombre = 'Jueves Santo - Semana Santa'                  WHERE id = 17;
UPDATE dias_feriados SET nombre = 'Viernes Santo - Semana Santa'                 WHERE id = 18;
UPDATE dias_feriados SET nombre = 'Declaracion de Independencia'                 WHERE id = 19;
UPDATE dias_feriados SET nombre = 'Dia del Trabajador'                           WHERE id = 20;
UPDATE dias_feriados SET nombre = 'Batalla de Carabobo / Dia del Ejercito'       WHERE id = 21;
UPDATE dias_feriados SET nombre = 'Dia de la Independencia'                      WHERE id = 22;
UPDATE dias_feriados SET nombre = 'Natalicio del Libertador Simon Bolivar'       WHERE id = 23;
UPDATE dias_feriados SET nombre = 'Aniversario fundacion Ciudad Bolivar'         WHERE id = 24;

-- Verificar resultado
SELECT id, fecha, nombre FROM dias_feriados ORDER BY fecha;
