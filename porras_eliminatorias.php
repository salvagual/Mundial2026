<?php
// PORRAS_GRUPOS.PHP - Estructura Cronológica Completa y Alineada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuaris = "datos/usuaris.txt";
$fichero_resultados = "datos/resultats.txt";

if (!file_exists($fichero_resultados) || !file_exists($usuaris)) {
    die("<p style='color:red; font-weight:bold; font-family:monospace; text-align:center; margin-top:50px;'>⚠️ Error: No se encuentran los archivos base.</p>");
}

function limpiarDatoPorra($cadena) {
    $cadena = str_replace("\xEF\xBB\xBF", "", $cadena);
    return trim($cadena, "# \r\n\t\x0B\x00");
}

// 1. CARGAR FICHERO MAESTRO DE RESULTADOS REALES
$lineas_res = file($fichero_resultados, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$partidos_reales = [];
foreach ($lineas_res as $i => $linea) {
    $linea_l = limpiarDatoPorra($linea);
    if ($linea_l !== '') {
        $partidos_reales[$i] = explode('#', $linea_l);
    }
}

// Mapeo de selecciones reales desde resultats.txt
$reales_dieciseis = []; for ($r=72; $r<104; $r++) { $eq = $partidos_reales[$r][0] ?? ''; $reales_dieciseis[] = ($eq !== '' && strtolower($eq) !== 'x') ? strtolower($eq) : ''; }
$reales_octavos    = []; for ($r=104; $r<120; $r++) { $eq = $partidos_reales[$r][5] ?? ''; $reales_octavos[]    = ($eq !== '' && $eq !== 'x') ? strtolower($eq) : ''; }
$reales_cuartos    = []; for ($r=120; $r<128; $r++) { $eq = $partidos_reales[$r][5] ?? ''; $reales_cuartos[]    = ($eq !== '' && $eq !== 'x') ? strtolower($eq) : ''; }
$reales_semis     = []; for ($r=128; $r<132; $r++) { $eq = $partidos_reales[$r][5] ?? ''; $reales_semis[]     = ($eq !== '' && $eq !== 'x') ? strtolower($eq) : ''; }
$reales_final     = []; for ($r=132; $r<134; $r++) { $eq = $partidos_reales[$r][5] ?? ''; $reales_final[]     = ($eq !== '' && $eq !== 'x') ? strtolower($eq) : ''; }
$real_campeon     = isset($partidos_reales[134][5]) ? strtolower(limpiarDatoPorra($partidos_reales[134][5])) : '';

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

// 3. ESTRUCTURA VISUAL DE LA TABLA
echo "<body style='background:#f4f7f6; font-family:\"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; padding:20px; color:#333;'>";
echo "<div style='margin-top: 30px; margin-bottom: 30px; margin-left: 260px; background:#fff; padding:25px; border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 5px solid #2c3e50; overflow-x: auto;'>";
echo "<h2 style='text-align:center; color:#2c3e50; margin-top:0; text-transform:uppercase; letter-spacing:1px; font-weight:700; border-bottom:2px solid #ecf0f1; padding-bottom:15px;'>🔮 AUDITORÍA CRONOLÓGICA GLOBAL DE PORRAS</h2>";

echo "<table style='border-collapse:collapse; margin-top:20px; font-family:monospace; font-size:9px; width:max-content;'>";
echo "<thead>";

// FILA 1 DE CABECERA: Bloques Generales por Archivos/Fases
echo "<tr style='background:#34495e; color:#fff; text-transform:uppercase; font-size:10px; letter-spacing:0.5px;'>";
echo "<th style='padding:12px; text-align:left; background:#2c3e50; position:sticky; left:0; z-index:3;' rowspan='2'>Jugador</th>";
echo "<th style='padding:12px 5px; text-align:center; background:#2c3e50;' colspan='133'>PREDICCIONES INICIALES (Antes del Torneo - \$us.txt)</th>";
echo "<th style='padding:12px 5px; text-align:center; background:#2980b9;' colspan='47'>FASE 1/16 (Antes de los partidos - _setzens)</th>";
echo "<th style='padding:12px 5px; text-align:center; background:#27ae60;' colspan='23'>FASE 1/8 (Antes de los partidos - _vuitens)</th>";
echo "<th style='padding:12px 5px; text-align:center; background:#e67e22;' colspan='11'>FASE 1/4 (Antes de los partidos - _quarts)</th>";
echo "<th style='padding:12px 5px; text-align:center; background:#9b59b6;' colspan='5'>FASE SEMIS (Antes de los partidos - _semis)</th>";
echo "<th style='padding:12px 5px; text-align:center; background:#d4ac0d; color:#000;' colspan='2'>FINAL (_final)</th>";
echo "</tr>";

// FILA 2 DE CABECERA: Subfases detalladas
echo "<tr style='background:#2c3e50; color:#fff; font-size:8px;'>";
// --- BLOQUE INICIAL ($us.txt) ---
for ($j=0; $j<72; $j++)  echo "<th style='padding:5px 2px; min-width:45px; background:#34495e;'>G.".($j+1)."</th>";
for ($j=0; $j<32; $j++)  echo "<th style='padding:5px 2px; min-width:50px; background:#5d6d7e;'>1/16</th>";
for ($j=0; $j<16; $j++)  echo "<th style='padding:5px 2px; min-width:50px; background:#415b76;'>1/8</th>";
for ($j=0; $j<8; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#2e4053;'>1/4</th>";
for ($j=0; $j<4; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#283747;'>SF</th>";
for ($j=0; $j<2; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#212f3d;'>F</th>";
echo "<th style='padding:5px 4px; min-width:55px; background:#78281f;'>Camp</th>";

// --- BLOQUE 1/16 (_setzens.txt) ---
for ($j=72; $j<88; $j++) echo "<th style='padding:5px 2px; min-width:45px; background:#2980b9;'>P.1/16</th>";
for ($j=0; $j<16; $j++)  echo "<th style='padding:5px 2px; min-width:50px; background:#2471a3;'>1/8</th>";
for ($j=0; $j<8; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#1f618d;'>1/4</th>";
for ($j=0; $j<4; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#1a5276;'>SF</th>";
for ($j=0; $j<2; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#154360;'>F</th>";
echo "<th style='padding:5px 4px; min-width:55px; background:#78281f;'>Camp</th>";

// --- BLOQUE 1/8 (_vuitens.txt) ---
for ($j=88; $j<96; $j++) echo "<th style='padding:5px 2px; min-width:45px; background:#27ae60;'>P.1/8</th>";
for ($j=0; $j<8; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#229954;'>1/4</th>";
for ($j=0; $j<4; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#1e8449;'>SF</th>";
for ($j=0; $j<2; $j++)   echo "<th style='padding:5px 2px; min-width:50px; background:#1a5d3b;'>F</th>";
echo "<th style='padding:5px 4px; min-width:55px; background:#78281f;'>Camp</th>";

// --- BLOQUE 1/4 (_quarts.txt) ---
for ($j=96; $j<100; $j++) echo "<th style='padding:5px 2px; min-width:45px; background:#e67e22;'>P.1/4</th>";
for ($j=0; $j<4; $j++)    echo "<th style='padding:5px 2px; min-width:50px; background:#ca6f1e;'>SF</th>";
for ($j=0; $j<2; $j++)    echo "<th style='padding:5px 2px; min-width:50px; background:#b9770e;'>F</th>";
echo "<th style='padding:5px 4px; min-width:55px; background:#78281f;'>Camp</th>";

// --- BLOQUE SEMIS (_semis.txt) ---
for ($j=100; $j<102; $j++) echo "<th style='padding:5px 2px; min-width:45px; background:#9b59b6;'>P.SF</th>";
for ($j=0; $j<2; $j++)     echo "<th style='padding:5px 2px; min-width:50px; background:#884ea1;'>F</th>";
echo "<th style='padding:5px 4px; min-width:55px; background:#78281f;'>Camp</th>";

// --- BLOQUE FINAL (_final.txt) ---
echo "<th style='padding:5px 2px; min-width:45px; background:#f1c40f; color:#000;'>P.F</th>";
echo "<th style='padding:5px 4px; min-width:55px; background:#78281f;'>Camp</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

// 4. FILA DE RESULTADOS REALES (Fila Maestra)
echo "<tr style='background:#eaeded; font-weight:bold; border-bottom:2px solid #bdc3c7;'>";
echo "<td style='padding:12px; font-size:11px; color:#2c3e50; position:sticky; left:0; background:#eaeded; z-index:2;'>⚽ <em>Resultados Reales</em></td>";

// Reales iniciales ($us.txt correspondientes)
for ($k=0; $k<72; $k++) { $r1=$partidos_reales[$k][2] ?? 'x'; $r2=$partidos_reales[$k][3] ?? 'x'; echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #bdc3c7;'>".(($r1==='x'||$r2==='x')?'-':"$r1-$r2")."</td>"; }
for ($k=0; $k<32; $k++) { $n=$reales_dieciseis[$k]??''; echo "<td style='padding:8px 2px; text-align:center; background:#eaeded; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<16; $k++) { $n=$reales_octavos[$k]??'';    echo "<td style='padding:8px 2px; text-align:center; background:#eaeded; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<8; $k++)  { $n=$reales_cuartos[$k]??'';   echo "<td style='padding:8px 2px; text-align:center; background:#eaeded; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<4; $k++)  { $n=$reales_semis[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; background:#eaeded; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<2; $k++)  { $n=$reales_final[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; background:#eaeded; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
echo "<td style='padding:8px 4px; text-align:center; background:#e74c3c; color:#fff;'>".($real_campeon?strtoupper($trad[$real_campeon]??$real_campeon):'-')."</td>";

// Reales bloque 1/16
for ($k=72; $k<88; $k++) { $r1=$partidos_reales[$k][2] ?? 'x'; $r2=$partidos_reales[$k][3] ?? 'x'; echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #bdc3c7;'>".(($r1==='x'||$r2==='x')?'-':"$r1-$r2")."</td>"; }
for ($k=0; $k<16; $k++) { $n=$reales_octavos[$k]??'';    echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<8; $k++)  { $n=$reales_cuartos[$k]??'';   echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<4; $k++)  { $n=$reales_semis[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<2; $k++)  { $n=$reales_final[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
echo "<td style='padding:8px 4px; text-align:center; background:#e74c3c; color:#fff;'>".($real_campeon?strtoupper($trad[$real_campeon]??$real_campeon):'-')."</td>";

// Reales bloque 1/8
for ($k=88; $k<96; $k++) { $r1=$partidos_reales[$k][2] ?? 'x'; $r2=$partidos_reales[$k][3] ?? 'x'; echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #bdc3c7;'>".(($r1==='x'||$r2==='x')?'-':"$r1-$r2")."</td>"; }
for ($k=0; $k<8; $k++)  { $n=$reales_cuartos[$k]??'';   echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<4; $k++)  { $n=$reales_semis[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<2; $k++)  { $n=$reales_final[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
echo "<td style='padding:8px 4px; text-align:center; background:#e74c3c; color:#fff;'>".($real_campeon?strtoupper($trad[$real_campeon]??$real_campeon):'-')."</td>";

// Reales bloque 1/4
for ($k=96; $k<100; $k++) { $r1=$partidos_reales[$k][2] ?? 'x'; $r2=$partidos_reales[$k][3] ?? 'x'; echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #bdc3c7;'>".(($r1==='x'||$r2==='x')?'-':"$r1-$r2")."</td>"; }
for ($k=0; $k<4; $k++)  { $n=$reales_semis[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
for ($k=0; $k<2; $k++)  { $n=$reales_final[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
echo "<td style='padding:8px 4px; text-align:center; background:#e74c3c; color:#fff;'>".($real_campeon?strtoupper($trad[$real_campeon]??$real_campeon):'-')."</td>";

// Reales bloque Semis
for ($k=100; $k<102; $k++) { $r1=$partidos_reales[$k][2] ?? 'x'; $r2=$partidos_reales[$k][3] ?? 'x'; echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #bdc3c7;'>".(($r1==='x'||$r2==='x')?'-':"$r1-$r2")."</td>"; }
for ($k=0; $k<2; $k++)  { $n=$reales_final[$k]??'';     echo "<td style='padding:8px 2px; text-align:center; font-size:8px;'>".($n?strtoupper($trad[$n]??$n):'-')."</td>"; }
echo "<td style='padding:8px 4px; text-align:center; background:#e74c3c; color:#fff;'>".($real_campeon?strtoupper($trad[$real_campeon]??$real_campeon):'-')."</td>";

// Reales bloque Final
$rf1=$partidos_reales[102][2] ?? 'x'; $rf2=$partidos_reales[102][3] ?? 'x'; echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #bdc3c7;'>".(($rf1==='x'||$rf2==='x')?'-':"$rf1-$rf2")."</td>";
echo "<td style='padding:8px 4px; text-align:center; background:#e74c3c; color:#fff;'>".($real_campeon?strtoupper($trad[$real_campeon]??$real_campeon):'-')."</td>";
echo "</tr>";


// 5. RENDERIZADO DE LOS PRONÓSTICOS JUGADOR POR JUGADOR
foreach ($usuarios_lista as $index => $usr) {
    $bg_fila = ($index % 2 == 0) ? "#f8f9fa" : "#ffffff";
    
    $f_base = "datos/" . $usr . ".txt";           $lines_base = file_exists($f_base) ? file($f_base, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $f_setzens = "datos/" . $usr . "_setzens.txt"; $lines_setzens = file_exists($f_setzens) ? file($f_setzens, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $f_vuitens = "datos/" . $usr . "_vuitens.txt"; $lines_vuitens = file_exists($f_vuitens) ? file($f_vuitens, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $f_quarts  = "datos/" . $usr . "_quarts.txt";  $lines_quarts  = file_exists($f_quarts)  ? file($f_quarts,  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $f_semis   = "datos/" . $usr . "_semis.txt";   $lines_semis   = file_exists($f_semis)   ? file($f_semis,   FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $f_final   = "datos/" . $usr . "_final.txt";   $lines_final   = file_exists($f_final)   ? file($f_final,   FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    echo "<tr style='background:$bg_fila; border-bottom:1px solid #eaeded;'>";
    
    $nombre_mostrar = htmlspecialchars($usr, ENT_QUOTES);
    if (isset($_SESSION['usuario']) && strtolower($_SESSION['usuario']) === strtolower($usr)) {
        $nombre_mostrar = "<strong style='color:#2c3e50; background:#ebf5fb; padding:2px 4px; border-radius:3px;'>👤 $nombre_mostrar</strong>";
    }
    echo "<td style='padding:12px; font-size:11px; font-weight:600; position:sticky; left:0; background:$bg_fila; z-index:2;'>$nombre_mostrar</td>";

    // ==========================================
    // 1) BLOQUE INICIAL ($us.txt)
    // ==========================================
    // Grupos partidos (0-71)
    for ($k=0; $k<72; $k++) {
        $prono = ''; $hit = false;
        if (isset($lines_base[$k])) { $df = explode('#', limpiarDatoPorra($lines_base[$k])); $prono = isset($df[1]) && trim($df[1])!=='' ? trim($df[0])."-".trim($df[1]) : trim($df[0]); }
        $r1 = $partidos_reales[$k][2] ?? 'x'; $r2 = $partidos_reales[$k][3] ?? 'x';
        if ($prono !== '' && $r1 !== 'x' && $prono === "$r1-$r2") $hit = true;
        $bg = $hit ? "background:rgba(46,204,113,0.18); color:#27ae60; font-weight:bold;" : "color:#7f8c8d;";
        echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #eaeded; $bg'>".($prono ? htmlspecialchars($prono, ENT_QUOTES) : '-')."</td>";
    }
    // 32 Clasificados 1/16 (Líneas 72-103)
    for ($k=72; $k<=103; $k++) {
        $eq = isset($lines_base[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_base[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_dieciseis));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60; font-weight:bold;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; border-left:1px solid #eaeded; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 16 a Octavos (Líneas 104-119)
    for ($k=104; $k<=119; $k++) {
        $eq = isset($lines_base[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_base[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_octavos));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 8 a Cuartos (Líneas 120-127)
    for ($k=120; $k<=127; $k++) {
        $eq = isset($lines_base[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_base[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_cuartos));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 4 a Semis (Líneas 128-131)
    for ($k=128; $k<=131; $k++) {
        $eq = isset($lines_base[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_base[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_semis));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 2 a la Final (Líneas 132-133)
    for ($k=132; $k<=133; $k++) {
        $eq = isset($lines_base[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_base[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_final));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // Campeón Inicial (Línea 134)
    $eq = isset($lines_base[134]) ? strtolower(limpiarDatoPorra(explode('#', $lines_base[134])[0])) : '';
    $hit = (!empty($eq) && $eq === $real_campeon);
    $bg = $hit ? "background:#2ecc71; color:#fff;" : "color:#7f8c8d;";
    echo "<td style='padding:8px 4px; text-align:center; font-size:9px; font-weight:bold; $bg'>".($eq ? strtoupper(htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES)) : '-')."</td>";


    // ==========================================
    // 2) BLOQUE DE 1/16 (_setzens.txt)
    // ==========================================
    // 16 Partidos (0-15)
    for ($k=0; $k<16; $k++) {
        $prono = ''; $hit = false;
        if (isset($lines_setzens[$k])) { $df = explode('#', limpiarDatoPorra($lines_setzens[$k])); if (isset($df[1]) && trim($df[1])!=='') $prono = trim($df[0])."-".trim($df[1]); }
        $r1 = $partidos_reales[72+$k][2] ?? 'x'; $r2 = $partidos_reales[72+$k][3] ?? 'x';
        if ($prono !== '' && $r1 !== 'x' && $prono === "$r1-$r2") $hit = true;
        $bg = $hit ? "background:rgba(46,204,113,0.18); color:#27ae60; font-weight:bold;" : "color:#7f8c8d;";
        echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #eaeded; $bg'>".($prono ? htmlspecialchars($prono, ENT_QUOTES) : '-')."</td>";
    }
    // 16 a Octavos (Líneas 16-31)
    for ($k=16; $k<32; $k++) {
        $eq = isset($lines_setzens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_setzens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_octavos));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 8 a Cuartos (Líneas 32-39)
    for ($k=32; $k<=39; $k++) {
        $eq = isset($lines_setzens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_setzens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_cuartos));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 4 a Semis (Líneas 40-43)
    for ($k=40; $k<=43; $k++) {
        $eq = isset($lines_setzens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_setzens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_semis));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 2 a la Final (Líneas 44-45)
    for ($k=44; $k<=45; $k++) {
        $eq = isset($lines_setzens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_setzens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_final));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // Campeón (Línea 46)
    $eq = isset($lines_setzens[46]) ? strtolower(limpiarDatoPorra(explode('#', $lines_setzens[46])[0])) : '';
    $hit = (!empty($eq) && $eq === $real_campeon);
    $bg = $hit ? "background:#2ecc71; color:#fff;" : "color:#7f8c8d;";
    echo "<td style='padding:8px 4px; text-align:center; font-size:9px; font-weight:bold; $bg'>".($eq ? strtoupper(htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES)) : '-')."</td>";


    // ==========================================
    // 3) BLOQUE DE 1/8 (_vuitens.txt)
    // ==========================================
    // 8 Partidos (0-7)
    for ($k=0; $k<8; $k++) {
        $prono = ''; $hit = false;
        if (isset($lines_vuitens[$k])) { $df = explode('#', limpiarDatoPorra($lines_vuitens[$k])); if (isset($df[1]) && trim($df[1])!=='') $prono = trim($df[0])."-".trim($df[1]); }
        $r1 = $partidos_reales[88+$k][2] ?? 'x'; $r2 = $partidos_reales[88+$k][3] ?? 'x';
        if ($prono !== '' && $r1 !== 'x' && $prono === "$r1-$r2") $hit = true;
        $bg = $hit ? "background:rgba(46,204,113,0.18); color:#27ae60; font-weight:bold;" : "color:#7f8c8d;";
        echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #eaeded; $bg'>".($prono ? htmlspecialchars($prono, ENT_QUOTES) : '-')."</td>";
    }
    // 8 a Cuartos (Líneas 8-15)
    for ($k=8; $k<16; $k++) {
        $eq = isset($lines_vuitens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_vuitens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_cuartos));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 4 a Semis (Líneas 16-19)
    for ($k=16; $k<=19; $k++) {
        $eq = isset($lines_vuitens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_vuitens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_semis));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 2 a la Final (Líneas 20-21)
    for ($k=20; $k<=21; $k++) {
        $eq = isset($lines_vuitens[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_vuitens[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_final));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // Campeón (Línea 22)
    $eq = isset($lines_vuitens[22]) ? strtolower(limpiarDatoPorra(explode('#', $lines_vuitens[22])[0])) : '';
    $hit = (!empty($eq) && $eq === $real_campeon);
    $bg = $hit ? "background:#2ecc71; color:#fff;" : "color:#7f8c8d;";
    echo "<td style='padding:8px 4px; text-align:center; font-size:9px; font-weight:bold; $bg'>".($eq ? strtoupper(htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES)) : '-')."</td>";


    // ==========================================
    // 4) BLOQUE DE 1/4 (_quarts.txt)
    // ==========================================
    // 4 Partidos (0-3)
    for ($k=0; $k<4; $k++) {
        $prono = ''; $hit = false;
        if (isset($lines_quarts[$k])) { $df = explode('#', limpiarDatoPorra($lines_quarts[$k])); if (isset($df[1]) && trim($df[1])!=='') $prono = trim($df[0])."-".trim($df[1]); }
        $r1 = $partidos_reales[96+$k][2] ?? 'x'; $r2 = $partidos_reales[96+$k][3] ?? 'x';
        if ($prono !== '' && $r1 !== 'x' && $prono === "$r1-$r2") $hit = true;
        $bg = $hit ? "background:rgba(46,204,113,0.18); color:#27ae60; font-weight:bold;" : "color:#7f8c8d;";
        echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #eaeded; $bg'>".($prono ? htmlspecialchars($prono, ENT_QUOTES) : '-')."</td>";
    }
    // 4 a Semis (Líneas 4-7)
    for ($k=4; $k<8; $k++) {
        $eq = isset($lines_quarts[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_quarts[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_semis));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // 2 a la Final (Líneas 8-9)
    for ($k=8; $k<=9; $k++) {
        $eq = isset($lines_quarts[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_quarts[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_final));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // Campeón (Línea 10)
    $eq = isset($lines_quarts[10]) ? strtolower(limpiarDatoPorra(explode('#', $lines_quarts[10])[0])) : '';
    $hit = (!empty($eq) && $eq === $real_campeon);
    $bg = $hit ? "background:#2ecc71; color:#fff;" : "color:#7f8c8d;";
    echo "<td style='padding:8px 4px; text-align:center; font-size:9px; font-weight:bold; $bg'>".($eq ? strtoupper(htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES)) : '-')."</td>";


    // ==========================================
    // 5) BLOQUE DE SEMIFINALES (_semis.txt)
    // ==========================================
    // 2 Partidos (0-1)
    for ($k=0; $k<2; $k++) {
        $prono = ''; $hit = false;
        if (isset($lines_semis[$k])) { $df = explode('#', limpiarDatoPorra($lines_semis[$k])); if (isset($df[1]) && trim($df[1])!=='') $prono = trim($df[0])."-".trim($df[1]); }
        $r1 = $partidos_reales[100+$k][2] ?? 'x'; $r2 = $partidos_reales[100+$k][3] ?? 'x';
        if ($prono !== '' && $r1 !== 'x' && $prono === "$r1-$r2") $hit = true;
        $bg = $hit ? "background:rgba(46,204,113,0.18); color:#27ae60; font-weight:bold;" : "color:#7f8c8d;";
        echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #eaeded; $bg'>".($prono ? htmlspecialchars($prono, ENT_QUOTES) : '-')."</td>";
    }
    // 2 a la Final (Líneas 2-3)
    for ($k=2; $k<4; $k++) {
        $eq = isset($lines_semis[$k]) ? strtolower(limpiarDatoPorra(explode('#', $lines_semis[$k])[0])) : '';
        $hit = (!empty($eq) && in_array($eq, $reales_final));
        $bg = $hit ? "background:rgba(46,204,113,0.12); color:#27ae60;" : "color:#95a5a6;";
        echo "<td style='padding:8px 2px; text-align:center; font-size:8px; $bg'>".($eq ? htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES) : '-')."</td>";
    }
    // Campeón (Línea 4)
    $eq = isset($lines_semis[4]) ? strtolower(limpiarDatoPorra(explode('#', $lines_semis[4])[0])) : '';
    $hit = (!empty($eq) && $eq === $real_campeon);
    $bg = $hit ? "background:#2ecc71; color:#fff;" : "color:#7f8c8d;";
    echo "<td style='padding:8px 4px; text-align:center; font-size:9px; font-weight:bold; $bg'>".($eq ? strtoupper(htmlspecialchars(($trad[$eq] ?? $eq), ENT_QUOTES)) : '-')."</td>";


    // ==========================================
    // 6) BLOQUE DE LA FINAL (_final.txt)
    // ==========================================
    // Partido Final (0)
    $prono_f = ''; $hit_f = false;
    if (isset($lines_final[0])) { $df = explode('#', limpiarDatoPorra($lines_final[0])); if (isset($df[1]) && trim($df[1])!=='') $prono_f = trim($df[0])."-".trim($df[1]); }
    $rf1 = $partidos_reales[102][2] ?? 'x'; $rf2 = $partidos_reales[102][3] ?? 'x';
    if ($prono_f !== '' && $rf1 !== 'x' && $prono_f === "$rf1-$rf2") $hit_f = true;
    $bg_f = $hit_f ? "background:rgba(46,204,113,0.18); color:#27ae60; font-weight:bold;" : "color:#7f8c8d;";
    echo "<td style='padding:8px 2px; text-align:center; border-left:1px solid #eaeded; $bg_f'>".($prono_f ? htmlspecialchars($prono_f, ENT_QUOTES) : '-')."</td>";

    // Vencedor Final / Campeón (1)
    $eq_campeon = isset($lines_final[1]) ? strtolower(limpiarDatoPorra(explode('#', $lines_final[1])[0])) : '';
    $hit_campeon = (!empty($eq_campeon) && $eq_campeon === $real_campeon);
    $bg_camp = $hit_campeon ? "background:#2ecc71; color:#fff; font-weight:bold;" : ($eq_campeon ? "background:rgba(231,76,60,0.1); color:#c0392b;" : "background:#fafafa; color:#bbb;");
    echo "<td style='padding:8px 4px; text-align:center; font-size:9px; font-weight:bold; $bg_camp'>" . ($eq_campeon ? strtoupper(htmlspecialchars(($trad[$eq_campeon] ?? $eq_campeon), ENT_QUOTES)) : '-') . "</td>";

    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</body>";
?>