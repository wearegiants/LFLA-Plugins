<?php


$buffer = ob_get_contents(); 

ob_get_clean();
ob_start();

$buffer = str_replace('<h1>',"<h1 $h1style>", $buffer);
$buffer = str_replace('<h2>',"<br><h2 $h2style>", $buffer);
$buffer = str_replace('<h3>',"<br><h3 $h3style>", $buffer);
$buffer = str_replace('<p>',"<p $pstyle>", $buffer);

echo $buffer;