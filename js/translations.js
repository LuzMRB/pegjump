// TRANSLATIONS — Bilingual strings for Peg Jump (ES / EN)

const TRANSLATIONS = {
  es: {
    subtitle: "¿Podrás resolver este puzle?",
    sectionTitle: "Empieza a resolver",
    sectionInstructions: "Haz clic en una ficha y luego en un hueco para saltar y eliminar fichas.",
    statsPegs: "Fichas restantes:",
    statsMoves: "Movimientos:",
    statsTime: "Tiempo:",
    ariaUndo: "Deshacer último movimiento",
    ariaReset: "Reiniciar partida",
    ariaHint: "Pista",
    footerProject: "Proyecto de Ingeniería Web I",
    metaDescription: "Peg Jump - Juego clásico de fichas - Proyecto Full Stack para Ingeniería Web I",
    alertsVictory: "¡VICTORIA! Has dejado solo 1 ficha en {moves} movimientos y {time}.",
    alertsGameOver: "Fin de la partida. Quedan {pegs} fichas. ¡Inténtalo de nuevo!"
  },
  en: {
    subtitle: "Can you solve this puzzle?",
    sectionTitle: "Start solving",
    sectionInstructions: "Click on a peg and then on an empty hole to jump and remove pegs.",
    statsPegs: "Pegs remaining:",
    statsMoves: "Moves:",
    statsTime: "Time:",
    ariaUndo: "Undo last move",
    ariaReset: "Restart game",
    ariaHint: "Hint",
    footerProject: "Web Engineering I Project",
    metaDescription: "Peg Jump - Classic peg board game - Full Stack project for Web Engineering I",
    alertsVictory: "VICTORY! You left only 1 peg in {moves} moves and {time}.",
    alertsGameOver: "Game over. {pegs} pegs left. Try again!"
  }
};

if (typeof window !== 'undefined') {
  window.TRANSLATIONS = TRANSLATIONS;
}
