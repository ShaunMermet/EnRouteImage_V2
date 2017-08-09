<?php
$db_host = 'localhost';
$db_user = 'labelImgManager';
$db_password = 'Y8iRL0yA8zCLbAaV';
$db_name = 'labelimgdb';
$link = mysqli_connect($db_host, $db_user, $db_password, $db_name);
 
// Check connection
if(!$link){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
// Attempt update query execution
$sql = "SELECT * FROM `labelimglinks` WHERE state = 3";
$imgs = mysqli_query($link, $sql);
$validatedID = [];
if (mysqli_num_rows($imgs) > 0) {
	while($row = mysqli_fetch_assoc($imgs)) {
		array_push($validatedID, $row["id"]);
    }
}
$ids = join("','",$validatedID); 
$sql2 = "UPDATE `labelimgarea` SET `state`= 3 WHERE `source` IN ('$ids')";
$res = mysqli_query($link, $sql2);
if ($res) {
    echo "Rows updated successfully <br>";
} else {
    echo "Error: " . $sql2 . "<br>" . mysqli_error($link). "<br> <br>";
}

if(mysqli_query($link, $sql2)){
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
// Close connection
mysqli_close($link);
?>