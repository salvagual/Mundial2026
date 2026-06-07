<?php
// OCTAVOS.PHP - Versión 5.2 (Sustituido Penaltis por Prórroga con Puntuación Simétrica de +1)
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

// 2. LEER RESULTADOS REALES (Desde resultats.txt adaptado al formato de 48 equipos)
$partidos_octavos = []; 
$reales_cuartos = []; 
$reales_semis = []; 
$reales_final = []; 
$real_campeon = "";

if (file_exists('datos/resultats.txt')) {
    $lineas_res = file('datos/resultats.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Buscar los 8 partidos reales de Octavos de Final (Líneas 120 a 127)
    for ($i = 120; $i < 128; $i++) {
        if (isset($lineas_res[$i])) {
            $partidos_octavos[] = explode('#', trim($lineas_res[$i]));
        }
    }

    // Clasificados Reales a Cuartos
    for ($i = 120; $i < 128; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_cuartos[] = $partes[5];
            }
        }
    }

    // Clasificados Reales a Semis
    for ($i = 128; $i < 132; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_semis[] = $partes[5];
            }
        }
    }

    // Clasificados Reales a la Final
    for ($i = 132; $i < 134; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_final[] = $partes[5];
            }
        }
    }

    // Vencedor Real / Campeón del Mundo
    if (isset($lineas_res[134])) {
        $partes = explode('#', trim($lineas_res[134]));
        if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
            $real_campeon = $partes[5];
        }
    }
}

// 3. CARGAR PRONÓSTICOS DEL USUARIO (_vuitens.txt)
$archivo_vuitens = "datos/" . $us . "_vuitens.txt";
$datos_goles_octavos = [];
$user_clasificados = [];

if (file_exists($archivo_vuitens)) {
    $lineas_u = file($archivo_vuitens, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas_u as $index => $l) {
        $partes = explode('#', trim($l));
        if ($index < 8) {
            // Primeras 8 líneas: Goles y prórroga/penaltis del usuario
            $datos_goles_octavos[$index] = $partes;
        } else {
            // Siguientes líneas: Clasificados de las fases eliminatorias consecutivas
            $user_clasificados[$index - 7] = $partes[0] ?? '';
        }
    }
}

// 4. GUARDAR CAMBIOS
if (isset($_POST['boton'])) {
    $txt = "";
    // Guardamos los goles y el estado del checkbox de Prórroga ('on' u 'off')
    for ($i = 0; $i < 8; $i++) {
        $pL = max(0, (int)$_POST["p_{$i}_L"]);
        $pV = max(0, (int)$_POST["p_{$i}_V"]);
        $prorrog = isset($_POST["prorrog_{$i}"]) ? "on" : "off";
        $txt .= "$pL#$pV#$prorrog#\r\n";
    }
    // Guardamos los desplegables de pronósticos clasificados (1 a 16)
    for ($i = 1; $i <= 16; $i++) {
        $txt .= ($_POST["clasificado_$i"] ?? '') . "#\r\n";
    }
    file_put_contents($archivo_vuitens, $txt);
    header("Location: Mundial2026.php?accio=vuitens"); 
    exit;
}
?>

