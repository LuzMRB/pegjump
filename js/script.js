// 
// SCRIPT.JS — Solitario Triangular
// Aquí implementamos la lógica del juego y la interactividad
// 

'use strict'; // Activa el modo estricto de JavaScript 

//  VARIABLES DEL ESTADO DEL JUEGO 
// let: variables que cambian durante la partida
// const: valores que no cambian nunca

// Array de 15 posiciones: true = tiene ficha, false = vacío
// Posición 0 empieza vacía (el hueco inicial)
let tablero = [
    false,          // Posición 0 (vacía al inicio)
    true, true,     // Posiciones 1-2
    true, true, true,           // Posiciones 3-5
    true, true, true, true,     // Posiciones 6-9
    true, true, true, true, true // Posiciones 10-14
];

let fichaSeleccionada = null;  // Posición de la ficha seleccionada (null = ninguna)
let movimientos = 0;            // Contador de movimientos realizados
let fichasRestantes = 14;       // Empieza con 14 fichas (15 - 1 hueco)
let tiempoSegundos = 0;         // Segundos transcurridos
let temporizador = null;        // Referencia al setInterval del timer
let juegoActivo = false;        // ¿El juego ha empezado?
let historialMovimientos = [];  // Array como pila para deshacer (push/pop)

let onboardingActive = false;
let onboardingPegIndex = null;
let onboardingHoleIndex = null;
let onboardingCompletedThisSession = false;

//  MAPA DE CONEXIONES 
// Objeto: cada posición tiene un array de movimientos posibles
// Cada movimiento es [posición_intermedia, posición_destino]
// Ejemplo: desde la posición 0, puedes saltar sobre la 1 y caer en la 3
const CONEXIONES = {
    0: [[1, 3], [2, 5]],
    1: [[0, 2], [3, 6], [4, 8]],  // añadido salto diagonal
    2: [[0, 1], [4, 7], [5, 9]],  // añadido salto diagonal
    3: [[1, 0], [4, 5], [6, 10], [7, 12]],
    4: [[7, 11], [8, 13]],
    5: [[2, 0], [4, 3], [8, 12], [9, 14]],
    6: [[1, 0], [3, 1], [7, 8], [11, 13]],  // corregido
    7: [[4, 2], [8, 9]],
    8: [[4, 1], [7, 6]],
    9: [[5, 2], [8, 7]],
    10: [[6, 3], [11, 12]],
    11: [[7, 4], [12, 13]],
    12: [[7, 3], [8, 5], [11, 10], [13, 14]],
    13: [[8, 4], [12, 11]],
    14: [[9, 5], [13, 12]]
};

function getFirstValidMove() {
    const initialBoard = [false, true, true, true, true, true, true, true, true, true, true, true, true, true, true];
    for (let i = 0; i < 15; i++) {
        if (!initialBoard[i]) continue;
        const conexiones = CONEXIONES[i];
        if (!conexiones) continue;
        for (const [intermedia, destino] of conexiones) {
            if (initialBoard[intermedia] && !initialBoard[destino]) {
                return { pegIndex: i, holeIndex: destino };
            }
        }
    }
    return null;
}

function startOnboarding() {
    if (onboardingCompletedThisSession) return;
    const move = getFirstValidMove();
    if (!move) return;

    const tooltip = document.getElementById('onboarding-tooltip');
    if (!tooltip || !elementosTablero[move.pegIndex] || !elementosTablero[move.holeIndex]) return;

    onboardingActive = true;
    onboardingPegIndex = move.pegIndex;
    onboardingHoleIndex = move.holeIndex;

    elementosTablero[move.pegIndex].classList.add('onboarding-peg');
    elementosTablero[move.holeIndex].classList.add('onboarding-hole');

    const lang = window.i18n && window.i18n.getLang ? window.i18n.getLang() : 'es';
    const t = window.TRANSLATIONS && window.TRANSLATIONS[lang];
    const textEl = tooltip.querySelector('.onboarding-tooltip-text');
    const startText = t && t.onboardingTooltipStart ? t.onboardingTooltipStart : 'Mueve esta ficha al hueco vacío para empezar';
    if (textEl) {
        textEl.textContent = startText;
        tooltip.setAttribute('data-onboarding-phase', 'start');
    }
    tooltip.classList.add('is-visible');
}

