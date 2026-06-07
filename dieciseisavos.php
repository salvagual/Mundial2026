<?php
// DIECISEISAVOS.PHP - Versión 1.0 (Adaptado para el formato de 48 equipos con Prórroga Sí/No)
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
$partidos_dieciseisavos = []; 
$reales_octavos = [];
$reales_cuartos = []; 
$reales_semis = []; 
$reales_final = []; 
$real_campeon = "";

if (file_exists('datos/resultats.txt')) {
    $lineas_res = file('datos/resultats.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // En tu resultats.txt, los 16 partidos reales de Dieciseisavos empiezan justo después de los 72 de grupos 
    // y los 32 de la bolsa de clasificados planos. Es decir, líneas 104 a 119 (índices 104 al 119).
    for ($i = 104; $i < 120; $i++) {
        if (isset($lineas_res[$i])) {
            $partidos_dieciseisavos[] = explode('#', trim($lineas_res[$i]));
        }
    }

    // Clasificados Reales a Octavos (Ganadores de Dieciseisavos, líneas 104 a 119, componente [5])
    for ($i = 104; $i < 120; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_octavos[] = $partes[5];
            }
        }
    }

    // Clasificados Reales a Cuartos (Ganadores de Octavos, líneas 120 a 127, componente [5])
    for ($i = 120; $i < 128; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_cuartos[] = $partes[5];
            }
        }
    }

    // Clasificados Reales a Semis (Ganadores de Cuartos, líneas 128 a 131, componente [5])
    for ($i = 128; $i < 132; $i++) {
        if (isset($lineas_res[$i])) {
            $partes = explode('#', trim($lineas_res[$i]));
            if (isset($partes[5]) && $partes[5] != '' && $partes[5] != 'x') {
                $reales_semis[] = $partes[5];
            }
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

// 3. CARGAR PRONÓSTICOS DEL USUARIO (_setzens.txt)
$archivo_setzens = "datos/" . $us . "_setzens.txt";
$datos_goles_dieciseisavos = [];
$user_clasificados = [];

if (file_exists($archivo_setzens)) {
    $lineas_u = file($archivo_setzens, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas_u as $index => $l) {
        $partes = explode('#', trim($l));
        if ($index < 16) {
            // Primeras 16 líneas: Goles y prórroga del usuario para dieciseisavos
            $datos_goles_dieciseisavos[$index] = $partes;
        } else {
            // Siguientes líneas: Clasificados de las fases consecutivas (1 a 31)
            $user_clasificados[$index - 15] = $partes[0] ?? '';
        }
    }
}

// 4. GUARDAR CAMBIOS POST
if (isset($_POST['boton'])) {
    $txt = "";
    // Guardamos los goles y prórrogas de los 16 partidos
    for ($i = 0; $i < 16; $i++) {
        $pL = max(0, (int)$_POST["p_{$i}_L"]);
        $pV = max(0, (int)$_POST["p_{$i}_V"]);
        $prorrog = isset($_POST["prorrog_{$i}"]) ? "on" : "off";
        $txt .= "$pL#$pV#$prorrog#\r\n";
    }
    // Guardamos todos los selectores (1 al 31) sin desfases
    for ($i = 1; $i <= 31; $i++) {
        $txt .= ($_POST["clasificado_$i"] ?? '') . "#\r\n";
    }
    file_put_contents($archivo_setzens, $txt);
    header("Location: Mundial2026.php?accio=setzens"); 
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
    <h2>Predicción de Fase Eliminatoria Completa (Dieciseisavos)</h2>

    <div class="fase-seccion">
        <h3>Dieciseisavos de Final</h3>
        <p style="font-size: 0.9em; color: #666;">Puntos: Goles exactos (+1 c/u) | Acierto Prórroga Sí/No (+1) | Clasificado a Octavos (+4)</p>
        
        <?php
        for ($i = 0; $i < 16; $i++) {
            $num = $i + 1;
            $eL = $partidos_dieciseisavos[$i][0] ?? "---";
            $eV = $partidos_dieciseisavos[$i][1] ?? "---";
            $resL = $partidos_dieciseisavos[$i][2] ?? 'x';
            $resV = $partidos_dieciseisavos[$i][3] ?? 'x';
            $resProrrog = $partidos_dieciseisavos[$i][4] ?? 'off';
            
            $vL = $datos_goles_dieciseisavos[$i][0] ?? 0;
            $vV = $datos_goles_dieciseisavos[$i][1] ?? 0;
            $vProrrog = $datos_goles_dieciseisavos[$i][2] ?? 'off';
            $sel = $user_clasificados[$num] ?? '';

            $claseL = $claseV = "";
            $pts_partido = 0;

            if ($resL !== 'x' && $resL !== '') {
                if ((int)$vL === (int)$resL) { $puntos_totales++; $pts_partido++; $claseL = "gol-acierto"; } else { $claseL = "gol-fallo"; }
                if ((int)$vV === (int)$resV) { $puntos_totales++; $pts_partido++; $claseV = "gol-acierto"; } else { $claseV = "gol-fallo"; }
                
                // Puntuación simétrica de Prórroga
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

            echo " | Ganador: <select name='clasificado_$num' onchange=\"actualizarSiguiente($num, 'D')\">";
            echo "<option value=''>-</option>";
            echo "<option value='".htmlspecialchars($eL, ENT_QUOTES)."' ".($sel==$eL?'selected':'').">$eL</option>";
            echo "<option value='".htmlspecialchars($eV, ENT_QUOTES)."' ".($sel==$eV?'selected':'').">$eV</option>";
            echo "</select>";
            
            echo verificarEquipo($sel, $reales_octavos, 4, $puntos_totales);
            echo "</div>";
        }
        ?>
    </div>

    <?php
    $fases_dinamicas = [
        [
            'tit' => 'Octavos de Final', 
            'ini' => 17, 
            'pts' => 5, 
            'lista' => $reales_cuartos, 
            'pref' => 'O',
            'cruces' => [['D1','D2'], ['D3','D4'], ['D5','D6'], ['D7','D8'], ['D9','D10'], ['D11','D12'], ['D13','D14'], ['D15','D16']]
        ],
        [
            'tit' => 'Cuartos de Final', 
            'ini' => 25, 
            'pts' => 6, 
            'lista' => $reales_semis, 
            'pref' => 'C',
            'cruces' => [['O17','O18'], ['O19','O20'], ['O21','O22'], ['O23','O24']]
        ],
        [
            'tit' => 'Semifinales', 
            'ini' => 29, 
            'pts' => 7, 
            'lista' => $reales_final, 
            'pref' => 'S',
            'cruces' => [['C25','C26'], ['C27','C28']]
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
        <?php $selF = $user_clasificados[31] ?? ''; ?>
        <div class="cruces-fila">
            Finalista 1 (<b class='label_S29'>-</b>) vs Finalista 2 (<b class='label_S30'>-</b>)
            | Ganador: <select name='clasificado_31'>
                <option value=''>-</option>
                <?php if($selF) echo "<option value='".htmlspecialchars($selF, ENT_QUOTES)."' selected>$selF</option>"; ?>
                <option value='' class='sel_S29'>-</option>
                <option value='' class='sel_S30'>-</option>
            </select>
            <?php echo verificarEquipo($selF, [$real_campeon], 10, $puntos_totales); ?>
        </div>
    </div>

    <div style='height:120px;'></div>
    <div style='position:fixed; bottom:0; left:0; width:100%; background:#eee; padding:15px; text-align:center; border-top:2px solid #999; z-index:100;'>
        <strong>PUNTOS TOTALES ACUMULADOS FASE ELIMINATORIA: <?php echo $puntos_totales; ?></strong> | 
        <input type='submit' name='boton' value='Guardar Cambios Eliminatorias' style='padding:10px 40px; font-weight:bold; cursor:pointer; background:#28a745; color:white; border:none; border-radius:4px;'>
    </div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){ 
    // Dispara la propagación en cadena inicial al cargar la página para rellenar las etiquetas del árbol
    for(var i = 1; i <= 30; i++) {
        actualizarSiguiente(i, (i < 17 ? 'D' : (i < 25 ? 'O' : 'C'))); 
    }
});
</script>