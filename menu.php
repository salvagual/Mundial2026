<?php
//MENU.PHP
//Versión 0.1 3.III.10

$menus=array();

$datos_menu="menu.txt";
$filas_menu=file($datos_menu);
foreach($filas_menu as $i=>$fila)
{
	$datos_fila[$i]=explode('|',$fila);
	$menus[$datos_fila[$i][0]]=$datos_fila[$i][1];
//	$menus[$trad[$datos_fila[$i][0]]]=$datos_fila[$i][1];
}

$accio='';
if (isset($_GET['accio']))
{
	$accio='&accio='.$_GET['accio'];
}
echo "<DIV style='position:absolute; top:20; left:10;'>\n";
$menu=0;
foreach($menus as $titulo_menu=>$accion)
{
	$menu++;
	echo "<DIV class='opcio' style='margin-top:20px'>";
	echo "<a href='".$accion."' class='opcio2'>";
	echo $titulo_menu."\n";
	echo "</a></DIV>";
}
echo "</DIV>";
echo "<DIV style='position:absolute; top:15; left:10;'>\n";
echo "<a href='Mundial2026.php?idioma=ca".$accio."'>\n";
echo "<img src='img/ca.png' width=35 height=25>";
echo '</a>';
//echo "<DIV style='position:absolute; top:30; left:50;'>\n";
echo "<a href='Mundial2026.php?idioma=es".$accio."'>\n";
echo "<img src='img/es.png' width=35 height=25>";
echo '</a></DIV>';
?>