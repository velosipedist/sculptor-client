<?php
namespace velosipedist\SculptorClient\tests\afterPack;


class AfterPackTest extends \PHPUnit_Framework_TestCase
{

    public function testPharIncludable()
    {
        require_once __DIR__ . '/../../build/sculptor.phar';
        $this->assertTrue(class_exists('\velosipedist\SculptorClient\SculptorClient'));
    }
}
