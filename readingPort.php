<?php
require 'vendor/autoload.php';

$serial = new PhpSerial;

$serial->deviceSet("/dev/ttyACM2");

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
$new = "";
$MASK = 255;
$MASK2 = $MASK<<8;
$image = fopen("test2","w");
$lastFrame=0;
// default str
$defaultStr = getDefaultStr();
// 
while(true){
	$read = "";
	while(strlen($read .= $serial->readPort()) < 32);
	$dataStr = substr($read,0,32);
	$data = unpack('C*',$dataStr);
	$read = substr($read,count($data));
	$nFrames = (($data[1]<<8) & $MASK2) + $data[2];
	$frame = (($data[3]<<8) & $MASK2) + $data[4];
	if($frame==1){
		rewind($image);
		ftruncate($image,0);
		$socket->send(1);
	}

	if($frame==$lastFrame+1){
		fwrite($image,substr($dataStr,4));
		$lastFrame+=1;
	}
	else{
		// si ocurren un error en los datos, llegan numeros muy grandes. tuyo?
		if($frame>$lastFrame+100 || $frame<$lastFrame-100){
			fwrite($image,$defaultStr);
			$lastFrame+=1;
		}else{
			fwrite($image,getDefaultStr((($frame-$lastFrame)-1)*28));
			fwrite($image,substr($dataStr,4));
			$lastFrame = $frame;
		}
	}
	echo $frame."\n";
	echo $nFrames."\n";
	echo "\n";
	if($frame==$nFrames) $socket->send(2);
}
$serial->deviceClose();

function getDefaultStr($bytes=28){
	$defaultStr = '';
	for ($i=0; $i < $bytes; $i++) { 
		$defaultStr.=chr(0);
	}	
}