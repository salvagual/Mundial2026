<?php
// SEMIFINALES.PHP - Versión 1.0 (Comienzo en Semifinales con Prórroga Sí/No + Gran Final)
if (!isset($_SESSION['usuari'])) {
    echo "<p class='error'>Debe iniciar sesión para ver esta página.</p>";
    return;
}

$us = $_SESSION['usuari'];
$puntos_totales = 0; 

// 1. FUNCIÓN DE VALIDACIÓN DE EQUIPOS
function verificarEquipo($nombreEquipo, $listaReales, $puntos, &$puntos_globales) {
    if (!$nombreEquipo || $nombreEquipo == "---" || empty($listaReales) || $nombreEquipo == 'x') return "";
    $eq_clean = strtolower(trim($nombreEquipo));
    $reales_clean = array_map(function($v) { return strtolower(trim($v)); }, $listaReales);
    
    if (in_array($eq_clean, $reales_clean)) {
        $puntos_globales += $puntos;
        return " <img src='img/ok.png' width='15'> <span style='color:green; font-size:11px; font-weight:bold;'>+$puntos</span>";
    }
    return "";
}

// 2. LEER RESULTADOS REALES DESDE RESULTATS.TXT
$partidos_semis = []; 
$reales_final = []; 
$real_campeon = "";

if (file_exists('datos/resultats.txt')) {
    $lineas_res = file('datos/resultats.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // En tu resultats.txt, los 2 partidos reales de Semifinales se encuentran 
    // en las líneas 132 y 133 (índices 132 y 133).
    for ($i = 132; $i < 134; $i++) {
        if (isset($lineas_res[$i])) {
            $partidos_semis[] = explode('#', trim($lineas_res[$i]));
        }
    }

    // Clasificados Reales a la Final (Ganadores de Semis, líneas 132 y 133, componente [5])
    for ($i = 132; $i < 134; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_final[] = $partes[5];
            }
        }
    }

    // Vencedor Real / Campeón del Mundo (Línea 134, componente [5])
    if (isset($lineas_res[134])) {
        $partes = explode('#', trim($lineas_res[134]));
        if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
            $real_campeon = $partes[5];
        }
    }
}

// 3. CARGAR PRONÓSTICOS DEL USUARIO (_semis.txt)
$archivo_semis = "datos/" . $us . "_semis.txt";
$datos_goles_semis = [];
$user_clasificados = [];

if (file_exists($archivo_semis)) {
    $lineas_u = file($archivo_semis, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas_u as $index => $l) {
        $partes = explode('#', trim($l));
        if ($index < 2) {
            // Primeras 2 líneas: Goles y prórroga del usuario para Semifinales
            $datos_goles_semis[$index] = $partes;
        } else {
            // Siguientes líneas: Guardamos las elecciones del árbol (Índices correlativos)
            $user_clasificados[$index - 1] = $partes[0] ?? '';
        }
    }
}

// 4. GUARDAR CAMBIOS POST
if (isset($_POST['boton'])) {
    $txt = "";
    // Guardamos los goles y prórrogas de los 2 partidos de Semifinales
    for ($i = 0; $i < 2; $i++) {
        $pL = max(0, (int)$_POST["p_{$i}_L"]);
        $pV = max(0, (int)$_POST["p_{$i}_V"]);
        $prorrog = isset($_POST["prorrog_{$i}"]) ? "on" : "off";
        $txt .= "$pL#$pV#$prorrog#\r\n";
    }
    // Guardamos de manera limpia las 3 decisiones del usuario:
    // clasificado_1 (Pasa Semi 1), clasificado_2 (Pasa Semi 2) y clasificado_3 (Campeón de la Gran Final)
    $txt .= ($_POST["clasificado_1"] ?? '') . "#\r\n";
    $txt .= ($_POST["clasificado_2"] ?? '') . "#\r\n";
    $txt .= ($_POST["clasificado_3"] ?? '') . "#\r\n";
    
    file_put_contents($archivo_semis, $txt);
    header("Location: Mundial2026.php?accio=semifinales"); 
    exit;
}
?>

