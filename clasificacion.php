<?php
// CLASIFICACION.PHP - Versión Adaptada con Desplazamiento de Margen (Evita solapamiento)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$fichero_puntos = "datos/puntos_usuarios.txt";

if (!file_exists($fichero_puntos)) {
    die("<p style='color:red; font-weight:bold; font-family:monospace; text-align:center; margin-top:50px;'>⚠️ Error: No se ha generado el archivo de puntuaciones. Ejecuta primero prepara_classificacio.php</p>");
}

// Función de limpieza para evitar fallos por espacios o caracteres invisibles
function limpiarDato($cadena) {
    $cadena = str_replace("\xEF\xBB\xBF", "", $cadena);
    return trim($cadena, "# \r\n\t\x0B\x00");
}

// 1. CARGAR Y PROCESAR PUNTUACIONES
$lineas_puntos = file($fichero_puntos, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$tabla_usuarios = [];

foreach ($lineas_puntos as $num_linea => $linea) {
    $linea_limpia = limpiarDato($linea);
    if ($linea_limpia === '' || $num_linea === 0) continue; // Saltamos la cabecera del archivo

    $datos = explode('#', $linea_limpia);
    
    $nombre         = limpiarDato($datos[0]);
    $pts_grupos     = (int)limpiarDato($datos[1]);
    $pts_setzens    = (int)limpiarDato($datos[2]); // Dieciseisavos
    $pts_octavos    = (int)limpiarDato($datos[3]);
    $pts_cuartos    = (int)limpiarDato($datos[4]);
    $pts_semis      = (int)limpiarDato($datos[5]);
    $pts_final      = (int)limpiarDato($datos[6]);
    $pts_totales    = (int)limpiarDato($datos[7]);

    if ($nombre === '' || $nombre === 'usuario') continue;

    // Agrupamos Grupos y Dieciseisavos para encajar en el formato clásico de la tabla original
    $fase_grupos_total = $pts_grupos + $pts_setzens;

    $tabla_usuarios[] = [
        'nombre'   => $nombre,
        'grupos'   => $fase_grupos_total,
        'octavos'  => $pts_octavos,
        'cuartos'  => $pts_cuartos,
        'semis'    => $pts_semis,
        'final'    => $pts_final,
        'total'    => $pts_totales
    ];
}

// 2. ORDENAR DE MAYOR A MENOR PUNTUACIÓN TOTAL
usort($tabla_usuarios, function($a, $b) {
    return $b['total'] <=> $a['total'];
});

// 3. DISEÑO VISUAL CON MARGEN DESPLAZADO A LA DERECHA
echo "<body style='background:#f4f7f6; font-family:\"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; padding:20px; color:#333;'>";

// MODIFICACIÓN: Se añade 'margin-left: 260px' para dejar espacio libre al menú de la izquierda
echo "<div style='max-width:900px; margin-top: 30px; margin-bottom: 30px; margin-left: 260px; background:#fff; padding:35px; border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 5px solid #2ecc71;'>";

// Cabecera estilizada con traducción nativa fallback
$titulo_clasificacion = isset($trad['CLASIFICACIÓN']) ? $trad['CLASIFICACIÓN'] : "CLASIFICACIÓN GENERAL";
echo "<h2 style='text-align:center; color:#2c3e50; margin-top:0; text-transform:uppercase; letter-spacing:1px; font-weight:700; border-bottom:2px solid #ecf0f1; padding-bottom:15px;'>🏆 $titulo_clasificacion</h2>";

echo "<table style='width:100%; border-collapse:collapse; margin-top:20px; font-family:monospace; font-size:13px;'>";
echo "<thead>";
echo "<tr style='background:#34495e; color:#fff; text-transform:uppercase; font-size:11px; letter-spacing:0.5px;'>";
echo "<th style='padding:12px 10px; border-top-left-radius:6px;'>Pos</th>";
echo "<th style='padding:12px 10px; text-align:left;'>Jugador</th>";
echo "<th style='padding:12px 10px; text-align:center;'>Fase Grupos / 16vos</th>";
echo "<th style='padding:12px 10px; text-align:center;'>Octavos</th>";
echo "<th style='padding:12px 10px; text-align:center;'>Cuartos</th>";
echo "<th style='padding:12px 10px; text-align:center;'>Semifinales</th>";
echo "<th style='padding:12px 10px; text-align:center;'>Final / Vencedor</th>";
echo "<th style='padding:12px 10px; text-align:center; background:#2ecc71; border-top-right-radius:6px;'>Total</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$posicion = 1;
foreach ($tabla_usuarios as $fila) {
    // Alternancia de colores para las filas
    $bg_fila = ($posicion % 2 == 0) ? "#f8f9fa" : "#ffffff";
    
    // Resaltados decorativos especiales para el Podio de los 3 primeros
    $estilo_pos = "font-weight:bold; color:#7f8c8d;";
    if ($posicion === 1) $estilo_pos = "background:#f1c40f; color:#fff; padding:3px 7px; border-radius:50%; font-weight:bold; display:inline-block; min-width:14px; text-align:center;";
    if ($posicion === 2) $estilo_pos = "background:#95a5a6; color:#fff; padding:3px 7px; border-radius:50%; font-weight:bold; display:inline-block; min-width:14px; text-align:center;";
    if ($posicion === 3) $estilo_pos = "background:#d35400; color:#fff; padding:3px 7px; border-radius:50%; font-weight:bold; display:inline-block; min-width:14px; text-align:center;";

    // Destacar en negrita al usuario en sesión si coincide
    $nombre_mostrar = htmlspecialchars($fila['nombre'], ENT_QUOTES);
    if (isset($_SESSION['usuario']) && strtolower($_SESSION['usuario']) === strtolower($fila['nombre'])) {
        $nombre_mostrar = "<strong style='color:#2980b9; background:#e8f4f8; padding:2px 6px; border-radius:3px;'>👤 $nombre_mostrar (Tú)</strong>";
    }

    echo "<tr style='background:$bg_fila; border-bottom:1px solid #eaeded; transition: background 0.2s;'>";
    echo "<td style='padding:14px 10px; text-align:center;'><span style='$estilo_pos'>$posicion</span></td>";
    echo "<td style='padding:14px 10px; font-size:14px; font-weight:500;'>$nombre_mostrar</td>";
    echo "<td style='padding:14px 10px; text-align:center; color:#7f8c8d;'>+{$fila['grupos']}</td>";
    echo "<td style='padding:14px 10px; text-align:center; color:#7f8c8d;'>+{$fila['octavos']}</td>";
    echo "<td style='padding:14px 10px; text-align:center; color:#7f8c8d;'>+{$fila['cuartos']}</td>";
    echo "<td style='padding:14px 10px; text-align:center; color:#7f8c8d;'>+{$fila['semis']}</td>";
    echo "<td style='padding:14px 10px; text-align:center; color:#7f8c8d;'>+{$fila['final']}</td>";
    echo "<td style='padding:14px 10px; text-align:center; font-size:15px; font-weight:bold; background:rgba(46, 204, 113, 0.15); color:#27ae60;'>{$fila['total']} pts</td>";
    echo "</tr>";

    $posicion++;
}

echo "</tbody>";
echo "</table>";

echo "<div style='margin-top:25px; font-size:11px; color:#95a5a6; text-align:center; font-style:italic;'>";
echo "Clasificación actualizada automáticamente tras computar los resultados reales del torneo.";
echo "</div>";

echo "</div>"; // Cierre del contenedor principal desplazado
echo "</body>";
?>