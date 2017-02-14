<?php
include('config.php');
if (!empty($_POST))
{
	$data = json_decode(($_POST['data']));
	
	$source = mysqli_real_escape_string($db,($data->dataSrc));
	$validated = mysqli_real_escape_string($db,($data->validated));
	if ($validated)
		$dbValid = 1;
	else{
		$dbValid = 0;
		deleteArea($source,$db);
	}
	
	$sql = "UPDATE `labelimglinks` SET `validated` = $dbValid WHERE `labelimglinks`.`id` = '$source'";
	if ($db->query($sql) === TRUE) {
		echo "record done";
	} else {
		echo "Error: " . $sql . "<br>" . $db->error;
	}
	$db->close();
}
else // $_POST is empty.
{
    echo "No data";
}

function deleteArea($source = NULL,$db){
	if(!is_null($source)){
		$sql = "DELETE FROM `labelimgarea` WHERE `source`= '$source'";
		if ($db->query($sql) === TRUE) {
			echo "delete done";
		} else {
			echo "Error: " . $sql . "<br>" . $db->error;
		}
		$db->close();
	}
}

?>