function endOnboardingPhase1() {
    if (!onboardingActive || onboardingPegIndex === null || onboardingHoleIndex === null) return;

    elementosTablero[onboardingPegIndex].classList.remove('onboarding-peg');
    elementosTablero[onboardingHoleIndex].classList.remove('onboarding-hole');
    onboardingPegIndex = null;
    onboardingHoleIndex = null;

    const tooltip = document.getElementById('onboarding-tooltip');
    if (!tooltip) return;

    const textEl = tooltip.querySelector('.onboarding-tooltip-text');
    const lang = window.i18n && window.i18n.getLang ? window.i18n.getLang() : 'es';
    const t = window.TRANSLATIONS && window.TRANSLATIONS[lang];
    const goalText = t && t.onboardingTooltipGoal ? t.onboardingTooltipGoal : 'Deja solo una ficha en el tablero';

    // Crossfade: fade out text, change content, fade in
    if (textEl) {
        textEl.classList.add('is-crossfade');
        tooltip.setAttribute('data-onboarding-phase', 'goal');
        setTimeout(() => {
            textEl.textContent = goalText;
            textEl.classList.remove('is-crossfade');
        }, 250);
    }

    setTimeout(() => {
        tooltip.classList.add('is-fadeout');
        setTimeout(() => {
            tooltip.classList.remove('is-visible', 'is-fadeout');
            if (textEl) textEl.textContent = '';
            onboardingCompletedThisSession = true;
            onboardingActive = false;
        }, 400);
    }, 3000);
}

// SELECCIÓN DE ELEMENTOS DEL DOM 
// document.getElementById(): selecciona un elemento por su ID
// document.querySelectorAll(): selecciona varios elementos por selector CSS

const elementosTablero = document.querySelectorAll('.posicion');
const spanFichas = document.getElementById('fichas-restantes');
const spanMovimientos = document.getElementById('movimientos');
const spanTiempo = document.getElementById('tiempo');
const btnReiniciar = document.getElementById('btn-reiniciar');
const btnDeshacer = document.getElementById('btn-deshacer');
const btnPista = document.getElementById('btn-pista');

//  FUNCIÓN: INICIALIZAR JUEGO 
// Resetea todo el estado y actualiza el DOM
function inicializarJuego() {
    // Resetear variables
    tablero = [
        false,
        true, true,
        true, true, true,
        true, true, true, true,
        true, true, true, true, true
    ];
    fichaSeleccionada = null;
    movimientos = 0;
    fichasRestantes = 14;
    tiempoSegundos = 0;
    juegoActivo = false;
    historialMovimientos = [];

    // Detener el temporizador si estaba corriendo
    if (temporizador !== null) {
        clearInterval(temporizador);
        temporizador = null;
    }

    // Actualizar el DOM: recorrer cada posición del tablero
    elementosTablero.forEach((elemento, indice) => {
        // Limpiar todas las clases extra (incl. onboarding)
        elemento.classList.remove('ficha', 'vacia', 'seleccionada', 'onboarding-peg', 'onboarding-hole');

        // Añadir la clase correcta según el estado
        if (tablero[indice]) {
            elemento.classList.add('ficha');    // Tiene ficha
        } else {
            elemento.classList.add('vacia');    // Está vacía
        }
    });

    // Actualizar los textos de las estadísticas
    actualizarEstadisticas();

    // Onboarding: ocultar tooltip al reiniciar y mostrar si aplica
    const tooltip = document.getElementById('onboarding-tooltip');
    if (tooltip) {
        tooltip.classList.remove('is-visible', 'is-fadeout', 'is-hiding');
        tooltip.removeAttribute('data-onboarding-phase');
        const textEl = tooltip.querySelector('.onboarding-tooltip-text');
        if (textEl) {
            textEl.textContent = '';
            textEl.classList.remove('is-crossfade');
        }
    }
    console.log('Juego inicializado');
}

//  FUNCIÓN: ACTUALIZAR ESTADÍSTICAS EN PANTALLA 
// Modifica el contenido de los <span> del DOM con textContent
function actualizarEstadisticas() {
    spanFichas.textContent = fichasRestantes;
    spanMovimientos.textContent = movimientos;
    spanTiempo.textContent = formatearTiempo(tiempoSegundos);
}

//  FUNCIÓN: FORMATEAR TIEMPO 
// 
function formatearTiempo(segundos) {
    const min = Math.floor(segundos / 60);  // Parte entera de la división
    const seg = segundos % 60;               // Resto de la división
    // padStart(2, '0') → añade un 0 delante si tiene menos de 2 dígitos
    return String(min).padStart(2, '0') + ':' + String(seg).padStart(2, '0');
}

