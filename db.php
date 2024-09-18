<?php
$con=mysqli_connect('localhost','root','');
$check=mysqli_select_db($con,'location_saver');
if(mysqli_error($con)){
print_r('<b style="background:white">'.mysqli_error($con).'</b>');
$errorText=mysqli_error($con).', DateTime '.date('d-m-Y H:i:s').'\n';
$myfile = fopen("log.txt", "a");
fwrite($myfile, $errorText);
fclose($myfile);
exit();
}
?>