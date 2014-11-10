<?php

require_once __DIR__ . '/../vendor/autoload.php';

$brg = new Burgomaster(__DIR__ . '/phar', __DIR__ . '/..');
$brg->recursiveCopy('vendor/composer', 'vendor/composer');
$brg->recursiveCopy('vendor/mtdowling/burgomaster/src', 'vendor/mtdowling/burgomaster/src');
$brg->recursiveCopy('vendor/guzzlehttp/guzzle/src', 'vendor/guzzlehttp/guzzle/src');
$brg->recursiveCopy('vendor/guzzlehttp/ringphp/src', 'vendor/guzzlehttp/ringphp/src');
$brg->recursiveCopy('vendor/guzzlehttp/streams/src', 'vendor/guzzlehttp/streams/src');
$brg->recursiveCopy('vendor/react/promise/src', 'vendor/react/promise/src');
$brg->recursiveCopy('vendor/php-console/php-console/src', 'vendor/php-console/php-console/src');
$brg->recursiveCopy('src', 'src');
$brg->deepCopy('vendor/autoload.php', 'vendor/autoload.php');
$brg->createPhar('build/sculptor.phar', null, 'vendor/autoload.php');

if (!file_exists(__DIR__ . '/faker.phar')) {
    $fakerPhar = new Burgomaster(__DIR__ . '/phar/faker', __DIR__ . '/..');
    $fakerPhar->recursiveCopy('vendor/fzaninotto/faker/src', 'vendor/fzaninotto/faker/src');
    $fakerPhar->createPhar('build/faker.phar', null, 'vendor/fzaninotto/faker/src/autoload.php');
}
