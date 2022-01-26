<?php
/*
header("content-type: image/png");
$font = '/captcha/Quixley.ttf';
$imagen = imagecreate(65,25) or die("error");
$color_fondo = imagecolorallocate($imagen, 0, 0, 0);
$color_texto = imagecolorallocate($imagen, 135, 206, 235);

function generate_captcha($chars, $length){
    $captcha = null;
    for($x=0;$x<$length; $x++){
        $rand = rand(0, count($chars)-1);
        $captcha .= $chars[$rand];
    }
    return $captcha;
}

$captcha = generate_captcha(array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F'), 6);

setcookie('captcha',  sha1($captcha),  time()+60*3);
$lineColor = imagecolorallocate($imagen, 175, 238, 238); 
$width = 50;
for($i=0;$i<20;$i++){ 
    $x1 = rand(0,$width);$x2 = rand(0,$width); 
    $y1 = rand(0,$width);$y2 = rand(0,$width); 
    imageline($imagen,$x1,$y1,$x2,$y2,$lineColor); 
}
imagestring($imagen, 20, 5, 5, $captcha, $color_texto);
imagepng($imagen);


*/
// Font directory + font name 
$font = 'fonts/Quixley.ttf'; 
// Total number of lines 
$lineCount = 45; 
// Size of the font 
$fontSize = 30; 
// Height of the image 
$height = 50; 
// Width of the image 
$width = 150; 
$img_handle = imagecreate ($width, $height) or die ("Cannot Create image"); 
// Set the Background Color RGB 
$backColor = imagecolorallocate($img_handle, 240,240,240); 
// Set the Line Color RGB 
$lineColor = imagecolorallocate($img_handle, 102,178,255);//175, 238, 238); 
// Set the Text Color RGB 
$txtColor = imagecolorallocate($img_handle, 0,128,255);//135, 206, 235); 

$captcha = null;
$chars = array(0,1,2,3,4,5,6,7,8,9);
    for($x=0;$x<4; $x++){
        $rand = rand(0, count($chars)-1);
        $captcha .= $chars[$rand];
    }

$textbox = imagettfbbox($fontSize, 0, $font, $captcha) or die('Error in imagettfbbox function'); 
$x = ($width - $textbox[4])/2; 
$y = ($height - $textbox[5])/2; 
imagettftext($img_handle, $fontSize, 0, $x, $y, $txtColor, $font , $captcha) or die('Error in imagettftext function'); 
for($i=0;$i<$lineCount;$i++){ 
    $x1 = rand(0,$width);$x2 = rand(0,$width); 
    $y1 = rand(0,$width);$y2 = rand(0,$width); 
    imageline($img_handle,$x1,$y1,$x2,$y2,$lineColor); 
} 
header('Content-Type: image/png');
//imagedestroy($img_handle); 
setcookie('captcha',  sha1($captcha),  time()+60*3);
imagepng($img_handle);
