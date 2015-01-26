<?php
namespace velosipedist\SculptorClient;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use PhpConsole\Connector;
use PhpConsole\Helper;
use velosipedist\SculptorClient\exception\ApiException;

class SculptorClient
{
    /**
     * @var array
     */
    private static $registeredFormSelectors = [];
    /**
     * @var boolean Enable debug mode
     */
    private $debugMode = false;
    /**
     * @var Client API calls client library
     */
    private $httpClient;
    /**
     * @var callable
     */
    private $errorHandler;
    /**
     * @var string
     */
    private $formMethod;
    /**
     * @var string
     */
    private $googleClientId;
    /**
     * @var string
     */
    private $pageUrl;
    private $apiKey;
    private $phpConsoleSetupDone = false;

    /**
     * Start up Sculptor API session instance.
     * @param $apiKey
     * @param $projectId
     * @param string $formMethod
     * @param string $host
     * @param array $guzzleOptions
     */
    function __construct(
        $apiKey, $projectId, $formMethod = 'post', $host = 'http://sculptor.tochno-tochno.ru', array $guzzleOptions = []
    )
    {
        $config = [
            'base_url' => $host,
            'defaults' => [
                'query' => [
                    'api_key' => $apiKey,
                    'project_id' => $projectId,
                ]
            ]
        ];
        $config = $this->applyAddGuzzleOptions($guzzleOptions, $config);
        $this->httpClient = new Client($config);
        $this->formMethod = $formMethod;
        $this->apiKey = $apiKey;
    }

    /**
     * Merge only safe params for configuring Guzzle
     * @param array $guzzleOptions
     * @param array $config
     * @return array
     */
    private function applyAddGuzzleOptions(array $guzzleOptions, array $config)
    {
        if (isset($guzzleOptions['verify'])) {
            $config['defaults']['verify'] = $guzzleOptions['verify'];
        }
        if (isset($guzzleOptions['cert'])) {
            $config['defaults']['cert'] = $guzzleOptions['cert'];
            return $config;
        }
        return $config;
    }

    /**
     * Register script to catch Google Analytics API ClientId and pass it to posted form data
     * @param $formCssSelector
     */
    public static function injectClientId($formCssSelector)
    {
        if (!isset(static::$registeredFormSelectors[$formCssSelector])) {
            register_shutdown_function(function () use ($formCssSelector) {
                print "<script>
                try{
                    ga(function(tracker){
                        $('{$formCssSelector}')
                        .append($('<input/>', {
                            type:'hidden',
                            name:'__sculptor[google_client_id]',
                            value: tracker.get('clientId')
                        })).append($('<input/>', {
                            type:'hidden',
                            name:'__sculptor[page_url]',
                            value: location.href + (location.hash ? '#' + location.hash : '')
                        })).append($('<input/>', {
                            type:'hidden',
                            name:'__sculptor[page_title]',
                            value: document.title
                        }))
                    })}catch(e){try{console.log(e)}catch(e2){}}
                </script>";
            });
            static::$registeredFormSelectors[$formCssSelector] = true;
        }
    }

    /**
     * Send create new Lead API request
     * @param Lead $data
     * @throws \Exception
     * @return ResponseInterface|null
     */
    public function createLead(Lead $data)
    {
        $post = $this->extractLeadBody($data);
        try {
            $response = $this->callApiMethod('/lead/api/lead', $post);
            return $response;
        } catch (\Exception $e) {
            //todo ErrorResponse?
            return null;
        }
    }

    /**
     * @param Lead $data
     * @return array
     */
    private function extractLeadBody(Lead $data)
    {
        $extracted = [
            'customerFullname' => $data->getCustomerFullname(),
            'customerEmail' => $data->getCustomerEmail(),
            'customerPhone' => $data->getCustomerPhone(),
            'customData' => json_encode($data->getCustomData()),
        ];
        $formScriptData = $this->formMethod == 'post' ? $_POST['__sculptor'] : $_GET['__sculptor'];
        if ($data->getCustomerCityName()) {
            $extracted['customerCityName'] = $data->getCustomerCityName();
        }
        if ($data->getCustomerCityLocalId()) {
            $extracted['customerCityLocalId'] = $data->getCustomerCityLocalId();
        }
        if ($data->getTypeSlug()) {
            $extracted['typeSlug'] = $data->getTypeSlug();
        }

        if ($this->googleClientId) {
            $extracted['googleClientId'] = $this->googleClientId;
        } else {
            $extracted['googleClientId'] = $formScriptData['google_client_id'];
        }
        if ($this->pageUrl) {
            $extracted['pageUrl'] = $this->pageUrl;
        } else {
            $extracted['pageUrl'] = $formScriptData['page_url'];
        }
        return $extracted;
    }

    /**
     * Call REST method of Sculptor API handling successful response and debugging failed requests
     * @param string $url
     * @param array $post
     * @return ResponseInterface
     * @throws ApiException
     */
    private function callApiMethod($url, $post)
    {
        if ($this->debugMode) {
            $this->setupPhpConsole();
        }
        $request = $this->httpClient->createRequest('POST', $url, ['body' => $post]);
        try {
            $result = $this->httpClient->send($request);
            if ($this->debugMode) {
                Helper::debug($request->getUrl(), 'sculptor.request.success');
                Helper::debug($result->json(), 'sculptor.request.success');
            }
            return $result;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                Helper::debug($e->getMessage(), 'sculptor.error');
                Helper::debug($this->httpClient, 'sculptor.client');
                Helper::debug($request, 'sculptor.request');
            }
            $apiException = new ApiException($e->getMessage(), $e->getCode(), $this->httpClient, $request);
            if (!is_null($callback = $this->errorHandler)) {
                $callback($apiException);
            }
            throw $apiException;
        }
    }

    /**
     * Bootstrap PHPConsole with API key as password
     * @throws \Exception
     */
    private function setupPhpConsole()
    {
        if (!$this->phpConsoleSetupDone) {
            Helper::register();
            Connector::getInstance()->setPassword($this->apiKey);
            $this->phpConsoleSetupDone = true;
        }
    }

    /**
     * Set or reset with null error handler for any request.
     * It must be any valid callback that receive one parameter, exception:
     * ```php
     * $client->setErrorHandler(function(\Exception $e){
     *   // do something with it, maybe returning any data you need after error occured
     * });
     * ```
     * @param callable|null $errorHandler
     */
    public function setErrorHandler($errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Force Google Analytics API clientId if it is caught from your custom code
     * @param string $forcedClientId
     */
    public function setGoogleClientId($forcedClientId)
    {
        $this->googleClientId = $forcedClientId;
    }

    /**
     * Force referring page url
     * @param string $pageUrl
     */
    public function setPageUrl($pageUrl)
    {
        $this->pageUrl = $pageUrl;
    }

    /**
     * @return boolean
     */
    public function getDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param boolean $debugMode
     */
    public function setDebugMode($debugMode)
    {
        $this->debugMode = (bool)$debugMode;
    }
}
