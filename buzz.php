<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include 'vendor/autoload.php';
use Buzz\Client\MultiCurl;
use Buzz\Exception\NetworkException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;


$client = new MultiCurl(new Psr17Factory());

$start = microtime(true);
$response = $client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush', [], null, '2.0'));
$timeFirstRequest = microtime(true)-$start;

// TODO parse request
$body = $response->getBody()->__toString();

$start = microtime(true);
$client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush/static/style.css?1547989877223381214'));
$client->sendRequest(new Request('GET', 'https://http2.golang.org/serverpush/static/jquery.min.js?1547989877223381214'));
$timeOtherRequests = microtime(true)-$start;


var_dump("\n\n\n\nFirst: ".$timeFirstRequest. "\nOther: ".$timeOtherRequests. "\n\n");

var_dump($timeFirstRequest > $timeOtherRequests);
echo "First: ".$timeFirstRequest. "\nOther: ".$timeOtherRequests. "\n";
