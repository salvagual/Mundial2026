<?php
// PRESENTACIO_CA.PHP - Versió Mundial 2026

if ($usuario_autenticado) {
    echo "<div class='form-registro'>";
    echo "<h3>" . htmlspecialchars(($trad["benvingut "] ?? "Benvingut/da, ") . $nom_usuari) . "</h3>";
    echo "<p>Ja has iniciat la teva sessió. Pots gestionar les teves porres des del menú lateral.</p>";
    echo "</div>";
} 
else {
    echo "<div class='presentacion-header'>"; // Esta clase ahora tiene el margen izquierdo asignado
    echo "<h3>Benvingut al Joc del Mundial 2026</h3>";
    echo "<p>Encara no has iniciat la teva sessió. Introdueix el teu usuari i contrasenya.</p>";
    echo "<p>Si no estàs registrat, prem <a href='Mundial2026.php?accio=registrarse' class='estiloenlace'>aquí</a>.</p>";
    echo "</div>";

    echo "<form action='Mundial2026.php' method='post' class='form-registro'>";
    echo "<h4>Identifica't</h4>";
    echo "<div class='campo'>";
    echo "<label>Nom usuari:</label>";
    echo "<input name='usuario' required>";
    echo "</div>";

    echo "<div class='campo'>";
    echo "<label>Password:</label>";
    echo "<input type='password' name='password' required>";
    echo "</div>";
    echo "<input type='submit' name='boton' value='Entrar' class='boton-guardar'>";
    echo "</form>";

    echo "<div class='reglas-contenedor' style='margin-top:20px; padding:15px; background:#f9f9f9; border-radius:8px;'>"; // Esta clase ahora tiene el margen izquierdo asignado
    echo "<h4>Regles del Joc (Mundial 2026)</h4>";
    echo "<p>El joc consisteix en encertar els resultats del mundial. Es guanyaran punts de la següent manera:</p>";
    
    echo "<h5>Puntuació Fase de Grups (12 Grups):</h5>";
    echo "<ul>
            <li>1 punt pels gols que ha marcat cada equip.</li>
            <li>3 punts per l'equip guanyador o l'empat.</li>
            <li>3 punts pel primer de cada grup.</li>
            <li>4 punts pels classificats a setzens de final.</li>
            <li>5 punts pels classificats a vuitens de final.</li>
            <li>6 punts pels classificats a quarts de final.</li>
            <li>7 punts pels classificats a les semifinals.</li>
            <li>8 punts pels classificats a la final.</li>
            <li>10 punts per encertar el guanyador del mundial.</li>
          </ul>";

    echo "<h5>Puntuació Fases Eliminatòries:</h5>";
    echo "<ul>
            <li>1 punt pels gols marcats (en 90 minuts o 120 amb pròrroga).</li>
            <li>1 punt per encertar que NO hi haurà penals.</li>
            <li>3 punts per encertar que SÍ hi haurà penals.</li>
          </ul>";

    echo "<h5>Punts Extra per Fase (Campió futur):</h5>";
    echo "<ul>
            <li><strong>Setzens:</strong> Fins a 8 punts per encertar el guanyador final.</li>
            <li><strong>Vuitens:</strong> Fins a 7 punts per encertar el guanyador final.</li>
            <li><strong>Quarts:</strong> Fins a 6 punts per encertar el guanyador final.</li>
            <li><strong>Semifinals:</strong> Fins a 5 punts per encertar el guanyador final.</li>
            <li><strong>Final:</strong> 3 punts pel guanyador del partit.</li>
          </ul>";
    echo "</div>";
}
?>