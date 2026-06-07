<?php

$fileContent = file_get_contents('datos/grups.txt');

$fichero_resultados="datos/resultats.txt";
$g=fopen($fichero_resultados,"w");

// Limpiamos posibles etiquetas de fuente y dividimos por líneas
$lines = explode("\n", str_replace("", "", $fileContent));
$teams = array_filter(array_map('trim', $lines));

// Agrupamos los equipos de 4 en 4 (12 grupos en total)
$groups = array_chunk($teams, 4);
$alphabet = range('A', 'L');

foreach ($groups as $index => $group) {
    // Si el grupo no tiene 4 equipos, saltamos para evitar errores
    if (count($group) < 4) continue;

    $groupLetter = $alphabet[$index];
    // echo "--- Grupo $groupLetter ---\n"; // Opcional: para organizar visualmente

    // Definición de enfrentamientos según tu lógica:
    // 1. Primero vs Segundo
    // 2. Tercero vs Cuarto
    // 3. Primero vs Tercero
    // 4. Segundo vs Cuarto
    // 5. Primero vs Cuarto
    // 6. Segundo vs Tercero
    
    $matches = [
        [$group[0], $group[1]],
        [$group[2], $group[3]],
        [$group[0], $group[2]],
        [$group[1], $group[3]],
        [$group[0], $group[4] ?? $group[3]], // Ajuste por si acaso, aunque siempre son 4
        [$group[1], $group[2]]
    ];

    // Corregimos el índice 4 del array (el 5º partido es 1º vs 4º)
    $matches[4] = [$group[0], $group[3]]; 

    foreach ($matches as $match) {
        $linea = $match[0] . "#" . $match[1] . "#x#x####\r\n";
	fwrite($g, $linea);
    }
}
$linea="";
for ($i=0;$i<32;$i++)
{
	$linea="x#\r\n";
	fwrite($g, $linea);
}
	
for ($i=0; $i<16; $i++)
{
	$linea="x#x#x#x####\r\n";
	fwrite($g, $linea);
}

for ($i=0;$i<16;$i++)
{
	$linea="x#\r\n";
	fwrite($g, $linea);
}
	
for ($i=0; $i<8; $i++)
{
	$linea="x#x#x#x####\r\n";
	fwrite($g, $linea);
}

for ($i=0; $i<4; $i++)
{
	$linea="x#x#x#x####\r\n";
	fwrite($g, $linea);
}

for ($i=0; $i<2; $i++)
{
	$linea="x#x#x#x####\r\n";
	fwrite($g, $linea);
}
$linea="x#x#x#x####\r\n";
fwrite($g, $linea);

$r=fclose($g);
