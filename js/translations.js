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
    alertsGameOver: "Fin de la partida. Quedan {pegs} fichas. ¡Inténtalo de nuevo!",
    onboardingTooltipStart: "Mueve esta ficha al hueco vacío para empezar",
    onboardingTooltipGoal: "Deja solo una ficha en el tablero",
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
    alertsGameOver: "Game over. {pegs} pegs left. Try again!",
    onboardingTooltipStart: "Move this peg to the empty hole to start",
    onboardingTooltipGoal: "Leave only one peg on the board",
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

if (typeof window !== 'undefined') {
  window.TRANSLATIONS = TRANSLATIONS;
}