//  FUNCIÓN: OBTENER MOVIMIENTOS VÁLIDOS DESDE UNA POSICIÓN 
// Devuelve un array con los movimientos posibles [intermedia, destino]
function obtenerMovimientosValidos(posicion) {
    const movimientosValidos = [];

    // Si la posición no tiene ficha, no hay movimientos
    if (!tablero[posicion]) {
        return movimientosValidos;
    }

    // Obtener las conexiones de esta posición
    const conexiones = CONEXIONES[posicion];

    // Si no hay conexiones definidas, devolver vacío
    if (!conexiones) {
        return movimientosValidos;
    }

    // Recorrer cada posible movimiento con for-of (UD3: bucle for-of)
    for (const movimiento of conexiones) {
        const intermedia = movimiento[0];  // Ficha que salta por encima
        const destino = movimiento[1];      // Donde aterriza

        // Condiciones para que el movimiento sea válido:
        // 1. La posición intermedia tiene ficha (para saltar sobre ella)
        // 2. La posición destino está vacía (para aterrizar)
        if (tablero[intermedia] && !tablero[destino]) {
            movimientosValidos.push(movimiento);
        }
    }

    return movimientosValidos;
}

//  FUNCIÓN: MANEJAR CLIC EN UNA POSICIÓN DEL TABLERO 
function manejarClic(posicion) {
    // Si no hay ficha seleccionada todavía...
    if (fichaSeleccionada === null) {
        // Solo se puede seleccionar una posición con ficha
        if (tablero[posicion]) {
            // Verificar que tiene al menos un movimiento válido
            const validos = obtenerMovimientosValidos(posicion);
            if (validos.length > 0) {
                fichaSeleccionada = posicion;
                elementosTablero[posicion].classList.add('seleccionada');

                // Iniciar temporizador en el primer clic
                if (!juegoActivo) {
                    juegoActivo = true;
                    iniciarTemporizador();
                }
            }
        }
    } else {
        // Ya hay una ficha seleccionada...

        // Si hace clic en la misma ficha → deseleccionar
        if (posicion === fichaSeleccionada) {
            elementosTablero[posicion].classList.remove('seleccionada');
            fichaSeleccionada = null;
            return; // Salir de la función
        }

        // Si hace clic en otra ficha → cambiar selección
        if (tablero[posicion]) {
            elementosTablero[fichaSeleccionada].classList.remove('seleccionada');
            fichaSeleccionada = posicion;
            elementosTablero[posicion].classList.add('seleccionada');
            return;
        }

        // Si hace clic en un hueco vacío → intentar mover
        if (!tablero[posicion]) {
            ejecutarMovimiento(fichaSeleccionada, posicion);
        }
    }
}

//  FUNCIÓN: EJECUTAR UN MOVIMIENTO 
function ejecutarMovimiento(origen, destino) {
    const validos = obtenerMovimientosValidos(origen);

    // Buscar si el destino está entre los movimientos válidos
    let movimientoEncontrado = null;
    for (const mov of validos) {
        if (mov[1] === destino) {
            movimientoEncontrado = mov;
            break; // Salir del bucle al encontrarlo
        }
    }

    // Si no se encontró un movimiento válido, no hacer nada
    if (movimientoEncontrado === null) {
        return;
    }

    const intermedia = movimientoEncontrado[0];

    // Guardar en historial para poder deshacer
    //  (array como pila)
    historialMovimientos.push({
        origen: origen,
        intermedia: intermedia,
        destino: destino
    });

    // Actualizar el array del tablero
    tablero[origen] = false;       // La ficha se va del origen
    tablero[intermedia] = false;   // La ficha saltada se elimina
    tablero[destino] = true;       // La ficha aterriza en el destino

    // Actualizar el DOM
    elementosTablero[origen].classList.remove('ficha', 'seleccionada');
    elementosTablero[origen].classList.add('vacia');
    elementosTablero[intermedia].classList.remove('ficha');
    elementosTablero[intermedia].classList.add('vacia');
    elementosTablero[destino].classList.remove('vacia');
    elementosTablero[destino].classList.add('ficha');

    // Actualizar contadores
    fichaSeleccionada = null;
    movimientos++;
    fichasRestantes--;
    actualizarEstadisticas();

    // Verificar si el juego terminó
    verificarFinJuego();
}

