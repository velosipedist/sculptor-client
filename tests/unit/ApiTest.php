<?php
namespace velosipedist\SculptorClient\tests\unit;

use Faker\Factory;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;
use velosipedist\SculptorClient\Lead;
use velosipedist\SculptorClient\SculptorClient;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SculptorClient
     */
    private $testClient;
    /**
     * @var Mock
     */
    private $mockHttpSubscriber;
    /**
     * @var History
     */
    private $httpHistory;

    private function getTestClient()
    {
        if ($this->testClient === null) {
            $this->testClient = $client = new SculptorClient(123);
        }
        return $this->testClient;
    }

    /**
     * @param Response[] $responses
     * @return $this
     */
    protected function expectResponses(array $responses)
    {
        $this->getMockHttpSubscriber()->addMultiple($responses);
        return $this;
    }

    protected function getMockHttpSubscriber()
    {
        if ($this->mockHttpSubscriber === null) {
            $this->mockHttpSubscriber = new Mock();
            $this->getTestClient()->getHttpClient()->getEmitter()
                ->attach($this->mockHttpSubscriber);
        }
        return $this->mockHttpSubscriber;
    }

    protected function getHttpHistory()
    {
        if ($this->httpHistory === null) {
            $this->httpHistory = new History();
            $this->getTestClient()->getHttpClient()->getEmitter()
                ->attach($this->httpHistory);
        }
        return $this->httpHistory;
    }

    public function testCreateLead()
    {
        $client = $this->getTestClient();
        $f = Factory::create('ru_RU');
        $lead = new Lead($f->name, $f->phoneNumber, $f->email, $f->city);
        $this->expectResponses([new Response(200)]);
        $history = $this->getHttpHistory();
        $response = $client->createLead('TEST_GUID', $lead);
        $this->assertEquals(200, $response->getStatusCode());
        $lastReq = $history->getLastRequest();
        $this->assertEquals('TEST_GUID', $lastReq->getQuery()->get('project_id'));
    }
}
