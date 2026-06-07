<?php
// PRESENTACIO_ES.PHP - Versión Coherente Mundial 2026

if ($usuario_autenticado) {
    echo "<div class='bienvenida_contenedor'>"; // Esta clase ahora tiene el margen izquierdo asignado
    echo "<h3>" . htmlspecialchars(($trad["bienvenido "] ?? "Bienvenido/a, ") . $nom_usuari) . "</h3>";
    echo "<p>Ya has iniciado sesión. Puedes acceder a las porras desde el menú lateral.</p>";
    echo "</div>";
} else {
    echo "<div class='presentacion_texto'>"; // Esta clase ahora tiene el margen izquierdo asignado
    echo "<h3>Bienvenido al Juego del Mundial 2026</h3>";
    echo "<p>Todavía no has iniciado tu sesión. Introduce tu usuario y contraseña.</p>";
    echo "<p>Si no estás registrado, pulsa <a href='Mundial2026.php?accio=registrarse' class='estiloenlace'>aquí</a>.</p>";
    echo "</div>";

    // Formulario de Login (Se mantiene centrado automáticamente en el espacio disponible)
    echo "<form action='Mundial2026.php' method='post' class='form-registro'>";
    echo "<h4>Identifícate</h4>";
    echo "<div class='campo'>";
    echo "<label>Usuario:</label>";
    echo "<input name='usuario' required>";
    echo "</div>";
    
    echo "<div class='campo'>";
    echo "<label>Contraseña:</label>";
    echo "<input type='password' name='password' required>";
    echo "</div>";
    echo "<input type='submit' value='Entrar' class='boton-guardar'>";
    echo "</form>";

    // Reglas del juego (Encapsuladas en la clase .reglas_juego con margen izquierdo)
    echo "<div class='reglas_juego' style='margin-top:20px; padding:15px; background:#f9f9f9; border-radius:8px;'>";
    echo "<h4>Reglas de puntuación (Mundial 2026):</h4>";
    echo "Se ganarán puntos al acertar los resultados y los equipos clasificados en cada fase.<br>"; 
    echo "En la <strong>fase de grupos (12 grupos)</strong> la puntuación será la siguiente:<br>";
    echo "- 1 punto por los goles que ha marcado cada equipo.<br>";
    echo "- 3 puntos por acertar el equipo vencedor o el empate.<br>";
    echo "- 3 puntos por el primero de cada grupo.<br>";
    echo "- 4 puntos por los clasificados a dieciseisavos de final.<br>";
    echo "- 5 puntos por los clasificados a octavos de final.<br>";
    echo "- 6 puntos por los clasificados a cuartos de final.<br>";
    echo "- 7 puntos por los clasificados a las semifinales.<br>";
    echo "- 8 puntos por los clasificados a la final.<br>";
    echo "- 10 puntos por acertar el vencedor del Mundial.<br><br>";

    echo "En las <strong>fases eliminatorias directas</strong> se puntuará así:<br>";
    echo "- 1 punto por los goles que marque un equipo en los 90 o 120 minutos (con prórroga).<br>";
    echo "- 1 punto por acertar que NO habrá penaltis.<br>";
    echo "- 3 puntos por acertar que SÍ habrá penaltis.<br><br>";

    echo "<strong>Puntos Extra Predicciones a Futuro por Fase:</strong><br>";
    echo "- <strong>Dieciseisavos:</strong> Hasta 8 puntos por acertar el campeón final.<br>";
    echo "- <strong>Octavos:</strong> Hasta 7 puntos por acertar el campeón final.<br>";
    echo "- <strong>Cuartos:</strong> Hasta 6 puntos por acertar el campeón final.<br>";
    echo "- <strong>Semifinales:</strong> Hasta 5 puntos por acertar el campeón final.<br>";
    echo "- <strong>Final:</strong> 3 puntos por el vencedor directo del partido.<br>";
    echo "</div>";
}
?>