<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 00:00:00 GMT');
header('Content-type: application/json');
require('../curlHandler.php');

$output = array(
	'status' => '0',
	'data' => array()
);

if (!isset($_GET['url']) || empty($_GET['url']))
{
	$output["error"] = "Please enter a url";
	outputAjax($output);
}
else
{
	$url = $_GET['url'];
}

$ch = new CurlHandler();
if(!$ch -> send($url)) 
{

	$output["error"] = "no response";
	$output["url"] = $url;
	outputAjax($output);
}
else
{
	$output['status'] = 1;
	$output['data'] = $ch -> responseBody;
	outputAjax($output);
}

function outputAjax($ajax)
{
	if($ajax['status'] == 1)
	{
		// json must be utf8 encoded!
		$ajax['data'] = utf8_encode($ajax['data']);
	}

	echo json_encode($ajax);
	exit;
}