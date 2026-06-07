<?php
// ENTRADA_RESULTATS.PHP - Versión 12 Grupos + Dieciseisavos + Relleno Clasificados
session_start();

$fichero_resultados = "datos/resultats.txt";
$fichero_grups = "datos/grups.txt";

// 1. CARGAR LISTA DE EQUIPOS PARA LOS DESPLEGABLES DE ELIMINATORIAS
$equipos = file($fichero_grups, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// 2. LEER DATOS ACTUALES
$fichero = file($fichero_resultados, FILE_IGNORE_NEW_LINES);
$partidos = [];
foreach ($fichero as $i => $d) { 
    $partidos[$i] = explode('#', $d); 
}

// 3. GUARDAR DATOS SI SE PULSA EL BOTÓN
if (isset($_POST['boton']) && $_POST['boton'] == 'Grabar') {
    $lineas = [];
    $lineas[0] = "\r\n"; 

    // GRUPOS (12 grupos x 6 partidos = 72 partidos. Índices 1 al 72)
    for ($i = 1; $i <= 72; $i++) {
        $eL = $partidos[$i][0] ?? '';
        $eV = $partidos[$i][1] ?? '';
        $gL = $_POST["g_{$i}_L"];
        $gV = $_POST["g_{$i}_V"];
        $p4 = $partidos[$i][4] ?? '';
        $p5 = $partidos[$i][5] ?? '';
        $p6 = $partidos[$i][6] ?? '';
        
        $lineas[$i] = "$eL#$eV#$gL#$gV#$p4#$p5#$p6#\r\n";
    }

    // RELLENO CLASIFICADOS GRUPOS (32 clasificados para Dieciseisavos. Índices 73 al 104)
    for ($i = 73; $i <= 104; $i++) {
        $lineas[$i] = ($_POST["clasificado_g_".($i-73)] ?? ($partidos[$i][0] ?? 'x')) . "#\r\n";
    }

    // DIECISEISAVOS (16 partidos: Índices 105 al 120)
    for ($i = 0; $i < 16; $i++) {
        $idx = 105 + $i;
        $lineas[$idx] = $_POST["d_{$i}_L_nom"]."#".$_POST["d_{$i}_V_nom"]."#".$_POST["d_{$i}_L_goles"]."#".$_POST["d_{$i}_V_goles"]."#".(isset($_POST["d_{$i}_pen"]) ? "on" : "off")."#".$_POST["d_{$i}_ganador"]."#\r\n";
    }

    // OCTAVOS (8 partidos: Índices 121 al 128)
    for ($i = 0; $i < 8; $i++) {
        $idx = 121 + $i;
        $lineas[$idx] = $_POST["o_{$i}_L_nom"]."#".$_POST["o_{$i}_V_nom"]."#".$_POST["o_{$i}_L_goles"]."#".$_POST["o_{$i}_V_goles"]."#".(isset($_POST["o_{$i}_pen"]) ? "on" : "off")."#".$_POST["o_{$i}_ganador"]."#\r\n";
    }

    // CUARTOS (4 partidos: Índices 129 al 132)
    for ($i = 0; $i < 4; $i++) {
        $idx = 129 + $i;
        $lineas[$idx] = $_POST["c_{$i}_L_nom"]."#".$_POST["c_{$i}_V_nom"]."#".$_POST["c_{$i}_L_goles"]."#".$_POST["c_{$i}_V_goles"]."#".(isset($_POST["c_{$i}_pen"]) ? "on" : "off")."#".$_POST["c_{$i}_ganador"]."#\r\n";
    }

    // SEMIFINALES (2 partidos: Índices 133 al 134)
    for ($i = 0; $i < 2; $i++) {
        $idx = 133 + $i;
        $lineas[$idx] = $_POST["s_{$i}_L_nom"]."#".$_POST["s_{$i}_V_nom"]."#".$_POST["s_{$i}_L_goles"]."#".$_POST["s_{$i}_V_goles"]."#".(isset($_POST["s_{$i}_pen"]) ? "on" : "off")."#".$_POST["s_{$i}_ganador"]."#\r\n";
    }

    // FINAL (1 partido: Índice 135)
    $lineas[135] = $_POST["f_L_nom"]."#".$_POST["f_V_nom"]."#".$_POST["f_L_goles"]."#".$_POST["f_V_goles"]."#".(isset($_POST["f_pen"]) ? "on" : "off")."#".$_POST["f_campeon"]."#\r\n";

    ksort($lineas); 
    file_put_contents($fichero_resultados, implode("", $lineas));
    
    // Recargar array tras guardar
    $fichero = file($fichero_resultados, FILE_IGNORE_NEW_LINES);
    foreach ($fichero as $i => $d) { $partidos[$i] = explode('#', $d); }
    echo "<p style='background:green; color:white; padding:10px; font-weight:bold;'>Resultados Guardados con Éxito.</p>";
}

// Función para generar selects de eliminatorias
function selectorEquipo($name, $selected, $equipos) {
    echo "<select name='$name' class='input-nom'><option value='x'>x</option>";
    foreach ($equipos as $e) {
        $e = trim($e);
        $sel = ($e == $selected) ? "selected" : "";
        echo "<option value='$e' $sel>$e</option>";
    }
    echo "</select>";
}
?>

<style>
    body { font-family: 'Segoe UI', sans-serif; background: #eee; padding: 30px; padding-bottom: 100px; }
    .fase-card { background: #fff; padding: 20px; margin-bottom: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .grid-grupos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
    .partido-fila { display: flex; align-items: center; gap: 8px; padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
    .grupo-label-eq { min-width: 100px; display: inline-block; font-weight: 500; }
    .input-gol { width: 40px; text-align: center; font-weight: bold; padding: 2px; }
    .input-nom { width: 130px; font-size: 12px; padding: 2px; }
    h2 { background: #333; color: #fff; padding: 10px; border-radius: 4px; margin-top: 0; font-size: 18px; }
    .btn-save { position: fixed; bottom: 20px; right: 20px; padding: 15px 45px; background: #28a745; color: #fff; border: none; border-radius: 50px; cursor: pointer; font-weight: bold; font-size: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 1000; }
    .btn-save:hover { background: #218838; }
    .grid-clasificados { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
</style>

<form method="post">
    <h1>Administración de Resultados Reales (Formato Ampliado)</h1>

    <div class="fase-card">
        <h2>Fase de Grupos (72 Partidos)</h2>
        <div class="grid-grupos">
            <?php for ($i = 1; $i <= 72; $i++): 
                $eqL = $partidos[$i][0] ?? "Eq L $i";
                $eqV = $partidos[$i][1] ?? "Eq V $i";
                $golL = $partidos[$i][2] ?? 'x';
                $golV = $partidos[$i][3] ?? 'x';
            ?>
                <div class="partido-fila">
                    <span style="color:#888; width: 3ch; display:inline-block;"><?php echo $i; ?></span>
                    <span class="grupo-label-eq" style="text-align: right;"><?php echo $eqL; ?></span>
                    <input type="text" name="g_<?php echo $i; ?>_L" value="<?php echo $golL; ?>" class="input-gol">
                    <span>-</span>
                    <input type="text" name="g_<?php echo $i; ?>_V" value="<?php echo $golV; ?>" class="input-gol">
                    <span class="grupo-label-eq"><?php echo $eqV; ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="fase-card" style="background: #fdfdfd; border: 1px dashed #bbb;">
        <h2>Equipos Clasificados de Grupos (Control Interno / Relleno)</h2>
        <div class="grid-clasificados">
            <?php for ($i = 0; $i < 32; $i++): $val = $partidos[73 + $i][0] ?? 'x'; ?>
                <div style="font-size: 12px; padding: 4px;">
                    <label style="color:#666;">Plaza <?php echo ($i+1); ?>:</label><br>
                    <?php selectorEquipo("clasificado_g_{$i}", $val, $equipos); ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="fase-card">
        <h2>Dieciseisavos de Final</h2>
        <?php for ($i = 0; $i < 16; $i++): $p = $partidos[105 + $i]; ?>
            <div class="partido-fila">
                <span style="color:#888; width: 3ch; display:inline-block;">D<?php echo ($i+1); ?></span>
                <?php selectorEquipo("d_{$i}_L_nom", $p[0] ?? '', $equipos); ?>
                <input type="text" name="d_<?php echo $i; ?>_L_goles" value="<?php echo $p[2] ?? 'x'; ?>" class="input-gol">
                <input type="text" name="d_<?php echo $i; ?>_V_goles" value="<?php echo $p[3] ?? 'x'; ?>" class="input-gol">
                <?php selectorEquipo("d_{$i}_V_nom", $p[1] ?? '', $equipos); ?>
                | Pen: <input type="checkbox" name="d_<?php echo $i; ?>_pen" <?php echo (($p[4] ?? '') == 'on' ? 'checked' : ''); ?>>
                | Ganador: <?php selectorEquipo("d_{$i}_ganador", $p[5] ?? '', $equipos); ?>
            </div>
        <?php endfor; ?>
    </div>

    <div class="fase-card">
        <h2>Octavos de Final</h2>
        <?php for ($i = 0; $i < 8; $i++): $p = $partidos[121 + $i]; ?>
            <div class="partido-fila">
                <span style="color:#888; width: 3ch; display:inline-block;">O<?php echo ($i+1); ?></span>
                <?php selectorEquipo("o_{$i}_L_nom", $p[0] ?? '', $equipos); ?>
                <input type="text" name="o_<?php echo $i; ?>_L_goles" value="<?php echo $p[2] ?? 'x'; ?>" class="input-gol">
                <input type="text" name="o_<?php echo $i; ?>_V_goles" value="<?php echo $p[3] ?? 'x'; ?>" class="input-gol">
                <?php selectorEquipo("o_{$i}_V_nom", $p[1] ?? '', $equipos); ?>
                | Pen: <input type="checkbox" name="o_<?php echo $i; ?>_pen" <?php echo (($p[4] ?? '') == 'on' ? 'checked' : ''); ?>>
                | Ganador: <?php selectorEquipo("o_{$i}_ganador", $p[5] ?? '', $equipos); ?>
            </div>
        <?php endfor; ?>
    </div>

    <div class="fase-card">
        <h2>Cuartos de Final</h2>
        <?php for ($i = 0; $i < 4; $i++): $p = $partidos[129 + $i]; ?>
            <div class="partido-fila">
                <span style="color:#888; width: 3ch; display:inline-block;">C<?php echo ($i+1); ?></span>
                <?php selectorEquipo("c_{$i}_L_nom", $p[0] ?? '', $equipos); ?>
                <input type="text" name="c_<?php echo $i; ?>_L_goles" value="<?php echo $p[2] ?? 'x'; ?>" class="input-gol">
                <input type="text" name="c_<?php echo $i; ?>_V_goles" value="<?php echo $p[3] ?? 'x'; ?>" class="input-gol">
                <?php selectorEquipo("c_{$i}_V_nom", $p[1] ?? '', $equipos); ?>
                | Pen: <input type="checkbox" name="c_<?php echo $i; ?>_pen" <?php echo (($p[4] ?? '') == 'on' ? 'checked' : ''); ?>>
                | Ganador: <?php selectorEquipo("c_{$i}_ganador", $p[5] ?? '', $equipos); ?>
            </div>
        <?php endfor; ?>
    </div>

    <div class="fase-card">
        <h2>Semifinales</h2>
        <?php for ($i = 0; $i < 2; $i++): $p = $partidos[133 + $i]; ?>
            <div class="partido-fila">
                <span style="color:#888; width: 3ch; display:inline-block;">S<?php echo ($i+1); ?></span>
                <?php selectorEquipo("s_{$i}_L_nom", $p[0] ?? '', $equipos); ?>
                <input type="text" name="s_<?php echo $i; ?>_L_goles" value="<?php echo $p[2] ?? 'x'; ?>" class="input-gol">
                <input type="text" name="s_<?php echo $i; ?>_V_goles" value="<?php echo $p[3] ?? 'x'; ?>" class="input-gol">
                <?php selectorEquipo("s_{$i}_V_nom", $p[1] ?? '', $equipos); ?>
                | Pen: <input type="checkbox" name="s_<?php echo $i; ?>_pen" <?php echo (($p[4] ?? '') == 'on' ? 'checked' : ''); ?>>
                | Ganador: <?php selectorEquipo("s_{$i}_ganador", $p[5] ?? '', $equipos); ?>
            </div>
        <?php endfor; ?>
    </div>

    <div class="fase-card" style="border: 3px solid #ffc107;">
        <h2>Gran Final</h2>
        <?php $p = $partidos[135]; ?>
        <div class="partido-fila">
            <?php selectorEquipo("f_L_nom", $p[0] ?? '', $equipos); ?>
            <input type="text" name="f_L_goles" value="<?php echo $p[2] ?? 'x'; ?>" class="input-gol">
            <input type="text" name="f_V_goles" value="<?php echo $p[3] ?? 'x'; ?>" class="input-gol">
            <?php selectorEquipo("f_V_nom", $p[1] ?? '', $equipos); ?>
            | Pen: <input type="checkbox" name="f_pen" <?php echo (($p[4] ?? '') == 'on' ? 'checked' : ''); ?>>
            | <b>CAMPEÓN:</b> <?php selectorEquipo("f_campeon", $p[5] ?? '', $equipos); ?>
        </div>
    </div>

    <input type="submit" name="boton" value="Grabar" class="btn-save">
</form>