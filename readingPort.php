<?php
require 'vendor/autoload.php';

$serial = new PhpSerial;

$serial->deviceSet("/dev/ttyACM1");

$serial->confBaudRate(57600);
$serial->confParity("none");
$serial->confCharacterLength(32);
$serial->confStopBits(1);
$serial->confFlowControl("none");
$serial->deviceOpen();

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH,'my pusher');
$socket->connect("tcp://127.0.0.1:5555");
/*new image*/
while(true){
	while(($read = $serial->readPort()) == "");
	$image = fopen("test2","w");
	$socket->send(1);
	fwrite($image,$read);	
	$time = time();
	while(time() - $time < 3){
		while(($read = $serial->readPort()) == "" && time() - $time < 3);
		if(time() - $time < 3){
			fwrite($image,$read);
			$time = time();
		}
	}
	$socket->send(2);
	fclose($image);
}
$serial->deviceClose();