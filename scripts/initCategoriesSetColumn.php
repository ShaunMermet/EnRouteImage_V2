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
$sql = "SELECT * FROM `labelimgcategories` WHERE set_id = 1";
$cats = mysqli_query($link, $sql);
$catList = [];
if (mysqli_num_rows($cats) > 0) {
	while($row = mysqli_fetch_assoc($cats)) {
		array_push($catList, $row);
    }
}


$sql2 = "SELECT * FROM `sets` WHERE id != 1";
$sets = mysqli_query($link, $sql2);
//$catList = [];
if (mysqli_num_rows($sets) > 0) {
	while($row = mysqli_fetch_assoc($sets)) {
		//array_push($catList, $row);
		foreach ($catList as &$cat) {
			$currentCatCat = $cat["Category"];
			$currentCatColor = $cat["Color"];
			$currentSetId = $row["id"];
			$sql3 = "SELECT * FROM `labelimgcategories` WHERE `Category` = '$currentCatCat' AND `set_id` = '$currentSetId'";
			$res3 = mysqli_query($link, $sql3);
			if (mysqli_num_rows($res3) == 0) {
				$sql4 = "INSERT INTO `labelimgcategories` (`id`, `Category`,`Color`, `set_id`) VALUES (NULL, '$currentCatCat', '$currentCatColor', '$currentSetId')";
				$res4 = mysqli_query($link, $sql4);
				if ($res4) {
				    echo "add cat ".$currentCatCat." for set ".$row["name"].$row["group_id"]."<br>";
				} else {
				    echo "Error: " . $sql4 . "<br>" . mysqli_error($link). "<br> <br>";
				}
			}
		    
		}
		echo "<br>";
    }
    echo "Records were updated successfully.";
}





 
// Close connection
mysqli_close($link);
?>