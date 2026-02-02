
-- DATABASE.SQL — Solitario Triangular
-- Script de creación de la Base de Datos y Tablas

--   - Bases de datos relacionales: esquema predefinido con tablas
--     relacionadas mediante claves primarias y foráneas.
--   - SQL (Structured Query Language): lenguaje estándar para
--     administrar y manipular bases de datos relacionales.
--   - Operaciones de modificación: CREATE, INSERT, UPDATE, DELETE
--   - Operaciones de consulta: SELECT, WHERE, ORDER BY, etc.
--
-- INSTRUCCIONES DE USO:
--   1. Acceder a phpMyAdmin: http://localhost/phpmyadmin/
--   2. Ir a la pestaña "SQL" (arriba)
--   3. Copiar y pegar TODO este script
--   4. Pulsar "Ejecutar" (o "Go")
--
--   Alternativa por terminal:
--   mysql -u root -p < sql/database.sql

-- 1. CREAR LA BASE DE DATOS
-- 
-- CREATE DATABASE: Crea una nueva base de datos en MySQL.
-- IF NOT EXISTS: Solo la crea si no existe ya (evita errores).
-- CHARACTER SET utf8mb4: Soporta tildes, ñ, emojis, etc.
-- COLLATE utf8mb4_unicode_ci: Reglas de ordenación para español.


CREATE DATABASE IF NOT EXISTS solitario_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- USE: Selecciona la base de datos con la que vamos a trabajar.
-- A partir de aquí, todas las operaciones se hacen sobre solitario_db.
USE solitario_db;



-- 2. TABLA: usuarios

-- Almacena los datos de los jugadores que se registran.

-- CREATE TABLE: Crea una nueva tabla en la base de datos.
-- IF NOT EXISTS: Solo la crea si no existe (seguridad).

-- TIPOS DE DATOS UTILIZADOS (UD4):
--   - INT: Número entero
--   - VARCHAR(n): Cadena de texto de longitud variable (máx n)
--   - TIMESTAMP: Fecha y hora
--   - AUTO_INCREMENT: El valor se incrementa automáticamente
--
-- RESTRICCIONES:
--   - PRIMARY KEY: Clave primaria (identifica cada fila de forma única)
--   - NOT NULL: El campo no puede estar vacío
--   - UNIQUE: No puede haber dos filas con el mismo valor
--   - DEFAULT: Valor por defecto si no se especifica otro


CREATE TABLE IF NOT EXISTS usuarios (
    -- id: Clave primaria autoincremental
    -- Cada usuario tendrá un número único (1, 2, 3, ...)
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- nombre: Nombre del jugador (obligatorio)
    -- VARCHAR(50): Hasta 50 caracteres, igual que el maxlength del HTML
    nombre VARCHAR(50) NOT NULL,

    -- email: Correo electrónico (opcional)
    -- VARCHAR(100): Hasta 100 caracteres
    -- DEFAULT NULL: Si no se proporciona, será NULL
    email VARCHAR(100) DEFAULT NULL,

    -- password: Contraseña HASHEADA (nunca en texto plano)
    -- VARCHAR(255): password_hash() de PHP genera cadenas largas
    -- NOT NULL: La contraseña es obligatoria
    password VARCHAR(255) NOT NULL,

    -- fecha_registro: Cuándo se registró el usuario
    -- CURRENT_TIMESTAMP: Se rellena automáticamente con la fecha actual
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 3. TABLA: puntuaciones

-- Almacena las puntuaciones de cada partida.
-- Está RELACIONADA con la tabla usuarios mediante una clave foránea.
-- CONCEPTO UD4: Las bases de datos relacionales usan claves
-- primarias y foráneas para relacionar tablas entre sí.

-- FOREIGN KEY (clave foránea): 
--   Enlaza esta tabla con la tabla usuarios.
--   usuario_id hace referencia al id de la tabla usuarios.
--   ON DELETE CASCADE: Si se borra un usuario, se borran sus puntuaciones.


CREATE TABLE IF NOT EXISTS puntuaciones (
    -- id: Clave primaria de cada puntuación
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- usuario_id: Referencia al usuario que hizo la puntuación
    -- Es una CLAVE FORÁNEA que apunta a usuarios.id
    usuario_id INT NOT NULL,

    -- fichas_restantes: Cuántas fichas quedaron al terminar
    -- Un valor menor es mejor (el ideal es 1)
    fichas_restantes INT NOT NULL,

    -- movimientos: Número de movimientos realizados
    movimientos INT NOT NULL,

    -- tiempo_segundos: Duración de la partida en segundos
    tiempo_segundos INT NOT NULL,

    -- puntuacion: Puntuación calculada
    -- Fórmula: se calcula en PHP al guardar
    puntuacion INT NOT NULL DEFAULT 0,

    -- fecha: Cuándo se jugó la partida
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- FOREIGN KEY: Crea la relación con la tabla usuarios
    -- REFERENCES usuarios(id): Apunta a la columna id de usuarios
    -- ON DELETE CASCADE: Si se borra el usuario, se borran sus puntuaciones
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);



-- 4. DATOS DE EJEMPLO (INSERT INTO)

-- INSERT INTO: Agrega nuevos registros a una tabla.
-- Sintaxis: INSERT INTO tabla (columna1, columna2) VALUES ('valor1', 'valor2');
--
-- La contraseña del ejemplo es 'test123' hasheada con password_hash().
-- NUNCA se almacenan contraseñas en texto plano.


-- Insertamos un usuario de ejemplo
-- La contraseña 'test123' hasheada con PASSWORD() de MySQL (solo para ejemplo)
-- En la aplicación real, se hashea con password_hash() de PHP
INSERT INTO usuarios (nombre, email, password) VALUES
    ('Ejemplo', 'ejemplo@test.com', '$2y$10$example_hash_placeholder_do_not_use_in_production');

-- Insertamos una puntuación de ejemplo para ese usuario (id=1)
INSERT INTO puntuaciones (usuario_id, fichas_restantes, movimientos, tiempo_segundos, puntuacion) VALUES
    (1, 1, 13, 150, 1850);


-- 5. CONSULTAS ÚTILES (REFERENCIA)

-- Estas consultas NO se ejecutan aquí, son EJEMPLOS de referencia
-- para usar desde PHP o desde phpMyAdmin.


-- Ver todos los usuarios:
-- SELECT * FROM usuarios;

-- Ver el ranking ordenado por puntuación (de mayor a menor):
-- SELECT u.nombre, p.fichas_restantes, p.movimientos, p.tiempo_segundos, p.puntuacion
-- FROM puntuaciones p
-- JOIN usuarios u ON p.usuario_id = u.id
-- ORDER BY p.puntuacion DESC
-- LIMIT 10;

-- Contar cuántos usuarios hay (función de agregado COUNT):
-- SELECT COUNT(*) AS total_usuarios FROM usuarios;

-- Ver la puntuación máxima (función de agregado MAX):
-- SELECT MAX(puntuacion) AS mejor_puntuacion FROM puntuaciones;

-- Ver puntuaciones de un usuario concreto (WHERE con condición):
-- SELECT * FROM puntuaciones WHERE usuario_id = 1;