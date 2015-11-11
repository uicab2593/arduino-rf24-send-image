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
$source = file_get_contents($_FILES['image']['tmp_name']);
$serial->deviceOpen();
$index=0;
$len = strlen($source);
while($index<$len){
	$aux = substr($source,$index,32);
	$serial->sendMessage($aux);
	while($serial->readPort()=="");
	$index += strlen($aux);
}
$serial->deviceClose();