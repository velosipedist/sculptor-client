<?php
namespace velosipedist\SculptorClient\tests\unit;

use velosipedist\SculptorClient\SculptorClient;

class ClientConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultOptions()
    {
        $client = new SculptorClient(123, 456);
        $this->assertNotEquals('', $client->getHttpClient()->getBaseUrl());
    }

    public function testSecureOptions()
    {
        $client = new SculptorClient(123, 456);
        $this->assertEquals(false, $client->getHttpClient()->getDefaultOption('verify'));
        $this->assertEquals(null, $client->getHttpClient()->getDefaultOption('cert'));

        $client = new SculptorClient(123, 456, ['base_url' => 'http://127.0.0.1']);
        $this->assertEquals(true, $client->getHttpClient()->getDefaultOption('verify'));
        $this->assertEquals(null, $client->getHttpClient()->getDefaultOption('cert'));

        $client = new SculptorClient(123, 456, ['base_url' => 'https://127.0.0.1']);
        $this->assertEquals(false, $client->getHttpClient()->getDefaultOption('verify'));
        $this->assertEquals(null, $client->getHttpClient()->getDefaultOption('cert'));

        $client = new SculptorClient(123, 456, ['base_url' => 'https://127.0.0.1', 'guzzle' => ['cert' => 'foo']]);
        $this->assertEquals(false, $client->getHttpClient()->getDefaultOption('verify'));
        $this->assertEquals('foo', $client->getHttpClient()->getDefaultOption('cert'));
    }

    public function testBadConfigs()
    {
        $this->setExpectedException('\Symfony\Component\OptionsResolver\Exception\ExceptionInterface');
        new SculptorClient(123, 456, ['form_method' => 'foobar']);
        $this->setExpectedException('\Symfony\Component\OptionsResolver\Exception\ExceptionInterface');
        new SculptorClient(123, 456, ['foo' => 'bar']);
        $this->setExpectedException('\Symfony\Component\OptionsResolver\Exception\ExceptionInterface');
        new SculptorClient(123, 456, ['guzzle' => 'bar']);
    }

    public function testTestMode()
    {
        $client = new SculptorClient(123, 456);
        $defaultQuery = $client->getHttpClient()->getDefaultOption('query');
        $this->assertArrayNotHasKey('test_mode', $defaultQuery);
        $client = new SculptorClient(123, 456, ['testing' => true]);
        $defaultQuery = $client->getHttpClient()->getDefaultOption('query');
        $this->assertArrayHasKey('test_mode', $defaultQuery);
    }
}
