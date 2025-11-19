-- Fix para la restricción de clave foránea en activity_logs
-- Este script permite que user_id sea NULL en activity_logs

USE login5_db;

-- Eliminar la restricción de clave foránea existente
ALTER TABLE `activity_logs` DROP FOREIGN KEY `activity_logs_ibfk_1`;

-- Volver a crear la restricción permitiendo NULL
ALTER TABLE `activity_logs` 
ADD CONSTRAINT `activity_logs_ibfk_1` 
FOREIGN KEY (`user_id`) 
REFERENCES `users` (`id`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Verificar que la columna user_id permita NULL
ALTER TABLE `activity_logs` 
MODIFY COLUMN `user_id` INT(11) NULL;
