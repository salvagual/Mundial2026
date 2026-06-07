<?php
// PORRAS_GRUPOS.PHP - Corrección de Visualización Completa de Goles del Usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuaris = "datos/usuaris.txt";
$fichero_resultados = "datos/resultats.txt";

if (!file_exists($fichero_resultados) || !file_exists($usuaris)) {
    die("<p style='color:red; font-weight:bold; font-family:monospace; text-align:center; margin-top:50px;'>⚠️ Error: No se encuentran los archivos base de resultados o usuarios.</p>");
}

// Función de limpieza para cadenas de texto
function limpiarDatoPorra($cadena) {
    $cadena = str_replace("\xEF\xBB\xBF", "", $cadena);
    return trim($cadena, "# \r\n\t\x0B\x00");
}

// 1. CARGAR RESULTADOS REALES DEL MUNDIAL
$lineas_res = file($fichero_resultados, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$partidos_reales = [];
foreach ($lineas_res as $i => $linea) {
    $linea_l = limpiarDatoPorra($linea);
    if ($linea_l !== '') {
        $partidos_reales[$i] = explode('#', $linea_l);
    }
}

// Extraer listas reales completas para las fases eliminatorias
$reales_octavos = []; for ($r=104; $r<120; $r++) { $eq = $partidos_reales[$r][5] ?? ''; if($eq !== '' && $eq !== 'x') $reales_octavos[] = strtolower($eq); }
$reales_cuartos = []; for ($r=120; $r<128; $r++) { $eq = $partidos_reales[$r][5] ?? ''; if($eq !== '' && $eq !== 'x') $reales_cuartos[] = strtolower($eq); }
$reales_semis   = []; for ($r=128; $r<132; $r++) { $eq = $partidos_reales[$r][5] ?? ''; if($eq !== '' && $eq !== 'x') $reales_semis[]   = strtolower($eq); }
$reales_final   = []; for ($r=132; $r<134; $r++) { $eq = $partidos_reales[$r][5] ?? ''; if($eq !== '' && $eq !== 'x') $reales_final[]   = strtolower($eq); }
$real_campeon   = isset($partidos_reales[134][5]) ? strtolower(limpiarDatoPorra($partidos_reales[134][5])) : '';

// 2. CARGAR LISTA DE JUGADORES
$lineas_usuarios = file($usuaris, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$usuarios_lista = [];
foreach ($lineas_usuarios as $linea_u) {
    $linea_u_l = limpiarDatoPorra($linea_u);
    if ($linea_u_l === '') continue;
    $datos_u = explode('#', $linea_u_l);
    $nombre_u = limpiarDatoPorra($datos_u[0] ?? '');
    if ($nombre_u !== '' && $nombre_u !== 'usuario') {
        $usuarios_lista[] = $nombre_u;
    }
}

// 3. DISEÑO VISUAL CONTENEDOR
echo "<body style='background:#f4f7f6; font-family:\"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; padding:20px; color:#333;'>";

echo "<div style='margin-top: 30px; margin-bottom: 30px; margin-left: 260px; background:#fff; padding:25px; border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 5px solid #9b59b6; overflow-x: auto;'>";

$titulo_porras = isset($trad['PORRAS GRUPOS']) ? $trad['PORRAS GRUPOS'] : "PRONÓSTICOS COMPLETOS DEL TORNEO POR JUGADOR";
echo "<h2 style='text-align:center; color:#2c3e50; margin-top:0; text-transform:uppercase; letter-spacing:1px; font-weight:700; border-bottom:2px solid #ecf0f1; padding-bottom:15px;'>🔮 $titulo_porras</h2>";

echo "<table style='border-collapse:collapse; margin-top:20px; font-family:monospace; font-size:10px; width:max-content;'>";
echo "<thead>";
echo "<tr style='background:#34495e; color:#fff; text-transform:uppercase; font-size:10px; letter-spacing:0.5px;'>";
echo "<th style='padding:12px 12px; text-align:left; border-top-left-radius:6px; font-size:11px; background:#2c3e50; position:sticky; left:0; z-index:2;'>Jugador</th>";

// Columnas de los 72 partidos de la fase de grupos
for ($j = 0; $j < 72; $j++) {
    $eq1 = isset($partidos_reales[$j][0]) ? ($trad[$partidos_reales[$j][0]] ?? $partidos_reales[$j][0]) : "P".($j+1);
    $eq2 = isset($partidos_reales[$j][1]) ? ($trad[$partidos_reales[$j][1]] ?? $partidos_reales[$j][1]) : "";
    echo "<th style='padding:12px 5px; text-align:center; border-left:1px solid #4a6572; min-width:55px; font-size:8.5px;'>$eq1-$eq2</th>";
}

// Columnas de las fases eliminatorias
echo "<th style='padding:12px 6px; text-align:center; background:#2980b9;' colspan='16'>Octavofinalistas (16)</th>";
echo "<th style='padding:12px 6px; text-align:center; background:#27ae60;' colspan='8'>Cuartofinalistas (8)</th>";
echo "<th style='padding:12px 6px; text-align:center; background:#d35400;' colspan='4'>Semifinalistas (4)</th>";
echo "<th style='padding:12px 6px; text-align:center; background:#8e44ad;' colspan='2'>Finalistas (2)</th>";
echo "<th style='padding:12px 10px; text-align:center; border-top-right-radius:6px; background:#e74c3c; font-size:11px;'>Campeón</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

// 4. FILA DE RESULTADOS REALES
echo "<tr style='background:#eaeded; font-weight:bold; border-bottom:2px solid #bdc3c7;'>";
echo "<td style='padding:12px 12px; font-size:11px; color:#2c3e50; position:sticky; left:0; background:#eaeded; z-index:2;'>⚽ <em>Resultados Reales</em></td>";

// 72 Resultados Reales de Grupos (Muestra el marcador real completo "GolesL-GolesV")
for ($k = 0; $k < 72; $k++) {
    $r1 = $partidos_reales[$k][2] ?? 'x';
    $r2 = $partidos_reales[$k][3] ?? 'x';
    $texto_res = ($r1 === 'x' || $r2 === 'x') ? '-' : "$r1-$r2";
    echo "<td style='padding:10px 4px; text-align:center; color:#2c3e50; border-left:1px solid #bdc3c7;'>$texto_res</td>";
}
// Resultados reales eliminatorias
for ($k = 0; $k < 16; $k++) echo "<td style='padding:10px 4px; text-align:center; color:#2980b9; background:rgba(41,128,185,0.08); border-left:1px solid #bdc3c7; font-size:9px;'>" . ($reales_octavos[$k] ?? '-') . "</td>";
for ($k = 0; $k < 8; $k++)  echo "<td style='padding:10px 4px; text-align:center; color:#27ae60; background:rgba(39,174,96,0.08); border-left:1px solid #bdc3c7; font-size:9px;'> " . ($reales_cuartos[$k] ?? '-') . "</td>";
for ($k = 0; $k < 4; $k++)  echo "<td style='padding:10px 4px; text-align:center; color:#d35400; background:rgba(211,84,0,0.08); border-left:1px solid #bdc3c7;'>" . ($reales_semis[$k] ?? '-') . "</td>";
for ($k = 0; $k < 2; $k++)  echo "<td style='padding:10px 4px; text-align:center; color:#8e44ad; background:rgba(142,68,173,0.08); border-left:1px solid #bdc3c7;'>" . ($reales_final[$k] ?? '-') . "</td>";
echo "<td style='padding:10px 6px; text-align:center; color:#fff; background:#e74c3c; font-size:11px;'>" . ($real_campeon ? strtoupper($real_campeon) : '-') . "</td>";
echo "</tr>";

// 5. CELDAS DE PRONÓSTICOS DE JUGADORES
foreach ($usuarios_lista as $index => $usr) {
    $bg_fila = ($index % 2 == 0) ? "#f8f9fa" : "#ffffff";
    $fichero_user = "datos/" . $usr . ".txt";
    
    // Cargamos las líneas crudas del usuario para poder leer múltiples columnas por fila si fuera necesario
    $lineas_u_raw = array_fill(0, 140, '');
    if (file_exists($fichero_user)) {
        $lineas_u_raw = file($fichero_user, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    echo "<tr style='background:$bg_fila; border-bottom:1px solid #eaeded;'>";
    
    // Nombre del jugador fijado con CSS sticky
    $nombre_mostrar = htmlspecialchars($usr, ENT_QUOTES);
    if (isset($_SESSION['usuario']) && strtolower($_SESSION['usuario']) === strtolower($usr)) {
        $nombre_mostrar = "<strong style='color:#9b59b6; background:#f5eef8; padding:2px 4px; border-radius:3px;'>👤 $nombre_mostrar</strong>";
    }
    echo "<td style='padding:12px 12px; font-size:11px; font-weight:600; white-space:nowrap; position:sticky; left:0; background:$bg_fila; box-shadow: 2px 0 5px rgba(0,0,0,0.04); z-index:1;'>$nombre_mostrar</td>";

    // A) PRONÓSTICOS FASE DE GRUPOS (72 partidos)
    for ($k = 0; $k < 72; $k++) {
        $prono_completo = '';
        $hit_partido = false;

        if (isset($lineas_u_raw[$k])) {
            $datos_fila_user = explode('#', limpiarDatoPorra($lineas_u_raw[$k]));
            
            // Evaluamos si el resultado se guarda como cadena unificada "GolesL-GolesV" en el primer campo
            // o si está dividido en campos adyacentes ($datos_fila_user[0] y $datos_fila_user[1])
            $g_local = isset($datos_fila_user[0]) ? trim($datos_fila_user[0]) : '';
            $g_visit = isset($datos_fila_user[1]) ? trim($datos_fila_user[1]) : '';

            if ($g_visit !== '' && $g_visit !== null) {
                // Si están separados por '#' en el fichero, los unimos con un guion para la vista
                $prono_completo = "$g_local-$g_visit";
            } else {
                // Si ya venía la cadena completa "X-Y", la usamos directamente
                $prono_completo = $g_local;
            }
        }

        // Recuperamos los goles reales correspondientes para validar el color de fondo
        $real_1 = $partidos_reales[$k][2] ?? 'x';
        $real_2 = $partidos_reales[$k][3] ?? 'x';
        $comparador_real = ($real_1 !== 'x' && $real_2 !== 'x') ? "$real_1-$real_2" : 'x';

        if (!empty($prono_completo) && $comparador_real !== 'x' && $prono_completo === $comparador_real) {
            $hit_partido = true;
        }

        $bg_celda = $hit_partido ? "background:rgba(46,204,113,0.2); color:#27ae60; font-weight:bold;" : "background:rgba(231,76,60,0.02); color:#7f8c8d;";
        
        echo "<td style='padding:10px 4px; text-align:center; border-left:1px solid #eaeded; $bg_celda'>" . ($prono_completo !== '' ? htmlspecialchars($prono_completo, ENT_QUOTES) : '-') . "</td>";
    }

    // B) OCTAVOFINALISTAS COMPLETOS (Líneas 104 a 119)
    for ($k = 104; $k <= 119; $k++) {
        $datos_fila_user = isset($lineas_u_raw[$k]) ? explode('#', limpiarDatoPorra($lineas_u_raw[$k])) : [];
        $eq = isset($datos_fila_user[0]) ? strtolower($datos_fila_user[0]) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_octavos));
        $bg_celda = $hit ? "background:rgba(46,204,113,0.15); color:#27ae60; font-weight:bold;" : "background:rgba(231,76,60,0.05); color:#95a5a6;";
        echo "<td style='padding:10px 4px; text-align:center; border-left:1px solid #eaeded; font-size:9px; $bg_celda'>" . ($eq ? htmlspecialchars($eq, ENT_QUOTES) : '-') . "</td>";
    }

    // C) CUARTOFINALISTAS COMPLETOS (Líneas 120 a 127)
    for ($k = 120; $k <= 127; $k++) {
        $datos_fila_user = isset($lineas_u_raw[$k]) ? explode('#', limpiarDatoPorra($lineas_u_raw[$k])) : [];
        $eq = isset($datos_fila_user[0]) ? strtolower($datos_fila_user[0]) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_cuartos));
        $bg_celda = $hit ? "background:rgba(46,204,113,0.15); color:#27ae60; font-weight:bold;" : "background:rgba(231,76,60,0.05); color:#95a5a6;";
        echo "<td style='padding:10px 4px; text-align:center; border-left:1px solid #eaeded; font-size:9px; $bg_celda'>" . ($eq ? htmlspecialchars($eq, ENT_QUOTES) : '-') . "</td>";
    }

    // D) SEMIFINALISTAS (Líneas 128 a 131)
    for ($k = 128; $k <= 131; $k++) {
        $datos_fila_user = isset($lineas_u_raw[$k]) ? explode('#', limpiarDatoPorra($lineas_u_raw[$k])) : [];
        $eq = isset($datos_fila_user[0]) ? strtolower($datos_fila_user[0]) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_semis));
        $bg_celda = $hit ? "background:rgba(46,204,113,0.15); color:#27ae60; font-weight:bold;" : "background:rgba(231,76,60,0.05); color:#95a5a6;";
        echo "<td style='padding:10px 4px; text-align:center; border-left:1px solid #eaeded; $bg_celda'>" . ($eq ? htmlspecialchars($eq, ENT_QUOTES) : '-') . "</td>";
    }

    // E) FINALISTAS (Líneas 132 y 133)
    for ($k = 132; $k <= 133; $k++) {
        $datos_fila_user = isset($lineas_u_raw[$k]) ? explode('#', limpiarDatoPorra($lineas_u_raw[$k])) : [];
        $eq = isset($datos_fila_user[0]) ? strtolower($datos_fila_user[0]) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_final));
        $bg_celda = $hit ? "background:rgba(46,204,113,0.15); color:#27ae60; font-weight:bold;" : "background:rgba(231,76,60,0.05); color:#95a5a6;";
        echo "<td style='padding:10px 4px; text-align:center; border-left:1px solid #eaeded; $bg_celda'>" . ($eq ? htmlspecialchars($eq, ENT_QUOTES) : '-') . "</td>";
    }

    // F) GANADOR / CAMPEÓN DEL MUNDO (Línea 134)
    $datos_fila_user = isset($lineas_u_raw[134]) ? explode('#', limpiarDatoPorra($lineas_u_raw[134])) : [];
    $eq_campeon = isset($datos_fila_user[0]) ? strtolower($datos_fila_user[0]) : '';
    $hit_campeon = (!empty($eq_campeon) && $eq_campeon === $real_campeon);
    $bg_camp = $hit_campeon ? "background:#2ecc71; color:#fff; font-weight:bold;" : ($eq_campeon ? "background:rgba(231,76,60,0.1); color:#c0392b;" : "background:#fafafa; color:#bbb;");
    echo "<td style='padding:10px 8px; text-align:center; border-left:2px solid #34495e; font-size:11px; font-weight:bold; $bg_camp'>" . ($eq_campeon ? strtoupper(htmlspecialchars($eq_campeon, ENT_QUOTES)) : '-') . "</td>";

    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

echo "</div>";
echo "</body>";
?>