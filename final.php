<?php
// FINAL.PHP - Versión 1.0 (Predicción directa de la Gran Final con Prórroga Sí/No y Campeón)
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
$partido_final = []; 
$real_campeon = "";

if (file_exists('datos/resultats.txt')) {
    $lineas_res = file('datos/resultats.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // En tu resultats.txt, el partido de la Gran Final se encuentra 
    // exactamente en la línea 134 (índice 134).
    if (isset($lineas_res[134])) {
        $partido_final = explode('#', trim($lineas_res[134]));
        // El ganador real anotado en el componente [5] es el Campeón del Mundo
        if (isset($partido_final[5]) && $partido_final[5] != '' && $partido_final[5] != 'x') {
            $real_campeon = $partido_final[5];
        }
    }
}

// 3. CARGAR PRONÓSTICOS DEL USUARIO (_final.txt)
$archivo_final = "datos/" . $us . "_final.txt";
$datos_goles_final = [];
$user_campeon = "";

if (file_exists($archivo_final)) {
    $lineas_u = file($archivo_final, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Línea 0: Goles y prórroga del usuario para la Final
    if (isset($lineas_u[0])) {
        $datos_goles_final = explode('#', trim($lineas_u[0]));
    }
    // Línea 1: El equipo elegido por el usuario como Campeón
    if (isset($lineas_u[1])) {
        $partes_c = explode('#', trim($lineas_u[1]));
        $user_campeon = $partes_c[0] ?? '';
    }
}

// 4. GUARDAR CAMBIOS POST
if (isset($_POST['boton'])) {
    $txt = "";
    // Guardamos los goles y prórroga de la Final (Línea 0)
    $pL = max(0, (int)$_POST["p_0_L"]);
    $pV = max(0, (int)$_POST["p_0_V"]);
    $prorrog = isset($_POST["prorrog_0"]) ? "on" : "off";
    $txt .= "$pL#$pV#$prorrog#\r\n";
    
    // Guardamos el Campeón seleccionado (Línea 1)
    $txt .= ($_POST["clasificado_campeon"] ?? '') . "#\r\n";
    
    file_put_contents($archivo_final, $txt);
    header("Location: Mundial2026.php?accio=final"); 
    exit;
}
?>

<style>
    .contenedor-mundial { margin-left: 25ch; font-family: sans-serif; padding: 20px; }
    .fase-seccion { margin-top: 20px; border: 1px solid #ddd; padding: 15px; background: #f4f4f4; border-radius: 4px; }
    .cruces-fila { margin-bottom: 12px; font-size: 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; }
    .equipo-txt { min-width: 150px; font-weight: bold; font-size: 15px; }
    .input-gol { width: 50px; text-align: center; font-weight: bold; padding: 4px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
    .gol-acierto { background-color: #e2f0d9; color: #385723; border: 2px solid #70ad47 !important; }
    .gol-fallo { background-color: #fce4d6; color: #c65911; border: 2px solid #c65911 !important; }
    .badge-pts { color: green; font-weight: bold; font-size: 12px; margin-left: 5px; }
</style>

<div class="contenedor-mundial">
<form method='post'>
    <h2>Predicción del Partido de la Gran Final</h2>

    <!-- GRAN FINAL -->
    <div class="fase-seccion" style="border: 2px solid #222; background: #fff;">
        <h3 style="color: #c5a059; font-size: 1.5em; margin-top: 5px;">🏆 La Gran Final</h3>
        <p style="font-size: 0.9em; color: #666;">Puntos: Goles exactos (+1 c/u) | Acierto Prórroga Sí/No (+1) | Acertar Campeón del Mundo (+7)</p>
        
        <?php
        $eL = $partido_final[0] ?? "---";
        $eV = $partido_final[1] ?? "---";
        $resL = $partido_final[2] ?? 'x';
        $resV = $partido_final[3] ?? 'x';
        $resProrrog = $partido_final[4] ?? 'off';
        
        $vL = $datos_goles_final[0] ?? 0;
        $vV = $datos_goles_final[1] ?? 0;
        $vProrrog = $datos_goles_final[2] ?? 'off';

        $claseL = $claseV = "";
        $pts_partido = 0;

        // Comprobación de los puntos por goles y prórroga
        if ($resL !== 'x' && $resL !== '') {
            if ((int)$vL === (int)$resL) { $puntos_totales++; $pts_partido++; $claseL = "gol-acierto"; } else { $claseL = "gol-fallo"; }
            if ((int)$vV === (int)$resV) { $puntos_totales++; $pts_partido++; $claseV = "gol-acierto"; } else { $claseV = "gol-fallo"; }
            
            // Puntuación simétrica de la Prórroga (+1 punto si acierta el escenario exacto)
            if ($vProrrog === $resProrrog) { 
                $puntos_totales += 1; 
                $pts_partido += 1; 
            }
        }

        echo "<div class='cruces-fila' style='margin-top: 15px;'>";
        echo "<span class='equipo-txt' style='text-align: right; padding-right: 10px;'>$eL</span>";
        echo "<input type='number' name='p_0_L' value='$vL' class='input-gol $claseL' min='0'>";
        echo " <b style='font-size:16px;'>-</b> ";
        echo "<input type='number' name='p_0_V' value='$vV' class='input-gol $claseV' min='0'>";
        echo "<span class='equipo-txt' style='padding-left: 10px;'>$eV</span>";
        
        $checked = ($vProrrog == 'on') ? 'checked' : '';
        echo " | <label style='cursor:pointer;'><input type='checkbox' name='prorrog_0' $checked> Prórroga</label>";
        
        if ($pts_partido > 0) echo " <span class='badge-pts'>+$pts_partido pts</span>";
        echo "</div>";
        ?>

        <!-- CORONACIÓN DEL CAMPEÓN -->
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px; border: 1px dashed #c5a059;">
            <strong style="font-size: 1.1em;">🥇 Campeón del Mundo:</strong>
            <select name='clasificado_campeon' style="padding: 5px; font-weight: bold; font-size: 14px; margin-left: 10px;">
                <option value=''>- Seleccionar Campeón -</option>
                <option value='<?php echo htmlspecialchars($eL, ENT_QUOTES); ?>' <?php echo ($user_campeon == $eL ? 'selected' : ''); ?>><?php echo $eL; ?></option>
                <option value='<?php echo htmlspecialchars($eV, ENT_QUOTES); ?>' <?php echo ($user_campeon == $eV ? 'selected' : ''); ?>><?php echo $eV; ?></option>
            </select>
            <?php 
            // Valida los 7 puntos si el usuario acierta el campeón real de la competición
            echo verificarEquipo($user_campeon, [$real_campeon], 7, $puntos_totales); 
            ?>
        </div>
    </div>

    <!-- MENÚ FIJO INFERIOR DE PUNTUACIÓN -->
    <div style='height:120px;'></div>
    <div style='position:fixed; bottom:0; left:0; width:100%; background:#eee; padding:15px; text-align:center; border-top:2px solid #999; z-index:100;'>
        <strong>PUNTOS TOTALES ACUMULADOS EN LA FINAL: <?php echo $puntos_totales; ?></strong> | 
        <input type='submit' name='boton' value='Guardar Pronóstico de la Final' style='padding:10px 40px; font-weight:bold; cursor:pointer; background:#d4af37; color:#000; border:1px solid #997d29; border-radius:4px;'>
    </div>
</form>
</div>