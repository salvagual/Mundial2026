<?php
// PREPARAR_CLASSIFICACIO.PHP - Versión Adaptada al Fichero Único Plano
session_start();

$fichero_resultados = "datos/resultats.txt";
$fichero_usuaris    = "datos/usuaris.txt";
$fichero_puntos     = "datos/puntos_usuarios.txt";

if (!file_exists($fichero_resultados) || !file_exists($fichero_usuaris)) {
    die("<p style='color:red; font-weight:bold;'>Error: No se encuentran los archivos base necesarios.</p>");
}

function limpiezaAbsoluta($cadena) {
    $cadena = str_replace("\xEF\xBB\xBF", "", $cadena);
    $cadena = trim($cadena, "# \r\n\t\x0B\x00");
    $cadena = preg_replace('/[\x00-\x1F\x7F]/', '', $cadena);
    return $cadena;
}

// 1. CARGAR RESULTADOS REALES DEL MUNDIAL (resultats.txt)
$lineas_res_raw = file($fichero_resultados, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$partidos = [];
foreach ($lineas_res_raw as $linea) {
    $linea_limpia = limpiezaAbsoluta($linea);
    if ($linea_limpia !== '') {
        $partidos[] = explode('#', $linea_limpia);
    }
}

// 2. CARGAR LISTA DE JUGADORES
$usuarios_raw = file($fichero_usuaris, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$registro_puntos = [];
$registro_puntos[] = "usuario#puntos_grupos#puntos_setzens#puntos_octavos#puntos_cuartos#puntos_semis#puntos_final#puntos_totales#";

echo "<h2>Auditoría de Puntuaciones y Trazabilidad (Fichero Único)</h2>";

foreach ($usuarios_raw as $usr) {
    $partes_usuario = explode('#', $usr);
    $us = limpiezaAbsoluta($partes_usuario[0]);
    
    if (empty($us) || $us === "usuario") continue;

    echo "<div style='background:#fdfefe; border:2px solid #3498db; margin-bottom:40px; padding:20px; font-family:monospace; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>";
    echo "<h2 style='margin-top:0; color:#2980b9; border-bottom:2px solid #3498db; padding-bottom:10px;'>👤 AUDITORÍA DE PUNTOS: $us</h2>";

    $pts_grupos   = 0; // Goles de grupos (0-71)
    $pts_setzens  = 0; // Clasificados para Dieciseisavos (72-103)
    $pts_octavos  = 0; // Clasificados para Octavos (104-119)
    $pts_cuartos  = 0; // Clasificados para Cuartos (120-127)
    $pts_semis    = 0; // Clasificados para Semis (128-131)
    $pts_final    = 0; // Clasificados para la Final (132-133) y Vencedor (134)

    $fichero_usuario = "datos/" . $us . ".txt"; 
    if (file_exists($fichero_usuario)) {
        $lineas_u = file($fichero_usuario, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // ==========================================
        // A) FASE DE GRUPOS - PARTIDOS (Líneas 0 a 71)
        // ==========================================
        echo "<h4 style='color:#d35400; margin-bottom:5px;'>1. PARTIDOS DE FASE DE GRUPOS (Líneas 0 a 71)</h4>";
        echo "<table border='1' cellpadding='4' cellspacing='0' style='border-collapse:collapse; font-size:11px; width:100%; margin-bottom:15px;'>";
        echo "<tr style='background:#eee;'><th>Línea</th><th>Partido Real</th><th>Res Real</th><th>Pronóstico Usuario</th><th>Puntos Goles</th><th>Puntos 1X2</th><th>Total</th></tr>";

        $pts_goles_fase = 0;
        for ($i = 0; $i < 72; $i++) {
            if (isset($lineas_u[$i]) && isset($partidos[$i])) {
                $user_partido = explode('#', limpiezaAbsoluta($lineas_u[$i]));
                $real_partido = $partidos[$i];

                $eqL = $real_partido[0] ?? 'Local';
                $eqV = $real_partido[1] ?? 'Visitante';
                $resL = $real_partido[2] ?? 'x';
                $resV = $real_partido[3] ?? 'x';

                $vL = isset($user_partido[0]) ? limpiezaAbsoluta($user_partido[0]) : '';
                $vV = isset($user_partido[1]) ? limpiezaAbsoluta($user_partido[1]) : '';
                
                $pts_goles_partido = 0;
                $pts_1x2_partido = 0;

                if ($resL !== 'x' && $resL !== '' && $vL !== '' && $vV !== '') {
                    if ((int)$vL === (int)$resL) $pts_goles_partido += 1;
                    if ((int)$vV === (int)$resV) $pts_goles_partido += 1;

                    $signo_user = ((int)$vL > (int)$vV) ? '1' : (((int)$vL < (int)$vV) ? '2' : 'X');
                    $signo_real = ((int)$resL > (int)$resV) ? '1' : (((int)$resL < (int)$resV) ? '2' : 'X');
                    
                    if ($signo_user === $signo_real) $pts_1x2_partido = 3;
                }

                $tot_partido = $pts_goles_partido + $pts_1x2_partido;
                $pts_goles_fase += $tot_partido;

                $bg_row = ($tot_partido == 5) ? "#e2f0d9" : (($tot_partido > 0) ? "#fff2cc" : "#fce4d6");
                echo "<tr style='background:$bg_row;'><td>$i</td><td>$eqL vs $eqV</td><td>$resL - $resV</td><td>$vL - $vV</td><td>+$pts_goles_partido</td><td>+$pts_1x2_partido</td><td><strong>$tot_partido pts</strong></td></tr>";
            }
        }
        echo "</table>";
        echo "<p>Puntos por Goles/Signos en Grupos: <b>$pts_goles_fase pts</b></p>";

        // ==========================================
        // B) CLASIFICADOS PARA DIECISEISAVOS (Líneas 72 a 103) - 4 Puntos
        // ==========================================
        echo "<h4 style='color:#34495e; margin-bottom:5px;'>2. PREDICCIÓN: CLASIFICADOS A DIECISEISAVOS (Líneas 72 a 103)</h4>";
        $reales_setzens = [];
        // En resultats.txt, las líneas 72 a 103 contienen la bolsa de los 32 equipos reales
        for ($r = 72; $r < 104; $r++) {
            $eq = $partidos[$r][0] ?? '';
            if ($eq !== '' && $eq !== 'x') $reales_setzens[] = strtolower(limpiezaAbsoluta($eq));
        }

        echo "<div style='display:flex; flex-wrap:wrap; gap:5px; margin-bottom:15px;'>";
        for ($u = 72; $u < 104; $u++) {
            if (isset($lineas_u[$u])) {
                $eq_user = limpiezaAbsoluta(explode('#', $lineas_u[$u])[0] ?? '');
                $eq_user_lc = strtolower($eq_user);

                if (!empty($eq_user_lc) && in_array($eq_user_lc, $reales_setzens)) {
                    $pts_setzens += 4;
                    echo "<span style='background:#d4edda; color:#155724; padding:3px 8px; border:1px solid #c3e6cb; border-radius:3px; font-size:11px;'>✔️ $eq_user (+4)</span>";
                } else {
                    echo "<span style='background:#f8d7da; color:#721c24; padding:3px 8px; border:1px solid #f5c6cb; border-radius:3px; font-size:11px;'>❌ $eq_user (0)</span>";
                }
            }
        }
        echo "</div>";

        // ==========================================
        // C) CLASIFICADOS PARA OCTAVOS (Líneas 104 a 119) - 5 Puntos
        // ==========================================
        echo "<h4 style='color:#27ae60; margin-bottom:5px;'>3. PREDICCIÓN: VENCEDORES DIECISEISAVOS / CLASIFICADOS A OCTAVOS (Líneas 104 a 119)</h4>";
        $reales_octavos = [];
        // En resultats.txt, el ganador real del cruce de dieciseisavos está en la columna índice 5
        for ($r = 104; $r < 120; $r++) {
            $eq = $partidos[$r][5] ?? '';
            if ($eq !== '' && $eq !== 'x') $reales_octavos[] = strtolower(limpiezaAbsoluta($eq));
        }

        echo "<div style='display:flex; flex-wrap:wrap; gap:5px; margin-bottom:15px;'>";
        for ($u = 104; $u < 120; $u++) {
            if (isset($lineas_u[$u])) {
                $eq_user = limpiezaAbsoluta(explode('#', $lineas_u[$u])[0] ?? '');
                $eq_user_lc = strtolower($eq_user);

                if (!empty($eq_user_lc) && in_array($eq_user_lc, $reales_octavos)) {
                    $pts_octavos += 5;
                    echo "<span style='background:#d4edda; color:#155724; padding:3px 8px; border:1px solid #c3e6cb; border-radius:3px; font-size:11px;'>✔️ $eq_user (+5)</span>";
                } else {
                    echo "<span style='background:#f8d7da; color:#721c24; padding:3px 8px; border:1px solid #f5c6cb; border-radius:3px; font-size:11px;'>❌ $eq_user (0)</span>";
                }
            }
        }
        echo "</div>";

        // ==========================================
        // D) CLASIFICADOS PARA CUARTOS (Líneas 120 a 127) - 6 Puntos
        // ==========================================
        echo "<h4 style='color:#2980b9; margin-bottom:5px;'>4. PREDICCIÓN: VENCEDORES OCTAVOS / CLASIFICADOS A CUARTOS (Líneas 120 a 127)</h4>";
        $reales_cuartos = [];
        for ($r = 120; $r < 128; $r++) {
            $eq = $partidos[$r][5] ?? '';
            if ($eq !== '' && $eq !== 'x') $reales_cuartos[] = strtolower(limpiezaAbsoluta($eq));
        }

        echo "<div style='display:flex; flex-wrap:wrap; gap:5px; margin-bottom:15px;'>";
        for ($u = 120; $u < 128; $u++) {
            if (isset($lineas_u[$u])) {
                $eq_user = limpiezaAbsoluta(explode('#', $lineas_u[$u])[0] ?? '');
                $eq_user_lc = strtolower($eq_user);

                if (!empty($eq_user_lc) && in_array($eq_user_lc, $reales_cuartos)) {
                    $pts_cuartos += 6;
                    echo "<span style='background:#d4edda; color:#155724; padding:3px 8px; border:1px solid #c3e6cb; border-radius:3px; font-size:11px;'>✔️ $eq_user (+6)</span>";
                } else {
                    echo "<span style='background:#f8d7da; color:#721c24; padding:3px 8px; border:1px solid #f5c6cb; border-radius:3px; font-size:11px;'>❌ $eq_user (0)</span>";
                }
            }
        }
        echo "</div>";

        // ==========================================
        // E) CLASIFICADOS PARA SEMIFINALES (Líneas 128 a 131) - 7 Puntos
        // ==========================================
        echo "<h4 style='color:#8e44ad; margin-bottom:5px;'>5. PREDICCIÓN: VENCEDORES CUARTOS / CLASIFICADOS A SEMIS (Líneas 128 a 131)</h4>";
        $reales_semis = [];
        for ($r = 128; $r < 132; $r++) {
            $eq = $partidos[$r][5] ?? '';
            if ($eq !== '' && $eq !== 'x') $reales_semis[] = strtolower(limpiezaAbsoluta($eq));
        }

        echo "<div style='display:flex; flex-wrap:wrap; gap:5px; margin-bottom:15px;'>";
        for ($u = 128; $u < 132; $u++) {
            if (isset($lineas_u[$u])) {
                $eq_user = limpiezaAbsoluta(explode('#', $lineas_u[$u])[0] ?? '');
                $eq_user_lc = strtolower($eq_user);

                if (!empty($eq_user_lc) && in_array($eq_user_lc, $reales_semis)) {
                    $pts_semis += 7;
                    echo "<span style='background:#d4edda; color:#155724; padding:3px 8px; border:1px solid #c3e6cb; border-radius:3px; font-size:11px;'>✔️ $eq_user (+7)</span>";
                } else {
                    echo "<span style='background:#f8d7da; color:#721c24; padding:3px 8px; border:1px solid #f5c6cb; border-radius:3px; font-size:11px;'>❌ $eq_user (0)</span>";
                }
            }
        }
        echo "</div>";

        // ==========================================
        // F) CLASIFICADOS PARA LA FINAL (Líneas 132 y 133) - 8 Puntos
        // ==========================================
        echo "<h4 style='color:#e67e22; margin-bottom:5px;'>6. PREDICCIÓN: VENCEDORES SEMIS / CLASIFICADOS A LA FINAL (Líneas 132 y 133)</h4>";
        $reales_final = [];
        for ($r = 132; $r < 134; $r++) {
            $eq = $partidos[$r][5] ?? '';
            if ($eq !== '' && $eq !== 'x') $reales_final[] = strtolower(limpiezaAbsoluta($eq));
        }

        echo "<div style='display:flex; flex-wrap:wrap; gap:5px; margin-bottom:15px;'>";
        for ($u = 132; $u < 134; $u++) {
            if (isset($lineas_u[$u])) {
                $eq_user = limpiezaAbsoluta(explode('#', $lineas_u[$u])[0] ?? '');
                $eq_user_lc = strtolower($eq_user);

                if (!empty($eq_user_lc) && in_array($eq_user_lc, $reales_final)) {
                    $pts_final += 8;
                    echo "<span style='background:#d4edda; color:#155724; padding:3px 8px; border:1px solid #c3e6cb; border-radius:3px; font-size:11px;'>✔️ $eq_user (+8)</span>";
                } else {
                    echo "<span style='background:#f8d7da; color:#721c24; padding:3px 8px; border:1px solid #f5c6cb; border-radius:3px; font-size:11px;'>❌ $eq_user (0)</span>";
                }
            }
        }
        echo "</div>";

        // ==========================================
        // G) VENCEDOR / CAMPEÓN DEL MUNDO (Línea 134) - 10 Puntos
        // ==========================================
        echo "<h4 style='color:#c0392b; margin-bottom:5px;'>7. PREDICCIÓN: VENCEDOR / CAMPEÓN MUNDIAL (Línea 134)</h4>";
        if (isset($lineas_u[134])) {
            $vencedor_user = limpiezaAbsoluta(explode('#', $lineas_u[134])[0] ?? '');
            $campeon_real  = limpiezaAbsoluta($partidos[134][5] ?? '');

            if (!empty($campeon_real) && $campeon_real !== 'x' && strtolower($campeon_real) === strtolower($vencedor_user)) {
                $pts_final += 10; 
                echo "<div style='background:#d4edda; color:#155724; padding:8px; border:1px solid #c3e6cb; border-radius:3px; font-size:11px; display:inline-block;'>✔️ ¡Campeón Acertado!: $vencedor_user (+10 pts)</div>";
            } else {
                echo "<div style='background:#f8d7da; color:#721c24; padding:8px; border:1px solid #f5c6cb; border-radius:3px; font-size:11px; display:inline-block;'>❌ Campeón Erróneo: $vencedor_user (Real: $campeon_real) (0 pts)</div>";
            }
        }
    }

    $pts_totales = $pts_goles_fase + $pts_setzens + $pts_octavos + $pts_cuartos + $pts_semis + $pts_final;
    echo "<br><br><span style='font-size:14px; background:#2ecc71; color:white; padding:5px 12px; border-radius:4px; font-weight:bold;'>TOTAL GLOBAL AUDITADO: $pts_totales PUNTOS</span>";
    echo "</div>";

    $registro_puntos[] = "$us#$pts_goles_fase#$pts_setzens#$pts_octavos#$pts_cuartos#$pts_semis#$pts_final#$pts_totales#";
}

file_put_contents($fichero_puntos, implode("\r\n", $registro_puntos) . "\r\n");
?>