//  FUNCIÓN: VERIFICAR FIN DEL JUEGO
function verificarFinJuego() {
    // Comprobar si queda algún movimiento posible
    let hayMovimientos = false;

    // Bucle for clásico (UD3: bucle for)
    for (let i = 0; i < 15; i++) {
        if (tablero[i]) {
            const validos = obtenerMovimientosValidos(i);
            if (validos.length > 0) {
                hayMovimientos = true;
                break; // No hace falta seguir buscando
            }
        }
    }

    if (!hayMovimientos) {
        // Detener el temporizador
        clearInterval(temporizador);
        temporizador = null;
        juegoActivo = false;

        // Mostrar mensaje según resultado (traducido según idioma actual)
        if (fichasRestantes === 1) {
            const msg = window.i18n && window.i18n.getTranslatedAlert
                ? window.i18n.getTranslatedAlert('alertsVictory', { moves: movimientos, time: formatearTiempo(tiempoSegundos) })
                : '¡VICTORIA! Has dejado solo 1 ficha en ' + movimientos + ' movimientos y ' + formatearTiempo(tiempoSegundos) + '.';
            alert(msg);
        } else {
            const msg = window.i18n && window.i18n.getTranslatedAlert
                ? window.i18n.getTranslatedAlert('alertsGameOver', { pegs: fichasRestantes })
                : 'Fin de la partida. Quedan ' + fichasRestantes + ' fichas. ¡Inténtalo de nuevo!';
            alert(msg);
        }
    }
}

// FUNCIÓN: DESHACER ÚLTIMO MOVIMIENTO 
function deshacer() {
    // pop() saca el último elemento del array (UD3: arrays)
    if (historialMovimientos.length === 0) {
        return; // No hay nada que deshacer
    }

    const ultimo = historialMovimientos.pop();

    // Revertir el movimiento
    tablero[ultimo.origen] = true;
    tablero[ultimo.intermedia] = true;
    tablero[ultimo.destino] = false;

    // Actualizar el DOM
    elementosTablero[ultimo.origen].classList.remove('vacia');
    elementosTablero[ultimo.origen].classList.add('ficha');
    elementosTablero[ultimo.intermedia].classList.remove('vacia');
    elementosTablero[ultimo.intermedia].classList.add('ficha');
    elementosTablero[ultimo.destino].classList.remove('ficha');
    elementosTablero[ultimo.destino].classList.add('vacia');

    movimientos--;
    fichasRestantes++;
    actualizarEstadisticas();
}

//  FUNCIÓN: MOSTRAR PISTA 
function mostrarPista() {
    // Buscar el primer movimiento válido que exista
    for (let i = 0; i < 15; i++) {
        if (tablero[i]) {
            const validos = obtenerMovimientosValidos(i);
            if (validos.length > 0) {
                // Resaltar la ficha que puede moverse
                elementosTablero[i].classList.add('seleccionada');
                // Quitar el resaltado después de 1 segundo
                setTimeout(() => {
                    elementosTablero[i].classList.remove('seleccionada');
                }, 1000);
                return; // Solo mostrar una pista
            }
        }
    }
}

//  FUNCIÓN: INICIAR TEMPORIZADOR 
// setInterval ejecuta una función cada X milisegundos
function iniciarTemporizador() {
    temporizador = setInterval(() => {
        tiempoSegundos++;
        spanTiempo.textContent = formatearTiempo(tiempoSegundos);
    }, 1000); // 1000 ms = 1 segundo
}


// EVENT LISTENERS: Conectar HTML con JavaScript


// Clic en cada posición del tablero (solo en modo juego)
elementosTablero.forEach((elemento, indice) => {
    elemento.addEventListener('click', () => {
        if (modoActual === 'game') manejarClic(indice);
    });
});

// Clic en los botones de control
btnReiniciar.addEventListener('click', inicializarJuego);
btnDeshacer.addEventListener('click', deshacer);
btnPista.addEventListener('click', mostrarPista);


/* ===================== TUTORIAL ===================== */

let modoActual = 'tutorial';
let tutorialPaso = 0;

