#  Solitario Triangular | Peg Solitaire

**Proyecto Full Stack — Ingeniería Web I**  
Universidad Alfonso X el Sabio (UAX)

**Autora:** Luz Rubio Bolger  
**Fecha:** Enero 2025

---

##  Objetivo del Proyecto

Desarrollar una **aplicación web completa** (Full Stack) que implemente el clásico juego de mesa **Solitario Triangular** (también conocido como *Peg Solitaire*).

El juego consiste en un tablero triangular con **15 posiciones** dispuestas en 5 filas. Al inicio, todas las posiciones están ocupadas por fichas excepto una. El jugador debe ir **saltando fichas** sobre otras adyacentes para eliminarlas del tablero, con el objetivo de dejar **una sola ficha** al final de la partida.


---

## Funcionalidades

### Juego

- **Tablero interactivo** de 15 posiciones con forma triangular.
- **Selección y movimiento** de fichas mediante clic o toque en pantalla.
- **Validación de movimientos**: solo se permiten saltos sobre fichas adyacentes hacia un hueco vacío.
- **Sistema de deshacer** (undo) para revertir el último movimiento.
- **Pistas**: el juego sugiere un movimiento válido cuando el jugador se atasca.
- **Detección automática** de fin de partida (victoria o derrota).
- **Selección de hueco inicial**: el jugador puede elegir desde qué posición empezar.

### Estadísticas y Puntuación

- **Contador de fichas restantes** actualizado en tiempo real.
- **Contador de movimientos** realizados.
- **Cronómetro** que mide el tiempo de cada partida.
- **Sistema de puntuación** que combina fichas eliminadas, movimientos, tiempo y bonificaciones especiales.

### Ranking y Persistencia

- **Formulario de registro** de puntuación con validación de campos (nombre, email).
- **Almacenamiento en base de datos MySQL** de las puntuaciones.
- **Tabla de ranking** con los mejores jugadores, cargada de forma asíncrona.
- **API REST** en PHP para la comunicación entre el frontend y la base de datos.

### Experiencia de Usuario

- **Diseño responsivo** adaptado a escritorio, tablet y móvil.
- **Animaciones CSS** para mejorar la retroalimentación visual (selección, captura, victoria).
- **Modal de resultados** al finalizar cada partida.
- **Navegación fluida** con menú hamburguesa en dispositivos móviles.
- **Accesibilidad**: atributos ARIA, navegación por teclado y soporte para movimiento reducido.

---

## Tecnologías Utilizadas

| Unidad Didáctica | Tecnologías | Uso en el Proyecto |
|---|---|---|
| **UD2** — HTML y CSS | HTML5, CSS3 | Estructura semántica del sitio (`header`, `nav`, `main`, `section`, `footer`). Estilos con CSS Custom Properties, Flexbox, Grid y animaciones con `@keyframes`. |
| **UD3** — JavaScript y DOM | JavaScript ES6+ | Lógica del juego implementada con clases. Manipulación dinámica del DOM, manejo de eventos (`click`, `submit`, `keydown`) y validación de formularios. |
| **UD4** — PHP y MySQL | PHP 8, MySQL, SQL | Backend con PHP para procesar solicitudes. Base de datos MySQL para almacenar puntuaciones. Consultas con `prepared statements` para prevenir SQL Injection. |
| **UD5** — PHP Orientado a Objetos | PHP (POO) | Clase `Puntuacion` con encapsulación (propiedades `private`, getters/setters con validación). Clase `Database` con patrón Singleton. Clase `APIController` para gestionar la API. |
| **UD6** — AJAX y Diseño Responsivo | Fetch API, JSON, CSS Media Queries | Comunicación asíncrona con el servidor mediante `fetch()` y `async/await`. Intercambio de datos en formato JSON. Diseño adaptable con media queries en 3 breakpoints (1024px, 768px, 480px). |

---

##  Seguridad

- **SQL Injection**: prevenido mediante `prepared statements` (`mysqli_prepare` + `bind_param`).
- **XSS (Cross-Site Scripting)**: prevenido con `htmlspecialchars()` en PHP y escapado de HTML en JavaScript.
- **Validación doble**: tanto en el cliente (JavaScript) como en el servidor (PHP) para garantizar la integridad de los datos.

---

##  Estructura del Proyecto

```
solitario_triangular/
├── index.html              ← Página principal (HTML5 semántico)
├── css/
│   └── style.css           ← Estilos, responsive design y animaciones
├── js/
│   └── script.js           ← Lógica del juego, DOM, eventos, Fetch API
├── php/
│   ├── config.php          ← Configuración de BD (Singleton)
│   ├── Puntuacion.php      ← Clase POO para gestionar puntuaciones
│   └── api.php             ← API REST (endpoints JSON)
├── sql/
│   └── database.sql        ← Script de creación de la base de datos
└── README.md               ← Este archivo
```

---

