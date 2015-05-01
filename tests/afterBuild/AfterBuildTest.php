<?php


namespace velosipedist\SculptorClient\tests\afterBuild;


class AfterBuildTest extends \PHPUnit_Framework_TestCase
{

    public function testPharIncludable()
    {
        require __DIR__ . '/../../build/sculptor.phar';
        $this->assertTrue(class_exists('\velosipedist\SculptorClient\SculptorClient'));
    }
}