<style>
    .contenedor-mundial { margin-left: 25ch; font-family: sans-serif; padding: 20px; }
    .fase-seccion { margin-top: 20px; border: 1px solid #ddd; padding: 15px; background: #f4f4f4; border-radius: 4px; }
    .cruces-fila { margin-bottom: 12px; font-size: 13px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px; }
    .equipo-txt { min-width: 140px; font-weight: bold; }
    .input-gol { width: 45px; text-align: center; font-weight: bold; padding: 3px; border: 1px solid #ccc; border-radius: 4px; }
    .gol-acierto { background-color: #e2f0d9; color: #385723; border: 2px solid #70ad47 !important; }
    .gol-fallo { background-color: #fce4d6; color: #c65911; border: 2px solid #c65911 !important; }
    .badge-pts { color: green; font-weight: bold; font-size: 11px; margin-left: 3px; }
</style>

<script>
function actualizarSiguiente(id, pref) {
    var sel = document.getElementsByName('clasificado_' + id)[0];
    var nom = (sel && sel.value) ? sel.value : "---";
    document.querySelectorAll('.label_' + pref + id).forEach(el => el.innerText = nom);
    document.querySelectorAll('.sel_' + pref + id).forEach(opt => { opt.text = nom; opt.value = nom; });
}
</script>

<div class="contenedor-mundial">
<form method='post'>
    <h2>Predicción de Fase Final (Desde Semifinales)</h2>

    <!-- SEMIFINALES -->
    <div class="fase-seccion">
        <h3>Semifinales</h3>
        <p style="font-size: 0.9em; color: #666;">Puntos: Goles exactos (+1 c/u) | Acierto Prórroga Sí/No (+1) | Clasificado a la Gran Final (+6)</p>
        
        <?php
        for ($i = 0; $i < 2; $i++) {
            $num = $i + 1;
            $eL = $partidos_semis[$i][0] ?? "---";
            $eV = $partidos_semis[$i][1] ?? "---";
            $resL = $partidos_semis[$i][2] ?? 'x';
            $resV = $partidos_semis[$i][3] ?? 'x';
            $resProrrog = $partidos_semis[$i][4] ?? 'off';
            
            $vL = $datos_goles_semis[$i][0] ?? 0;
            $vV = $datos_goles_semis[$i][1] ?? 0;
            $vProrrog = $datos_goles_semis[$i][2] ?? 'off';
            $sel = $user_clasificados[$num] ?? ''; // Carga quién pasa de cada semi (se usa internamente en el script)

            $claseL = $claseV = "";
            $pts_partido = 0;

            if ($resL !== 'x' && $resL !== '') {
                if ((int)$vL === (int)$resL) { $puntos_totales++; $pts_partido++; $claseL = "gol-acierto"; } else { $claseL = "gol-fallo"; }
                if ((int)$vV === (int)$resV) { $puntos_totales++; $pts_partido++; $claseV = "gol-acierto"; } else { $claseV = "gol-fallo"; }
                
                // Puntuación simétrica de Prórroga (+1 por acertar si hay o no)
                if ($vProrrog === $resProrrog) { 
                    $puntos_totales += 1; 
                    $pts_partido += 1; 
                }
            }

            echo "<div class='cruces-fila'>";
            echo "<span class='equipo-txt'>$eL</span>";
            echo "<input type='number' name='p_{$i}_L' value='$vL' class='input-gol $claseL' min='0'>";
            echo " - ";
            echo "<input type='number' name='p_{$i}_V' value='$vV' class='input-gol $claseV' min='0'>";
            echo "<span class='equipo-txt'>$eV</span>";
            
            $checked = ($vProrrog == 'on') ? 'checked' : '';
            echo " | <input type='checkbox' name='prorrog_{$i}' $checked> Prórroga";
            
            if ($pts_partido > 0) echo " <span class='badge-pts'>+$pts_partido pts</span>";

            echo " | Ganador: <select name='clasificado_$num' onchange=\"actualizarSiguiente($num, 'S')\">";
            echo "<option value=''>-</option>";
            echo "<option value='".htmlspecialchars($eL, ENT_QUOTES)."' ".($sel==$eL?'selected':'').">$eL</option>";
            echo "<option value='".htmlspecialchars($eV, ENT_QUOTES)."' ".($sel==$eV?'selected':'').">$eV</option>";
            echo "</select>";
            
            echo verificarEquipo($sel, $reales_final, 6, $puntos_totales);
            echo "</div>";
        }
        ?>
    </div>

    <!-- GRAN FINAL Y CAMPEÓN MUNDIAL -->
    <div style='margin-top:20px; border:2px solid #333; padding:15px; background:#fff; border-radius:4px;'>
        <h3>Gran Final</h3>
        <p style="font-size: 0.9em; color: #666;">Puntos: Acertar Campeón del Mundo (+7)</p>
        <?php $selF = $user_clasificados[3] ?? ''; // ID 3 (Campeón definitivo) ?>
        <div class="cruces-fila">
            Finalista 1 (<b class='label_S1'>-</b>) vs Finalista 2 (<b class='label_S2'>-</b>)
            | Ganador: <select name='clasificado_3'>
                <option value=''>-</option>
                <?php if($selF) echo "<option value='".htmlspecialchars($selF, ENT_QUOTES)."' selected>$selF</option>"; ?>
                <option value='' class='sel_S1'>-</option>
                <option value='' class='sel_S2'>-</option>
            </select>
            <?php echo verificarEquipo($selF, [$real_campeon], 7, $puntos_totales); ?>
        </div>
    </div>
    <!-- MENÚ FIJO INFERIOR -->
    <div style='height:120px;'></div>
    <div style='position:fixed; bottom:0; left:0; width:100%; background:#eee; padding:15px; text-align:center; border-top:2px solid #999; z-index:100;'>
        <strong>PUNTOS TOTALES ACUMULADOS FASE FINAL: <?php echo $puntos_totales; ?></strong> | 
        <input type='submit' name='boton' value='Guardar Cambios Semifinales' style='padding:10px 40px; font-weight:bold; cursor:pointer; background:#28a745; color:white; border:none; border-radius:4px;'>
    </div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){ 
    // Dispara la propagación en cadena inicial para enviar los ganadores seleccionados de Semis a la Gran Final
    for(var i = 1; i <= 2; i++) {
        actualizarSiguiente(i, 'S'); 
    }
});
</script>