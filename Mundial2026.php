<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mundial2026.PHP - Versión 2026 Adaptada
session_start();

// 1. Gestión de Idioma
if (!isset($_SESSION['idioma'])) {
    $_SESSION['idioma'] = 'es';
}

if (isset($_GET['idioma'])) {
    $lang = $_GET['idioma'];
    if ($lang === 'ca' || $lang === 'es') {
        $_SESSION['idioma'] = $lang;
    }
}
$idioma = $_SESSION['idioma'];

// 2. Procesamiento de Login
$usuario_autenticado = false;
$nom_usuari = "";
$lliga = "";

if (isset($_POST['usuario'], $_POST['password'])) {
    $us_intent = trim($_POST['usuario']);
    $pw_intent = $_POST['password'];

    if (file_exists('datos/usuaris.txt')) {
        $usuarios_db = file('datos/usuaris.txt');
        foreach ($usuarios_db as $linea) {
            $datos = explode('#', trim($linea));
            if ($datos[0] === $us_intent && password_verify($pw_intent, $datos[1])) {
                $_SESSION['usuari'] = $datos[0];
                $_SESSION['nom_real'] = $datos[2];
                $_SESSION['lliga'] = $datos[3];
                $usuario_autenticado = true;
                break;
            }
        }
    }
}

if (isset($_SESSION['usuari'])) {
    $usuario_autenticado = true;
    $nom_usuari = $_SESSION['nom_real'] ?? $_SESSION['usuari']; 
}

// 3. Cargar Traducciones
$fidioma = ($idioma === 'ca') ? 'traduccions/catala.txt' : 'traduccions/castella.txt';
$trad = [];
if (file_exists($fidioma)) {
    $vidioma = file($fidioma);
    foreach($vidioma as $t) {
        $tr = explode('#', trim($t));
        if (isset($tr[1])) $trad[$tr[0]] = $tr[1];
    }
}

// 4. Control de Acceso y Enrutado (Whitelist actualizada para 2026)
$accio = $_GET['accio'] ?? 'presentacio';
$paginas_publicas = ['presentacio', 'registrarse', 'classificacio', 'porres_grups', 'porres_eliminatories'];

if (!$usuario_autenticado && !in_array($accio, $paginas_publicas)) {
    $accio = 'presentacio';
}

$mapa_ficheros = [
    'presentacio' => "presentacio_{$idioma}.php",
    'registrarse' => 'registrarse.php',
    'fase_grups'  => 'grupos.php',
    'setzens'     => 'dieciseisavos.php', // NUEVA RONDA 2026
    'vuitens'     => 'octavos.php',
    'quarts'      => 'cuartos.php',
    'semifinals'  => 'semis.php',
    'final'       => 'final.php',
    'classificacio' => 'clasificacion.php',
    'classificacio_lliga' => 'clasificacion_lliga.php',
    'porres_grups' => 'porras_grupos.php',
    'porres_grups_lliga' => 'porras_grupos_lliga.php',
    'porres_eliminatories' => 'porras_eliminatorias.php',
    'porres_eliminatories_lliga' => 'porras_eliminatorias_lliga.php',
    'crear_lliga' => 'crear_lliga.php',
    'unirse_lliga' => 'unirse_lliga.php',
    'sortirse_lliga' => 'sortirse_lliga.php',
    'missatge' => 'missatge.php'
];

$fichero = $mapa_ficheros[$accio] ?? "presentacio_{$idioma}.php";
$titulo_web = $trad['Presentación'] ?? "Mundial 2026";

// 5. Salida HTML
echo '<!DOCTYPE html>
<html lang="'.$idioma.'">
<head>
    <meta charset="UTF-8">
    <title>'.htmlspecialchars($titulo_web).'</title>
    <link rel="stylesheet" href="Mundial2026.css">
</head>
<body>';

if ($usuario_autenticado) {
    echo "<div class='bienvenida_usuario' style='margin-left: 20%'>";
    echo htmlspecialchars(($trad["bienvenido "] ?? "Bienvenido/a ") . $nom_usuari, ENT_QUOTES, 'UTF-8');
    echo "</div>";
}

echo "<div class='menu'>";
include ('menu.php');
echo "</div>";

echo "<div class='separacio'></div>";

echo "<div class='centre' style='min-height: 100%;'>";
if (file_exists($fichero)) {
    include ($fichero);
} else {
    echo "<p class='error'>Error: El archivo solicitado no existe ($fichero).</p>";
}
echo "</div>
</body>
</html>";
?>