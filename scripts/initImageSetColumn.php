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
$sql = "SELECT * FROM `labelimglinks` WHERE 1";
$imgs = mysqli_query($link, $sql);
if (mysqli_num_rows($imgs) > 0) {
	while($row = mysqli_fetch_assoc($imgs)) {
		error_log($row["id"]." - ".$row["path"]);
		$rowID = $row["id"];
		$catID = $row["category"];
		if (is_null($row["group"])){$row["group"] = 1;}
		if(is_null($catID) || $catID == 0){
			$catName = "Default";
		}else{
	        $sql2 = "SELECT Category FROM `labelimgcategories` WHERE id = $catID";
			$res2 = mysqli_query($link, $sql2);
			if (mysqli_num_rows($res2) > 0) {
				$row2 = mysqli_fetch_assoc($res2);
				$catName = $row2["Category"];
			}
		}
		$grpID = $row["group"];
		error_log($catName." - ".$grpID);
		$sql3 = "SELECT * FROM `sets` WHERE `name` = '$catName' AND `group_id` = '$grpID'";
		$res3 = mysqli_query($link, $sql3);
		if (mysqli_num_rows($res3) == 0) {
			error_log("create ".$catName." - ".$grpID);
			$sql4 = "INSERT INTO `sets` (`id`, `name`, `group_id`) VALUES (NULL, '$catName', '$grpID')";
			$res4 = mysqli_query($link, $sql4);
			if ($res4) {
			    echo "New set created successfully ".$catName." - ".$grpID."<br>";
			} else {
			    echo "Error: " . $sql4 . "<br>" . mysqli_error($link). "<br> <br>";
			}
			$res5 = mysqli_query($link, $sql3);
			if (mysqli_num_rows($res5) > 0) {
				$row5 = mysqli_fetch_assoc($res5);
				$setId = $row5["id"];
			}
		}else{
			$row3 = mysqli_fetch_assoc($res3);
			$setId = $row3["id"];
		}
		error_log($setId);
		$sql6 = "UPDATE `labelimglinks` 
	    SET `set_id`='$setId'
	    WHERE `labelimglinks`.`id` = '$rowID'";
	    $res6 = mysqli_query($link, $sql6);
		if ($res6) {
		    echo "Image ".$rowID." attrib to set ".$setId." successfully <br>";
		} else {
		    echo "Error: " . $sql4 . "<br>" . mysqli_error($link). "<br> <br>";
		}
    }
	
}

if(mysqli_query($link, $sql6)){
    echo "Records were updated successfully.";
} else {
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
// Close connection
mysqli_close($link);
?>