const TUTORIAL_TEXTOS = {
    es: {
        tutorial_title: "Cómo jugar",
        tutorial_step_0: "Una ficha salta sobre otra",
        tutorial_step_1: "Tu turno — toca la ficha",
        tutorial_step_2: "La ficha saltada desaparece",
        tutorial_step_3: "Objetivo: deja solo una ficha",
        tutorial_play: "Jugar",
        tutorial_skip: "Saltar",
        tutorial_repeat: "Repetir tutorial"
    },
    en: {
        tutorial_title: "How to play",
        tutorial_step_0: "A peg jumps over another",
        tutorial_step_1: "Your turn — tap the peg",
        tutorial_step_2: "The jumped peg disappears",
        tutorial_step_3: "Goal: leave only one peg",
        tutorial_play: "Play",
        tutorial_skip: "Skip",
        tutorial_repeat: "Replay tutorial"
    }
};

function obtenerIdiomaActual() {
    if (window.i18n && typeof window.i18n.getLang === 'function') return window.i18n.getLang();
    const btnEN = document.querySelector('[data-lang="en"]');
    if (btnEN && btnEN.classList.contains('active')) return 'en';
    if (document.documentElement.lang === 'en') return 'en';
    const stored = localStorage.getItem('pegjump-lang');
    if (stored === 'es' || stored === 'en') return stored;
    return 'es';
}

function textoTutorial(clave) {
    const lang = obtenerIdiomaActual();
    return TUTORIAL_TEXTOS[lang]?.[clave] || TUTORIAL_TEXTOS['es'][clave] || clave;
}

function actualizarTextoTutorial(paso) {
    const el = document.querySelector('.tutorial-text');
    if (!el) return;
    el.classList.add('fade-out');
    setTimeout(() => {
        el.textContent = textoTutorial('tutorial_step_' + paso);
        el.classList.remove('fade-out');
    }, 400);
}

function actualizarTodoTextosTutorial() {
    const titulo = document.querySelector('.tutorial-title');
    if (titulo) titulo.textContent = textoTutorial('tutorial_title');
    const btnPlay = document.querySelector('.tutorial-btn-play');
    if (btnPlay) btnPlay.textContent = textoTutorial('tutorial_play');
    const btnSkip = document.querySelector('.tutorial-btn-skip');
    if (btnSkip) btnSkip.textContent = textoTutorial('tutorial_skip');
    const btnRepeat = document.querySelector('.tutorial-btn-repeat');
    if (btnRepeat) btnRepeat.textContent = textoTutorial('tutorial_repeat');
}

function actualizarDots(paso) {
    document.querySelectorAll('.tutorial-dot').forEach(dot => {
        dot.classList.toggle('active', parseInt(dot.dataset.step) === paso);
    });
}

const TUTORIAL_KEY = 'pegjump_tutorial_completado';

function tutorialCompletado() {
    return localStorage.getItem(TUTORIAL_KEY) === 'true';
}

function marcarTutorialCompletado() {
    localStorage.setItem(TUTORIAL_KEY, 'true');
}

function resetearTutorial() {
    localStorage.removeItem(TUTORIAL_KEY);
}

window.actualizarTodoTextosTutorial = actualizarTodoTextosTutorial;
window.actualizarTextoTutorial = actualizarTextoTutorial;
window.getTutorialPaso = () => tutorialPaso;

function renderTutorialBoard(estado) {
    const posiciones = document.querySelectorAll('.tutorial-pos');
    posiciones.forEach((pos, i) => {
        pos.className = 'tutorial-pos';
        if (estado[i] === 1) {
            pos.classList.add('tutorial-ficha');
        } else {
            pos.classList.add('tutorial-hueco-destino');
        }
    });
}

function animarSaltoTutorial(callback) {
    const posiciones = document.querySelectorAll('.tutorial-pos');
    const fichaA = posiciones[0];
    const fichaB = posiciones[1];
    const huecoC = posiciones[2];

    const rectA = fichaA.getBoundingClientRect();
    const rectC = huecoC.getBoundingClientRect();
    const distX = rectC.left - rectA.left;

    fichaA.classList.add('tutorial-ficha-mover');
    fichaA.style.transform = `translateX(${distX}px)`;

    setTimeout(() => {
        fichaB.classList.add('tutorial-ficha-desaparecer');
    }, 500);

    setTimeout(() => {
        fichaA.style.transform = '';
        fichaA.classList.remove('tutorial-ficha-mover');
        renderTutorialBoard([0, 0, 1]);
        if (callback) callback();
    }, 1400);
}

