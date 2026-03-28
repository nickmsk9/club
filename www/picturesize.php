<?php

# Constants
if($_GET[type]==1){
define(MAX_WIDTH, 150);
define(MAX_HEIGHT, 80);
}elseif($_GET[type]==2){
define(MAX_WIDTH, 230);
define(MAX_HEIGHT, 340);
}elseif($_GET[type]==3){
define(MAX_WIDTH, 300);
define(MAX_HEIGHT, 440);
}elseif($_GET[type]==4){
define(MAX_WIDTH, 180);
define(MAX_HEIGHT, 100);
}elseif($_GET[type]==5){
define(MAX_WIDTH, 140);
define(MAX_HEIGHT,190);	
} elseif ($_GET[type]==6){
define(MAX_WIDTH, 200);
define(MAX_HEIGHT,160);
}
else{
define(MAX_WIDTH, 500);
define(MAX_HEIGHT,400);	
}
# Get image location
$image_file = $_GET[image];
$image_path = "$image_file";
# Load image
$img = null;
$ext = strtolower(end(explode('.', $image_path)));
if ($ext == 'jpg' || $ext == 'jpeg') {
$img = @imagecreatefromjpeg($image_path);
} else if ($ext == 'png') {
$img = @imagecreatefrompng($image_path);
# Only if your version of GD includes GIF support
} else if ($ext == 'gif') {
$img = @imagecreatefromgif($image_path);
}

# If an image was successfully loaded, test the image for size
if ($img) {

# Get image size and scale ratio
$width = imagesx($img);
$height = imagesy($img);
$scale = min(MAX_WIDTH/$width, MAX_HEIGHT/$height);

# If the image is larger than the max shrink it
if ($scale < 1) {
if (($width > MAX_WIDTH) OR ($height > MAX_HEIGHT)) {
$new_width = floor($scale*$width);
$new_height = floor($scale*$height);
# Create a new temporary image
$tmp_img = imagecreatetruecolor($new_width, $new_height);
# Copy and resize old image into new image
imagecopyresampled($tmp_img, $img, 0, 0, 0, 0,
$new_width, $new_height, $width, $height);
imagedestroy($img);
$img = $tmp_img;
}
}else{
header("Location: $image_path");
exit;
}}

# Create error image if necessary
if (!$img) {
$img = imagecreate(MAX_WIDTH, MAX_HEIGHT);
imagecolorallocate($img,0,0,0);
$c = imagecolorallocate($img,70,70,70);
imageline($img,0,0,MAX_WIDTH,MAX_HEIGHT,$c2);
imageline($img,MAX_WIDTH,0,0,MAX_HEIGHT,$c2);
}

# Display the image
header("Content-type: image/jpeg");
header('Content-Disposition: inline; filename='.str_replace('/','',$image_path));

imagejpeg($img);
imagedestroy($img);
?>