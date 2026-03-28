<?php

require_once 'include/bittorrent.php';

dbconn();
include_once 'include/Wall_Updates.php';

$Wall = new Wall_Updates();



	$valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
	if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
		{
			$name = $_FILES['photoimg']['name'];
			$size = $_FILES['photoimg']['size'];
			
			if(strlen($name))
				{
					list($txt, $ext) = explode(".", $name);
					if(in_array($ext,$valid_formats))
					{
					if($size<(1024*1024))
						{
							$actual_image_name = time().$CURUSER["id"].".".$ext;
							$tmp = $_FILES['photoimg']['tmp_name'];
							if(move_uploaded_file($tmp, $path.$actual_image_name))
								{
								    $data=$Wall->Image_Upload($CURUSER["id"],$actual_image_name);
									 $newdata=$Wall->Get_Upload_Image($CURUSER["id"],$actual_image_name);
									 if($newdata)
									{
								//echo '<img src="data:image/jpg;base64,'.$newdata['image_base'].'" class="preview" id="'.$newdata['id'].'"/>';
								echo "<img src='uploads/".$actual_image_name."'  class='preview' id='".$newdata['id']."'/>";
									}
								}
							else
								echo "failed";
						}
						else
						echo "Image file size max 1 MB";					
						}
						else
						echo "Invalid file format.";	
				}
				
			else
				echo "Please select image..!";
				
			exit;
		}
?>