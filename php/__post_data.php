<?php

include('config.php');
if (!empty($_POST))
{
    echo "Data sended to server\n";
	
	$data = json_decode(($_POST['data']));
	
	$source = mysqli_real_escape_string($db,($data->dataSrc));
	
	$rects= $data->rects;
	foreach ($rects as $num => $rect) {//for each rectangle
		
		$rectType = mysqli_real_escape_string($db,($rect->type));
		$rectLeft = mysqli_real_escape_string($db,($rect->rectLeft));
		$rectTop = mysqli_real_escape_string($db,($rect->rectTop));
		$rectRight = mysqli_real_escape_string($db,($rect->rectRight));
		$rectBottom = mysqli_real_escape_string($db,($rect->rectBottom));
		$sql = "SELECT * FROM 
		`labelimgarea` lia WHERE 
		lia.source='$source' AND 
		lia.rectType='$rectType' AND 
		lia.rectLeft='$rectLeft' AND 
		lia.rectTop='$rectTop' AND 
		lia.rectRight='$rectRight' AND 
		lia.rectBottom='$rectBottom';";
		$result = $db->query($sql);
		if ($result->num_rows > 0) {
			echo "row was already created";
		} else {
			$sql = "
			INSERT INTO labelimgarea (source, rectType, rectLeft,rectTop,rectRight,rectBottom)
			VALUES ('$source','$rectType','$rectLeft','$rectTop','$rectRight','$rectBottom')";
			if ($db->query($sql) === TRUE) {
				echo "New record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $db->error;
			}
		}
	}
	$db->close();
}
else // $_POST is empty.
{
    echo "No data";
}

?>