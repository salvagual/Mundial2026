<?php
// GRUPOS.PHP - Versión 5.5 (Cálculo Completo de Mejores Terceros en PHP + Puntuación Estricta)
if (!isset($_SESSION['usuari'])) {
    echo "<p class='error'>Debe iniciar sesión para ver esta página.</p>";
    return;
}

$us = $_SESSION['usuari'];
$puntos_totales = 0; 

// 1. FUNCIÓN DE VALIDACIÓN DE EQUIPOS
function verificarEquipo($nombreEquipo, $listaReales, $puntos, &$puntos_globales) {
    if (!$nombreEquipo || $nombreEquipo == "---" || empty($listaReales) || $nombreEquipo == 'x') return "";
    
    $limpiarVocablos = function($cadena) {
        $cadena = str_replace("\xEF\xBB\xBF", "", $cadena);
        return strtolower(trim($cadena));
    };

    $eq_clean = $limpiarVocablos($nombreEquipo);
    $reales_clean = array_map($limpiarVocablos, $listaReales);
    
    if (in_array($eq_clean, $reales_clean)) {
        $puntos_globales += $puntos;
        return " <img src='img/ok.png' width='15'> <span style='color:green; font-size:11px; font-weight:bold;'>+$puntos</span>";
    }
    return "";
}

// 2. LEER RESULTADOS REALES DINÁMICAMENTE
$partidos_base = []; 
$reales_dieciseisavos = []; 
$reales_octavos = []; 
$reales_cuartos = []; 
$reales_semis = []; 
$reales_final = []; 
$real_campeon = "";

if (file_exists('datos/resultats.txt')) {
    $lineas_res = file('datos/resultats.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    for ($i = 0; $i < 72; $i++) {
        if (isset($lineas_res[$i])) $partidos_base[] = explode('#', trim($lineas_res[$i]));
    }

    $indice_cruce = 1;
    foreach ($lineas_res as $indice_l => $linea) {
        if ($indice_l < 72) continue;
        
        $partes_cruce = explode('#', trim($linea));
        
        if (count($partes_cruce) >= 6) {
            $ganador_real = trim($partes_cruce[5]);
            if ($ganador_real != '' && $ganador_real != 'x') {
                if ($indice_cruce <= 16) $reales_octavos[] = $ganador_real;
                elseif ($indice_cruce <= 24) $reales_cuartos[] = $ganador_real;
                elseif ($indice_cruce <= 28) $reales_semis[] = $ganador_real;
                elseif ($indice_cruce <= 30) $reales_final[] = $ganador_real;
                elseif ($indice_cruce == 31) $real_campeon = $ganador_real;
            }
            $indice_cruce++;
        } else {
            $equipo_bolsa = trim($partes_cruce[0]);
            if ($equipo_bolsa != '' && $equipo_bolsa != 'x') {
                $reales_dieciseisavos[] = $equipo_bolsa;
            }
        }
    }
}

// 3. CARGAR PRONÓSTICOS GUARDADOS DEL USUARIO
$datos_user_goles = []; 
$user_clasificados = [];

