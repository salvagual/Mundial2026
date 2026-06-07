<?php
// REGISTRARSE.PHP - Versión Coherente con Mundial2026 0.3

function prepara($nom)
{
$sense['À']='a';
$sense['Á']='a';
$sense['Â']='a';
$sense['Ã']='a';
$sense['Ä']='a';
$sense['Å']='a';
$sense['Æ']='a';
$sense['à']='a';
$sense['á']='a';
$sense['â']='a';
$sense['ã']='a';
$sense['ä']='a';
$sense['å']='a';
$sense['æ']='a';
$sense['Ç']='c';
$sense['È']='e';
$sense['É']='e';
$sense['Ê']='e';
$sense['Ë']='e';
$sense['Ì']='i';
$sense['Í']='i';
$sense['Î']='i';
$sense['Ï']='i';
$sense['Ð']='d';
$sense['Ñ']='n';
$sense['Ò']='o';
$sense['Ó']='o';
$sense['Ô']='o';
$sense['Õ']='o';
$sense['Ö']='o';
$sense['Ø']='o';
$sense['Ù']='u';
$sense['Ú']='u';
$sense['Û']='u';
$sense['Ü']='u';
$sense['Ý']='y';
$sense['Þ']='b';
$sense['ß']='s';
$sense['ç']='c';
$sense['è']='e';
$sense['é']='e';
$sense['ê']='e';
$sense['ë']='e';
$sense['ì']='i';
$sense['í']='i';
$sense['î']='i';
$sense['ï']='i';
$sense['ð']='d';
$sense['ñ']='n';
$sense['ò']='o';
$sense['ó']='o';
$sense['ô']='o';
$sense['õ']='o';
$sense['ö']='o';
$sense['ø']='o';
$sense['ù']='u';
$sense['ú']='u';
$sense['û']='u';
$sense['ý']='y';
$sense['ý']='y';
$sense['þ']='b';
$sense['ÿ']='y';
$sense['&']='y';
$sense['#340;']='r';
$sense['&#341;']='r';
$sortida = strtr($nom, $sense);
	$sortida = strtolower($sortida);
	return $sortida;
}

function registrarse($trad, $error)
{
    // Cambiamos la acción a Mundial2026.php para mantener el flujo del controlador
    echo "<FORM ACTION='Mundial2026.php?accio=registrarse' METHOD=post NAME=alta>\n";
    
    // Eliminamos estilos absolutos para usar el CSS mejorado
    echo "<div class='form-registro'>";
    
    echo "<div class='campo'>";
    echo "<label>" . htmlspecialchars($trad["Nombre usuario:"]) . "</label>";
    $valUs = $error ? htmlspecialchars($_POST['usuario'] ?? '') : '';
    echo "<input name='usuario' size='15' value='$valUs' required>";
    echo "</div>";

    echo "<div class='campo'>";
    echo "<label>Password:</label>";
    echo "<input type='password' name='pass1' size='15' required>";
    echo "</div>";

    echo "<div class='campo'>";
    echo "<label>" . htmlspecialchars($trad['comprobación del Password:']) . "</label>";
    echo "<input type='password' name='pass2' size='15' required>";
    echo "</div>";

    echo "<div class='campo'>";
    echo "<label>" . htmlspecialchars($trad['Liga']) . ":</label>";
    $valLliga = $error ? htmlspecialchars($_POST['lliga'] ?? '') : '';
    echo "<input name='lliga' size='30' value='$valLliga'>";
    echo "</div>";

    echo "<div class='acciones'>";
    echo "<input type='submit' name='boton' value='" . htmlspecialchars($trad['registrarse']) . "'>";
    echo "</div>";
    
    echo "</div></FORM>\n";
}

// 1. Carga de usuarios (Solo nombres para validación de duplicados)
$usuarios_registrados = [];
if (file_exists('datos/usuaris.txt')) {
    $lineas = file('datos/usuaris.txt');
    foreach ($lineas as $l) {
        $partes = explode('#', trim($l));
        if (!empty($partes[0])) $usuarios_registrados[] = $partes[0];
    }
}

if (!isset($_POST['boton'])) {
    registrarse($trad, FALSE);
} else {
    $us = prepara($_POST['usuario']);
    
    if (in_array($us, $usuarios_registrados)) {
        echo "<p class='error'>" . $trad["¡OTRO USUARIO YA HA ESCOGIDO ESTE NOMBRE!"] . "</p>";
        registrarse($trad, TRUE);
    } 
    elseif ($_POST['pass1'] !== $_POST['pass2'] || empty($_POST['pass1'])) {
        $msg = empty($_POST['pass1']) ? $trad["ĄEL PASSWORD NO PUEDE ESTAR VACIO!"] : $trad["ĄLOS PASSWORDS NO COINCIDEN!"];
        echo "<p class='error'>$msg</p>";
        registrarse($trad, TRUE);
    } 
    else {
        // 2. Creación de archivos del usuario
        $ficheros = [
            "" => 32, // Ajustado a la estructura del bucle original
            "_vuitens" => 23,
            "_quarts" => 11,
            "_semis" => 5,
            "_final" => 2
        ];

        foreach ($ficheros as $suf => $lineas) {
            $ruta = "datos/{$us}{$suf}.txt";
            if (!file_exists($ruta)) {
                $contenido = str_repeat("##" . PHP_EOL, $lineas);
                file_put_contents($ruta, $contenido);
            }
        }

        // 3. Hashing de contraseña y guardado
        $password_hash = password_hash($_POST['pass1'], PASSWORD_DEFAULT);
        // Formato: usuario#hash#nombre_pantalla#liga#puntos...
        $linea_registro = "$us#$password_hash#{$_POST['usuario']}#{$_POST['lliga']}#0#0#0#0#0#0#\r\n";
        
        file_put_contents('datos/usuaris.txt', $linea_registro, FILE_APPEND);

        // 4. Gestión de ligas
        $lliga_txt = $_POST['lliga'] . "\r\n";
        $todas_lligues = file_exists('datos/lligues.txt') ? file('datos/lligues.txt') : [];
        if (!in_array($lliga_txt, $todas_lligues)) {
            file_put_contents('datos/lligues.txt', $lliga_txt, FILE_APPEND);
        }

        // 5. Autologin coherente
        $_SESSION['usuari'] = $us;
        $_SESSION['nom_real'] = $_POST['usuario'];
        $_SESSION['lliga'] = $_POST['lliga'];

        echo "<p class='exito'>¡Registro completado!</p>";
        include('grupos.php');
    }
}
?>