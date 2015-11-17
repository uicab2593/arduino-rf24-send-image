<?php
require 'vendor/autoload.php';

set_time_limit (60 * 5);
$serial = new PhpSerial;
$serial->deviceSet("/dev/ttyACM0");
$serial->confBaudRate(57600);
$serial->confParity("none");
$serial->confCharacterLength(32);
$serial->confStopBits(1);
$serial->confFlowControl("none");

$source = file_get_contents(isset($_FILES['image']['tmp_name'])?$_FILES['image']['tmp_name']:'descarga.png');
$serial->deviceOpen();
$index=0;
$len = strlen($source);
$nFrames = ceil($len/28); 
$curFrame = 1;
$start = true;
$MASK = 255;
echo $nFrames."\n";
while($index<$len){
	$aux = substr($source,$index,28);
	$aux = chr(($nFrames>>8) & $MASK).chr($nFrames & $MASK).chr(($curFrame>>8) & $MASK).chr($curFrame & $MASK).$aux;
	echo $curFrame."\n";
	$serial->sendMessage($aux);
	while($serial->readPort()=="");
	$index += 28;
	$curFrame++;
}
$serial->deviceClose();