if (file_exists("datos/$us.txt")) {
    $lineas_u = file("datos/$us.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Líneas 0 a 71: Goles de grupos
    for ($i = 0; $i < 72; $i++) {
        if (isset($lineas_u[$i])) {
            $datos_user_goles[$i] = explode('#', trim($lineas_u[$i]));
        } else {
            $datos_user_goles[$i] = ['', ''];
        }
    }
    
    // Líneas 72 a 134: Mapeo lineal del árbol (63 registros en total)
    for ($i = 1; $i <= 63; $i++) {
        $idx_txt = 71 + $i;
        if (isset($lineas_u[$idx_txt])) {
            $user_clasificados[$i] = explode('#', trim($lineas_u[$idx_txt]))[0] ?? '';
        } else {
            $user_clasificados[$i] = '';
        }
    }
}

// 4. MATRIZ DE REGLAS DE MEJORES TERCEROS
$matriz_js_reglas = [];
if (file_exists('datos/mejores_terceros.txt')) {
    $lineas_mt = file('datos/mejores_terceros.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas_mt as $l_mt) {
        $columnas = explode('#', trim($l_mt, '#'));
        if (count($columnas) >= 13) {
            $key_comb = $columnas[0].$columnas[1].$columnas[2].$columnas[3];
            $matriz_js_reglas[$key_comb] = [
                '1A' => $columnas[5], '1B' => $columnas[6], '1D' => $columnas[7], 
                '1E' => $columnas[8], '1G' => $columnas[9], '1I' => $columnas[10], 
                '1K' => $columnas[11], '1L' => $columnas[12]
            ];
        }
    }
}

// 5. SIMULACIÓN COMPLETA DE GRUPOS EN PHP
$pronos_pos = [];
$todos_los_terceros_php = [];
$letras_v = ['A','B','C','D','E','F','G','H','I','J','K','L'];

foreach ($letras_v as $idx_g => $l) {
    if (!isset($partidos_base[$idx_g * 6])) continue;
    $base = $idx_g * 6;
    $equipos_g = [$partidos_base[$base][0], $partidos_base[$base][1], $partidos_base[$base+1][0], $partidos_base[$base+1][1]];
    $orden = [[0,1],[2,3],[0,2],[3,1],[3,0],[1,2]];
    $pts = [0,0,0,0]; $gf = [0,0,0,0]; $gc = [0,0,0,0];
    
    for ($i=0; $i<6; $i++) {
        $gL = max(0, (int)($datos_user_goles[$base + $i][0] ?? 0)); 
        $gV = max(0, (int)($datos_user_goles[$base + $i][1] ?? 0));
        $gf[$orden[$i][0]] += $gL; $gc[$orden[$i][0]] += $gV; $gf[$orden[$i][1]] += $gV; $gc[$orden[$i][1]] += $gL;
        if ($gL > $gV) $pts[$orden[$i][0]] += 3; elseif ($gL < $gV) $pts[$orden[$i][1]] += 3; else { $pts[$orden[$i][0]]++; $pts[$orden[$i][1]]++; }
    }
    
    $tabla = [];
    for($i=0; $i<4; $i++) $tabla[] = ['n'=>$equipos_g[$i], 'p'=>$pts[$i], 'd'=>$gf[$i]-$gc[$i], 'f'=>$gf[$i], 'g'=>$l];
    usort($tabla, function($a,$b){ return $b['p']-$a['p'] ?: $b['d']-$a['d'] ?: $b['f']-$a['f']; });
    
    $pronos_pos['1'.$l] = $tabla[0]['n']; 
    $pronos_pos['2'.$l] = $tabla[1]['n'];
    $todos_los_terceros_php[] = $tabla[2];
}

usort($todos_los_terceros_php, function($a,$b){ return $b['p']-$a['p'] ?: $b['d']-$a['d'] ?: $b['f']-$a['f']; });
$mejores8_php = array_slice($todos_los_terceros_php, 0, 8);
$letras_eliminadas_php = [];
foreach ($todos_los_terceros_php as $t) {
    if (!in_array($t, $mejores8_php, true)) {
        $letras_eliminadas_php[] = $t['g'];
    }
}
sort($letras_eliminadas_php);
$comb_eliminados_php = implode('', $letras_eliminadas_php);

if (isset($matriz_js_reglas[$comb_eliminados_php])) {
    $mapeo_actual_php = $matriz_js_reglas[$comb_eliminados_php];
    foreach ($mapeo_actual_php as $lider => $letra_tercero) {
        foreach ($todos_los_terceros_php as $tercer_eq) {
            if ($tercer_eq['g'] === $letra_tercero) {
                $pronos_pos['31' . $lider] = $tercer_eq['n'];
                break;
            }
        }
    }
}

// 6. GUARDAR CAMBIOS POST - ESTRUCTURA PLANA SIN DESPLAZAMIENTOS
if (isset($_POST['boton'])) {
    $nuevas_lineas = [];

    // A. Fase de Grupos: 72 partidos secuenciales (Líneas 0 a 71)
    $idx_partido = 0;
    foreach ($letras_v as $l) {
        for ($p = 0; $p < 6; $p++) {
            $gL = max(0, (int)$_POST["g_{$l}_p{$p}_l"]); 
            $gV = max(0, (int)$_POST["g_{$l}_p{$p}_v"]);
            $nuevas_lineas[$idx_partido] = "{$gL}#{$gV}#";
            $idx_partido++;
        }
    }

    // B. Los 32 clasificados automáticos de Grupos (Líneas 72 a 103)
    $cruces_dieciseisavos = [
        ['1E','311E'],['1I','311I'],['2A','2B'],['1F','2C'],
        ['2K','2L'],['1H','2J'],['1D','311D'],['1G','311G'],
        ['1C','2F'],['2E','2I'],['1A','311A'],['1L','311L'],
        ['1J','2H'],['2D','2G'],['1B','311B'],['1K','311K']
    ];

    $idx_txt = 72;
    foreach ($cruces_dieciseisavos as $c) {
        $eq1 = $pronos_pos[$c[0]] ?? '';
        $eq2 = $pronos_pos[$c[1]] ?? '';
        
        $nuevas_lineas[$idx_txt] = $eq1 . "#";
        $idx_txt++;
        $nuevas_lineas[$idx_txt] = $eq2 . "#";
        $idx_txt++;
    }

    // C. Tus selecciones manuales ordenadas cronológicamente

    // Líneas 104 a 119: Ganadores de Dieciseisavos (Selectores 1 al 16) -> Pasan a Octavos
    for ($i = 1; $i <= 16; $i++) {
        $nuevas_lineas[] = ($_POST["clasificado_$i"] ?? '') . "#";
    }

    // Líneas 120 a 127: Ganadores de Octavos (Selectores 17 al 24) -> Pasan a Cuartos
    for ($i = 17; $i <= 24; $i++) {
        $nuevas_lineas[] = ($_POST["clasificado_$i"] ?? '') . "#";
    }

    // Líneas 128 a 131: Ganadores de Cuartos (Selectores 25 al 28) -> Pasan a Semis
    for ($i = 25; $i <= 28; $i++) {
        $nuevas_lineas[] = ($_POST["clasificado_$i"] ?? '') . "#";
    }

    // Líneas 132 y 133: Ganadores de Semis (Selectores 29 y 30) -> Pasan a la Final
    for ($i = 29; $i <= 30; $i++) {
        $nuevas_lineas[] = ($_POST["clasificado_$i"] ?? '') . "#";
    }

    // Línea 134: Ganador de la Final (Selector 32) -> ¡CAMPEÓN DEL MUNDO!
    $nuevas_lineas[] = ($_POST["clasificado_32"] ?? '') . "#";

    // Guardar el archivo plano con el finalizador de línea estándar de Windows
    $contenido = implode("\r\n", $nuevas_lineas) . "\r\n";
    file_put_contents("datos/$us.txt", $contenido);
    
    header("Location: Mundial2026.php?accio=fase_grups"); 
    exit;
}

?>

<style>
    .contenedor-mundial { margin-left: 25ch; }
    .input-goles { width: 5ch; text-align: center; padding: 2px; } 
    .pts-total { font-weight: bold; color: #28a745; margin-left: 4px; font-size: 11px; }
</style>

<script>
const reglasTerceros = <?php echo json_encode($matriz_js_reglas); ?>;
const letrasGrupos = ['A','B','C','D','E','F','G','H','I','J','K','L'];
var pronosPosGlobal = {};

function validarNegativo(input) { if (input.value < 0) input.value = 0; }

function recalcularTodoElTorneo() {
    var todosLosTerceros = [];

    letrasGrupos.forEach(function(g) {
        var nombres = [];
        for(var e=0; e<4; e++) nombres.push(document.getElementById('nom_'+g+'_'+e).innerText);
        
        var orden = [[0,1],[2,3],[0,2],[3,1],[3,0],[1,2]];
        var pts=[0,0,0,0], gf=[0,0,0,0], gc=[0,0,0,0];
        
        for(var i=0; i<6; i++){
            var inputL = document.getElementsByName('g_'+g+'_p'+i+'_l')[0];
            var inputV = document.getElementsByName('g_'+g+'_p'+i+'_v')[0];
            if(inputL && inputV) {
                validarNegativo(inputL); validarNegativo(inputV);
                var gL = parseInt(inputL.value)||0; var gV = parseInt(inputV.value)||0;
                gf[orden[i][0]]+=gL; gc[orden[i][0]]+=gV; gf[orden[i][1]]+=gV; gc[orden[i][1]]+=gL;
                if(gL>gV) pts[orden[i][0]]+=3; else if(gL<gV) pts[orden[i][1]]+=3; else { pts[orden[i][0]]++; pts[orden[i][1]]++; }
            }
        }
        
        var tabla = [];
        for(var i=0; i<4; i++) tabla.push({n: nombres[i], p: pts[i], f: gf[i], c: gc[i], d: gf[i]-gc[i], g: g});
        tabla.sort((a,b) => b.p - a.p || b.d - a.d || b.f - a.f);
        
        for(var i=0; i<4; i++){
            var r_nom = document.getElementById('r_'+g+'_'+i+'_nom');
            if(r_nom) {
                document.getElementById('r_'+g+'_'+i+'_nom').innerText = tabla[i].n;
                document.getElementById('r_'+g+'_'+i+'_pts').innerText = tabla[i].p;
                document.getElementById('r_'+g+'_'+i+'_gf').innerText = tabla[i].f;
                document.getElementById('r_'+g+'_'+i+'_gc').innerText = tabla[i].c;
                document.getElementById('r_'+g+'_'+i+'_dg').innerText = tabla[i].d;
            }
        }
        
        pronosPosGlobal['1'+g] = tabla[0].n;
        pronosPosGlobal['2'+g] = tabla[1].n;
        todosLosTerceros.push(tabla[2]);
    });

    todosLosTerceros.sort((a,b) => b.p - a.p || b.d - a.d || b.f - a.f);
    var mejores8 = todosLosTerceros.slice(0, 8);
    var letrasEliminados = [];
    todosLosTerceros.forEach(function(t) {
        if(!mejores8.includes(t)) { letrasEliminados.push(t.g); }
    });
    letrasEliminados.sort();
    var combEliminados = letrasEliminados.join('');

    letrasGrupos.forEach(function(g) {
        delete pronosPosGlobal['311A']; delete pronosPosGlobal['311B'];
        delete pronosPosGlobal['311D']; delete pronosPosGlobal['311E'];
        delete pronosPosGlobal['311G']; delete pronosPosGlobal['311I'];
        delete pronosPosGlobal['311K']; delete pronosPosGlobal['311L'];
    });

    if(reglasTerceros[combEliminados]) {
        var mapeoActual = reglasTerceros[combEliminados];
        Object.keys(mapeoActual).forEach(function(lider) {
            var letraGrupoTercero = mapeoActual[lider];
            var tercerEquipoEncontrado = todosLosTerceros.find(t => t.g === letraGrupoTercero);
            if(tercerEquipoEncontrado) {
                pronosPosGlobal['31'+lider] = tercerEquipoEncontrado.n;
            }
        });
    }

    Object.keys(pronosPosGlobal).forEach(function(pos) {
        actualizarLabels(pos, pronosPosGlobal[pos]);
    });

    for(var i=1; i<=30; i++) actualizarSiguiente(i, (i<17?'D':(i<25?'O':(i<29?'C':'S')))); 
    actualizarSiguiente(32, 'G');
}

function actualizarLabels(pos, nom) {
    document.querySelectorAll('.label_'+pos).forEach(el => el.innerText = nom || "-");
    document.querySelectorAll('.sel_'+pos).forEach(opt => { 
        opt.text = nom || "-"; 
        opt.value = nom || ""; 
    });
}

function actualizarSiguiente(id, pref) {
    var sel = document.getElementsByName('clasificado_'+id)[0];
    var nom = (sel && sel.value) ? sel.value : "---";
    document.querySelectorAll('.label_'+pref+id).forEach(el => el.innerText = nom);
    document.querySelectorAll('.sel_'+pref+id).forEach(opt => { opt.text = nom; opt.value = nom; });
}
</script>

<div class="contenedor-mundial">
<form method='post'>
<div style='display:flex; flex-wrap:wrap; gap:10px;'>
<?php
foreach ($letras_v as $idx_g => $l) {
    if (!isset($partidos_base[$idx_g * 6])) continue;
    echo "<div style='border:1px solid #ccc; padding:8px; width:380px; background:#fff;'><strong>GRUPO $l</strong>";
    $base = $idx_g * 6;
    $equipos_g = [$partidos_base[$base][0], $partidos_base[$base][1], $partidos_base[$base+1][0], $partidos_base[$base+1][1]];
    foreach($equipos_g as $ei => $enom) echo "<span id='nom_{$l}_$ei' style='display:none;'>$enom</span>";

    for ($p=0; $p<6; $p++) {
        $idx = $base + $p;
        $vL = $datos_user_goles[$idx][0] ?? 0; $vV = $datos_user_goles[$idx][1] ?? 0;
        $rL = $partidos_base[$idx][2] ?? 'x'; $rV = $partidos_base[$idx][3] ?? 'x';
        $stL = $stV = $icon = $puntos_txt = ""; $pts_p = 0;
        
        if ($rL !== 'x') {
            if ((int)$vL === (int)$rL) { $puntos_totales++; $pts_p++; }
            if ((int)$vV === (int)$rV) { $puntos_totales++; $pts_p++; }
            if (($vL>$vV?1:($vL<$vV?2:0)) == ($rL>$rV?1:($rL<$rV?2:0))) { $icon = "ok.png"; $puntos_totales += 3; $pts_p += 3; } else { $icon = "no_ok.png"; }
            $stL = ((int)$vL === (int)$rL) ? "style='color:green; font-weight:bold;'" : "style='color:red;'";
            $stV = ((int)$vV === (int)$rV) ? "style='color:green; font-weight:bold;'" : "style='color:red;'";
            $puntos_txt = "<span class='pts-total'>+$pts_p</span>";
        }
        echo "<div style='font-size:11px; margin-bottom:2px;'>".$partidos_base[$idx][0]." <input type='number' name='g_{$l}_p{$p}_l' value='$vL' $stL class='input-goles' min='0' oninput=\"validarNegativo(this)\" onchange=\"recalcularTodoElTorneo()\"> - <input type='number' name='g_{$l}_p{$p}_v' value='$vV' $stV class='input-goles' min='0' oninput=\"validarNegativo(this)\" onchange=\"recalcularTodoElTorneo()\"> ".$partidos_base[$idx][1];
        if($icon) echo " <img src='img/$icon' width='12'> $puntos_txt</div>"; else echo "</div>";
    }
    echo "<table width='100%' style='font-size:10px; margin-top:5px; border-collapse:collapse;' border='1' bordercolor='#eee'><tr style='background:#eee;'><th>Equipo</th><th>Pts</th><th>GF</th><th>GC</th><th>DG</th></tr>";
    for($i=0;$i<4;$i++) echo "<tr><td id='r_{$l}_{$i}_nom'>-</td><td id='r_{$l}_{$i}_pts' align='center'>0</td><td id='r_{$l}_{$i}_gf' align='center'>0</td><td id='r_{$l}_{$i}_gc' align='center'>0</td><td id='r_{$l}_{$i}_dg' align='center'>0</td></tr>";
    echo "</table></div>";
}
?>
</div>

<?php
// CONFIGURACIÓN CENTRALIZADA DE CRUCES EN CADENA
$fases = [
    ['tit' => 'Dieciseisavos', 'ini' => 1, 'pts' => 4, 'lista' => $reales_dieciseisavos, 'pref' => 'D', 'cruces' => [
        ['1E','311E'],['1I','311I'],['2A','2B'],['1F','2C'],
        ['2K','2L'],['1H','2J'],['1D','311D'],['1G','311G'],
        ['1C','2F'],['2E','2I'],['1A','311A'],['1L','311L'],
        ['1J','2H'],['2D','2G'],['1B','311B'],['1K','311K']
    ]],
    ['tit' => 'Octavos', 'ini' => 17, 'pts' => 5, 'lista' => $reales_octavos, 'pref' => 'O', 'cruces' => [
        ['D1','D2'],['D3','D4'],['D5','D6'],['D7','D8'],
        ['D9','D10'],['D11','D12'],['D13','D14'],['D15','D16']
    ]],
    ['tit' => 'Cuartos', 'ini' => 25, 'pts' => 6, 'lista' => $reales_cuartos, 'pref' => 'C', 'cruces' => [
        ['O17','O18'],['O19','O20'],['O21','O22'],['O23','O24']
    ]],
    ['tit' => 'Semis', 'ini' => 29, 'pts' => 7, 'lista' => $reales_semis, 'pref' => 'S', 'cruces' => [
        ['C25','C26'],['C27','C28']
    ]]
];

$array_busqueda = [];
for($i=1;$i<=16;$i++) $array_busqueda[] = 'D'.$i;
for($i=17;$i<=24;$i++) $array_busqueda[] = 'O'.$i;
for($i=25;$i<=28;$i++) $array_busqueda[] = 'C'.$i;

foreach ($fases as $f) {
    echo "<div style='margin-top:20px; border:1px solid #ddd; padding:10px; background:#f4f4f4;'><h3>{$f['tit']}</h3>";
    foreach ($f['cruces'] as $i => $c) {
        $num = $f['ini'] + $i; 
        
        // AJUSTE CRUCIAL: El selector visual sigue llamándose 'clasificado_$num', 
        // pero los datos recuperados del fichero ahora están 32 posiciones desplazados.
        $num_fichero = $num + 32;
        $sel = $user_clasificados[$num_fichero] ?? ''; 
        
        if ($f['tit'] == 'Dieciseisavos') {
            $n1 = $pronos_pos[$c[0]] ?? '';
            $n2 = $pronos_pos[$c[1]] ?? '';
        } else {
            $idx1 = array_search($c[0], $array_busqueda);
            $idx2 = array_search($c[1], $array_busqueda);
            
            // Los equipos que compiten se buscan en base a los ganadores de la ronda anterior guardados en el fichero (+32)
            $n1 = ($idx1 !== false) ? ($user_clasificados[($idx1 + 1) + 32] ?? '') : '';
            $n2 = ($idx2 !== false) ? ($user_clasificados[($idx2 + 1) + 32] ?? '') : '';
        }

        echo "<div style='margin-bottom:8px;'>#$num <b class='label_{$c[0]}'>{$c[0]}</b>" . verificarEquipo($n1, $f['lista'], $f['pts'], $puntos_totales) . " vs <b class='label_{$c[1]}'>{$c[1]}</b>" . verificarEquipo($n2, $f['lista'], $f['pts'], $puntos_totales);
        echo " | Ganador: <select name='clasificado_$num' onchange=\"actualizarSiguiente($num, '{$f['pref']}')\"><option value=''>-</option>";
        if($sel) echo "<option value='".htmlspecialchars($sel, ENT_QUOTES)."' selected>$sel</option>";
        echo "<option value='' class='sel_{$c[0]}'>-</option><option value='' class='sel_{$c[1]}'>-</option></select></div>";
    }
    echo "</div>";
}

// GRAN FINAL Y CAMPEÓN MUNDIAL REAJUSTADO
echo "<div style='margin-top:20px; border:2px solid #333; padding:10px; background:#fff;'><h3>Gran Final</h3>";
// Los finalistas se buscan en las posiciones de las semis reajustadas (29+32=61 y 30+32=62)
$f1 = $user_clasificados[61] ?? ''; $f2 = $user_clasificados[62] ?? '';
echo "<div><b class='label_S29'>-</b>".verificarEquipo($f1, $reales_final, 8, $puntos_totales)." vs <b class='label_S30'>-</b>".verificarEquipo($f2, $reales_final, 8, $puntos_totales);
echo " | Ganador: <select name='clasificado_32' onchange=\"actualizarSiguiente(32, 'G')\"><option value=''>-</option>";

// El campeón real elegido está guardado en la posición 63 del array reajustado
$selF = $user_clasificados[63] ?? ''; 
if($selF) echo "<option value='".htmlspecialchars($selF, ENT_QUOTES)."' selected>$selF</option>";

echo "<option value='' class='sel_S29'>-</option><option value='' class='sel_S30'>-</option></select> ".verificarEquipo($selF, [$real_campeon], 10, $puntos_totales)."</div></div>";

echo "<div style='height:120px;'></div><div style='position:fixed; bottom:0; left:0; width:100%; background:#eee; padding:15px; text-align:center; border-top:2px solid #999; z-index:100;'>";
echo "<strong>PUNTOS TOTALES: $puntos_totales</strong> | <input type='submit' name='boton' value='Guardar Cambios' style='padding:10px 40px;'></div></form></div>";

echo "<script>document.addEventListener('DOMContentLoaded', function(){ recalcularTodoElTorneo(); });</script>";
?>