<style>
    .contenedor-mundial { margin-left: 25ch; font-family: sans-serif; padding: 20px; }
    .fase-seccion { margin-top: 20px; border: 1px solid #ddd; padding: 15px; background: #f4f4f4; border-radius: 4px; }
    .cruces-fila { margin-bottom: 12px; font-size: 13px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px; }
    .equipo-txt { min-width: 130px; font-weight: bold; }
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
    <h2>Predicción de Fase Eliminatoria (Octavos)</h2>

    <div class="fase-seccion">
        <h3>Octavos de Final</h3>
        <p style="font-size: 0.9em; color: #666;">Puntos: Goles exactos (+1 c/u) | Acierto Prórroga Sí/No (+1) | Clasificado a Cuartos (+3)</p>
        
        <?php
        for ($i = 0; $i < 8; $i++) {
            $num = $i + 1;
            $eL = $partidos_octavos[$i][0] ?? "---";
            $eV = $partidos_octavos[$i][1] ?? "---";
            $resL = $partidos_octavos[$i][2] ?? 'x';
            $resV = $partidos_octavos[$i][3] ?? 'x';
            $resProrrog = $partidos_octavos[$i][4] ?? 'off'; // Lee 'on' o 'off' del archivo real
            
            $vL = $datos_goles_octavos[$i][0] ?? 0;
            $vV = $datos_goles_octavos[$i][1] ?? 0;
            $vProrrog = $datos_goles_octavos[$i][2] ?? 'off'; // 'on' o 'off' del usuario
            $sel = $user_clasificados[$num] ?? '';

            $claseL = $claseV = "";
            $pts_partido = 0;

            // Comprobación de puntos si hay resultados reales en el archivo
            if ($resL !== 'x' && $resL !== '') {
                // Puntos por goles exactos
                if ((int)$vL === (int)$resL) { $puntos_totales++; $pts_partido++; $claseL = "gol-acierto"; } else { $claseL = "gol-fallo"; }
                if ((int)$vV === (int)$resV) { $puntos_totales++; $pts_partido++; $claseV = "gol-acierto"; } else { $claseV = "gol-fallo"; }
                
                // NUEVA LÓGICA SIMÉTRICA: Suma 1 punto si acierta el escenario exacto de prórroga (tanto si SÍ hay como si NO hay)
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

            echo " | Ganador: <select name='clasificado_$num' onchange=\"actualizarSiguiente($num, 'O')\">";
            echo "<option value=''>-</option>";
            echo "<option value='".htmlspecialchars($eL, ENT_QUOTES)."' ".($sel==$eL?'selected':'').">$eL</option>";
            echo "<option value='".htmlspecialchars($eV, ENT_QUOTES)."' ".($sel==$eV?'selected':'').">$eV</option>";
            echo "</select>";
            
            // Valida los puntos del clasificado real
            echo verificarEquipo($sel, $reales_cuartos, 3, $puntos_totales);
            echo "</div>";
        }
        ?>
    </div>

    <?php
    $fases_dinamicas = [
        [
            'tit' => 'Cuartos de Final', 
            'ini' => 9, 
            'pts' => 5, 
            'lista' => $reales_semis, 
            'pref' => 'C',
            'cruces' => [['O1','O2'], ['O3','O4'], ['O5','O6'], ['O7','O8']]
        ],
        [
            'tit' => 'Semifinales', 
            'ini' => 13, 
            'pts' => 6, 
            'lista' => $reales_final, 
            'pref' => 'S',
            'cruces' => [['C9','C10'], ['C11','C12']]
        ]
    ];

    foreach ($fases_dinamicas as $f) {
        echo "<div class='fase-seccion'><h3>{$f['tit']}</h3>";
        foreach ($f['cruces'] as $i => $c) {
            $num = $f['ini'] + $i; 
            $sel = $user_clasificados[$num] ?? '';
            
            echo "<div class='cruces-fila'>#$num Ganador #{$c[0]} (<b class='label_{$c[0]}'>-</b>) vs Ganador #{$c[1]} (<b class='label_{$c[1]}'>-</b>)";
            echo " | Ganador: <select name='clasificado_$num' onchange=\"actualizarSiguiente($num, '{$f['pref']}')\">";
            echo "<option value=''>-</option>";
            if($sel) echo "<option value='".htmlspecialchars($sel, ENT_QUOTES)."' selected>$sel</option>";
            echo "<option value='' class='sel_{$c[0]}'>-</option>";
            echo "<option value='' class='sel_{$c[1]}'>-</option>";
            echo "</select>";
            
            echo verificarEquipo($sel, $f['lista'], $f['pts'], $puntos_totales);
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <div style='margin-top:20px; border:2px solid #333; padding:15px; background:#fff; border-radius:4px;'>
        <h3>Gran Final</h3>
        <?php $selF = $user_clasificados[16] ?? ''; ?>
        <div class="cruces-fila">
            Finalista 1 (<b class='label_S13'>-</b>) vs Finalista 2 (<b class='label_S14'>-</b>)
            | Ganador: <select name='clasificado_16'>
                <option value=''>-</option>
                <?php if($selF) echo "<option value='".htmlspecialchars($selF, ENT_QUOTES)."' selected>$selF</option>"; ?>
                <option value='' class='sel_S13'>-</option>
                <option value='' class='sel_S14'>-</option>
            </select>
            <?php echo verificarEquipo($selF, [$real_campeon], 7, $puntos_totales); ?>
        </div>
    </div>

    <div style='height:120px;'></div>
    <div style='position:fixed; bottom:0; left:0; width:100%; background:#eee; padding:15px; text-align:center; border-top:2px solid #999; z-index:100;'>
        <strong>PUNTOS TOTALES ACUMULADOS FASE FINAL: <?php echo $puntos_totales; ?></strong> | 
        <input type='submit' name='boton' value='Guardar Cambios Fase Final' style='padding:10px 40px; font-weight:bold; cursor:pointer; background:#28a745; color:white; border:none; border-radius:4px;'>
    </div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){ 
    for(var i = 1; i <= 14; i++) {
        actualizarSiguiente(i, (i < 9 ? 'O' : (i < 13 ? 'C' : 'S'))); 
    }
});
</script>