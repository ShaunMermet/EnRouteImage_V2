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
 
// Attempt update query execution
$sql = "UPDATE `segimages` lnk LEFT JOIN `segareas` are ON are.source = lnk.id
        SET lnk.state = 2
        WHERE lnk.validated = 0 AND are.deleted_at IS NULL";
$sql2 = "UPDATE `segimages`
        SET state = 3
        WHERE validated = 1";

if( mysqli_query($link, $sql) && mysqli_query($link, $sql2)){
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
// Close connection
mysqli_close($link);
?>