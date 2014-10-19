<?php

require_once __DIR__ . '/../vendor/autoload.php';

$brg = new Burgomaster(__DIR__ . DIRECTORY_SEPARATOR . 'phar', __DIR__ . DIRECTORY_SEPARATOR . '..');
$brg->recursiveCopy('vendor/composer', 'vendor/composer');
$brg->recursiveCopy('vendor/mtdowling/burgomaster/src', 'vendor/mtdowling/burgomaster/src');
$brg->recursiveCopy('vendor/guzzlehttp/guzzle/src', 'vendor/guzzlehttp/guzzle/src');
$brg->recursiveCopy('vendor/guzzlehttp/ringphp/src', 'vendor/guzzlehttp/ringphp/src');
$brg->recursiveCopy('vendor/guzzlehttp/streams/src', 'vendor/guzzlehttp/streams/src');
$brg->recursiveCopy('vendor/react/promise/src', 'vendor/react/promise/src');
$brg->recursiveCopy('src', 'src');
$brg->deepCopy('vendor/autoload.php', 'vendor/autoload.php');
$brg->createPhar('build/sculptor.phar', null, 'vendor/autoload.php');