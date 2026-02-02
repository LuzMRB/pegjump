# Solitario Triangular — Proyecto Full Stack

**Ingeniería Web I — UAX**  
**Autora:** Luz Rubio Bolger  

## Descripción

Juego de Solitario Triangular desarrollado como proyecto Full Stack para la asignatura de Ingeniería Web I. El objetivo es eliminar fichas saltando sobre ellas hasta dejar solo una en el tablero.

## Estructura del Proyecto

```
solitario/
├── index.php              ← Página principal (PHP + HTML)
├── index.html             ← Versión estática (solo HTML, sin BD)
├── css/
│   └── style.css          ← Estilos (UD2: CSS, Flexbox, Grid, Media Queries)
├── js/
│   └── script.js          ← Lógica del juego + fetch a PHP (UD3: JavaScript + DOM)
├── php/
│   ├── config.php         ← Configuración de la base de datos (UD4: mysqli_connect)
│   ├── process.php        ← Procesamiento del formulario (UD4/UD5: INSERT, prepared statements)
│   ├── get_ranking.php    ← Obtener ranking (UD4: SELECT con JOIN)
│   └── test_conexion.php  ← Script de prueba de conexión a la BD
├── sql/
│   └── database.sql       ← Script SQL para crear la BD y tablas (UD4: CREATE TABLE)
└── README.md              ← Este archivo
```

## Tecnologías Utilizadas

| Tecnología | Uso | Unidad |
|---|---|---|
| HTML5 | Estructura semántica | UD1 |
| CSS3 | Estilos, Flexbox, Grid, Responsive | UD2 |
| JavaScript | Lógica del juego, DOM, Eventos, Fetch | UD3 |
| PHP | Backend, procesamiento de formularios | UD4/UD5 |
| MySQL | Base de datos relacional | UD4 |

## Instalación y Configuración (Clase 7)

### Requisitos Previos
- **XAMPP** (o WAMP/MAMP) instalado y ejecutándose
- Apache y MySQL activos en el panel de control de XAMPP

### Paso 1: Copiar el proyecto
Copiar la carpeta del proyecto dentro de la carpeta `htdocs` de XAMPP:
```
C:\xampp\htdocs\solitario\    (Windows)
/opt/lampp/htdocs/solitario/  (Linux)
/Applications/XAMPP/htdocs/solitario/  (Mac)
```

### Paso 2: Crear la Base de Datos
1. Abrir **phpMyAdmin**: `http://localhost/phpmyadmin/`
2. Ir a la pestaña **SQL**
3. Copiar y pegar el contenido de `sql/database.sql`
4. Pulsar **Ejecutar**

Esto creará:
- Base de datos: `solitario_db`
- Tabla `usuarios`: almacena los jugadores
- Tabla `puntuaciones`: almacena las puntuaciones de cada partida
- Datos de ejemplo para verificar que funciona

### Paso 3: Verificar la Conexión
Abrir en el navegador:
```
http://localhost/solitario/php/test_conexion.php
```
Deben aparecer todos los tests en verde (✅).

### Paso 4: Acceder al juego
```
http://localhost/solitario/index.php
```

## Conceptos UD4/UD5 Aplicados

### PHP Básico
- **Variables** (`$nombre`, `$conexion`): empiezan con `$`, tipado débil
- **Funciones** (`calcularPuntuacion()`): definición y llamada
- **Estructuras de control**: `if/elseif/else`, `foreach`, `while`
- **Operadores**: aritméticos, comparación, concatenación (`.`)

### Variables Superglobales
- `$_POST`: datos enviados por formulario con método POST
- `$_SERVER`: información del servidor (`REQUEST_METHOD`)
- `php://input`: cuerpo de la petición HTTP (para JSON)

### Conexión a Base de Datos (mysqli)
- `mysqli_connect()`: establece la conexión
- `mysqli_query()`: ejecuta consultas SQL
- `mysqli_fetch_assoc()`: obtiene filas como arrays asociativos
- `mysqli_prepare()`: prepara consultas (seguridad)
- `mysqli_stmt_bind_param()`: vincula parámetros
- `mysqli_stmt_execute()`: ejecuta consultas preparadas
- `mysqli_close()`: cierra la conexión

### Seguridad (UD5 §3.6)
- **Consultas preparadas**: previenen inyección SQL
- **password_hash()**: hasheo seguro de contraseñas (bcrypt)
- **password_verify()**: verificación de contraseñas
- **htmlspecialchars()**: previene ataques XSS
- **Validación doble**: tanto en cliente (JS) como en servidor (PHP)

### SQL (UD4 §6.1)
- `CREATE DATABASE/TABLE`: creación de estructura
- `INSERT INTO`: inserción de datos
- `SELECT ... JOIN ... ORDER BY`: consultas con relaciones
- `FOREIGN KEY`: claves foráneas entre tablas

## Base de Datos

### Tabla `usuarios`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| nombre | VARCHAR(50) | Nombre del jugador |
| email | VARCHAR(100) | Email (opcional) |
| password | VARCHAR(255) | Contraseña hasheada |
| fecha_registro | TIMESTAMP | Fecha de registro |

### Tabla `puntuaciones`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT (PK, AUTO_INCREMENT) | Identificador único |
| usuario_id | INT (FK → usuarios.id) | Referencia al usuario |
| fichas_restantes | INT | Fichas al terminar |
| movimientos | INT | Movimientos realizados |
| tiempo_segundos | INT | Duración en segundos |
| puntuacion | INT | Puntuación calculada |
| fecha | TIMESTAMP | Fecha de la partida |