function aplicarClasesPaso(paso) {
    const posiciones = document.querySelectorAll('.tutorial-pos');
    posiciones.forEach(pos => {
        pos.classList.remove('tutorial-ficha-origen', 'tutorial-ficha-intermedia', 'tutorial-hueco-destino');
    });

    if (paso === 0 || paso === 1) {
        renderTutorialBoard([1, 1, 0]);
        posiciones[0].classList.add('tutorial-ficha-origen');
        posiciones[1].classList.add('tutorial-ficha-intermedia');
        posiciones[2].classList.add('tutorial-hueco-destino');
    }
}

function iniciarTutorial() {
    modoActual = 'tutorial';
    tutorialPaso = 0;
    document.body.classList.remove('modo-game');
    document.body.classList.add('modo-tutorial');
    actualizarTodoTextosTutorial();
    ejecutarPasoTutorial(0);
}

function ejecutarPasoTutorial(paso) {
    tutorialPaso = paso;
    actualizarDots(paso);

    const btnPlay = document.querySelector('.tutorial-btn-play');
    const btnSkip = document.querySelector('.tutorial-btn-skip');
    const btnRepeat = document.querySelector('.tutorial-btn-repeat');

    switch (paso) {
        case 0:
            aplicarClasesPaso(0);
            actualizarTextoTutorial(0);
            if (btnPlay) btnPlay.style.display = 'none';
            if (btnSkip) btnSkip.style.display = '';
            if (btnRepeat) btnRepeat.style.display = 'none';
            setTimeout(() => {
                animarSaltoTutorial(() => {
                    setTimeout(() => ejecutarPasoTutorial(1), 1500);
                });
            }, 1500);
            break;

        case 1:
            aplicarClasesPaso(1);
            actualizarTextoTutorial(1);
            if (btnPlay) btnPlay.style.display = 'none';
            if (btnSkip) btnSkip.style.display = '';
            if (btnRepeat) btnRepeat.style.display = 'none';
            activarClicsTutorial();
            break;

        case 2:
            actualizarTextoTutorial(2);
            actualizarDots(2);
            if (btnPlay) btnPlay.style.display = 'none';
            if (btnSkip) btnSkip.style.display = '';
            if (btnRepeat) btnRepeat.style.display = 'none';
            setTimeout(() => ejecutarPasoTutorial(3), 2000);
            break;

        case 3:
            actualizarTextoTutorial(3);
            actualizarDots(3);
            if (btnPlay) btnPlay.style.display = '';
            if (btnSkip) btnSkip.style.display = 'none';
            if (btnRepeat) btnRepeat.style.display = '';
            break;
    }
}

function activarClicsTutorial() {
    const posiciones = document.querySelectorAll('.tutorial-pos');
    const handler = (e) => {
        const tpos = parseInt(e.currentTarget.dataset.tpos);
        if (tpos === 0) {
            posiciones.forEach(p => p.removeEventListener('click', handler));
            animarSaltoTutorial(() => {
                ejecutarPasoTutorial(2);
            });
        }
    };
    posiciones.forEach(pos => {
        pos.addEventListener('click', handler);
    });
}

function finalizarTutorial() {
    marcarTutorialCompletado();
    modoActual = 'game';
    document.body.classList.remove('modo-tutorial');
    document.body.classList.add('modo-game');
    inicializarJuego();
}

// Tutorial: botones
const btnTutorialPlay = document.querySelector('.tutorial-btn-play');
const btnTutorialSkip = document.querySelector('.tutorial-btn-skip');
const btnTutorialRepeat = document.querySelector('.tutorial-btn-repeat');

if (btnTutorialPlay) btnTutorialPlay.addEventListener('click', finalizarTutorial);
if (btnTutorialSkip) btnTutorialSkip.addEventListener('click', finalizarTutorial);
if (btnTutorialRepeat) btnTutorialRepeat.addEventListener('click', () => {
    resetearTutorial();
    iniciarTutorial();
});

// Cambio de idioma: actualizar textos del tutorial
document.querySelectorAll('[data-lang]').forEach(btn => {
    btn.addEventListener('click', () => {
        setTimeout(() => {
            if (document.body.classList.contains('modo-tutorial')) {
                actualizarTodoTextosTutorial();
                actualizarTextoTutorial(tutorialPaso);
            }
        }, 100);
    });
});

// INICIALIZACIÓN — Siempre mostrar tutorial al cargar
iniciarTutorial();

console.log('Peg Jump — JS cargado correctamente');

