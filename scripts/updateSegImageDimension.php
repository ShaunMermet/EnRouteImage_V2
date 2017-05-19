<?php
/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$db_host = 'localhost';
$db_user = 'labelImgManager';
$db_password = 'Y8iRL0yA8zCLbAaV';
$db_name = 'labelimgdb';
$link = mysqli_connect($db_host, $db_user, $db_password, $db_name);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
$imgPath = '../img/segmentation/';
// Attempt update query execution
$sql = "SELECT * FROM `segimages` WHERE `naturalWidth` IS NULL";
$imgs = $link->query($sql);
while ($img = $imgs->fetch_object()) {
	$imgInfo = getimagesize($imgPath.$img->path);
	error_log(print_r($img->path,true));
    error_log(print_r($imgInfo,true));
    $sql2 = "UPDATE `segimages` 
    SET `naturalWidth`='$imgInfo[0]',`naturalHeight`='$imgInfo[1]' 
    WHERE `segimages`.`id` = '$img->id'";
    $link->query($sql2);
}

if(mysqli_query($link, $sql)){
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
// Close connection
mysqli_close($link);
?>