<?php
namespace velosipedist\SculptorClient;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use velosipedist\SculptorClient\exception\ApiException;
use velosipedist\SculptorClient\exception\BadResponseException;

class SculptorClient
{
    private static $javascriptLibraryRegistered = false;
    private static $leadJavascriptOptions;

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
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var array
     */
    private $resolvedOptions;

    /**
     * Start up Sculptor API session instance.
     * @param string $apiKey
     * @param array $options
     */
    function __construct($apiKey, array $options = [])
    {
        $options = $this->resolveInitOptions($apiKey, $options);
        $this->httpClient = new Client($options['guzzle']);
        $this->formMethod = $options['form_method'];
        $this->resolvedOptions = $options;
        $this->apiKey = $apiKey;
    }

    /**
     * Reconfigure javascript injection at runtime, before or after
     * @param array $options
     */
    public static function configureLeadsJavascript(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'mock_ga' => false,
            'enabled' => true,
        ])->setAllowedTypes([
            'mock_ga' => 'bool',
            'enabled' => 'bool',
        ]);
        static::$leadJavascriptOptions = $resolver->resolve($options);
        if (!static::$javascriptLibraryRegistered) {
            static::$javascriptLibraryRegistered = true;
            register_shutdown_function(function () {
                // incapsulate static call
                static::appendJavascript();
            });
        }
    }

    /**
     * @param $apiKey
     * @param array $options
     * @return array
     */
    private function resolveInitOptions($apiKey, array $options)
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'base_url' => 'https://sculptor.tochno-tochno.ru',
            'form_method' => 'post',
            'guzzle' => [],
            'testing' => false,
        ]);
        $optionsResolver->setAllowedTypes([
            'base_url' => 'string',
            'form_method' => 'string',
            'guzzle' => 'array',
            'testing' => 'bool',
        ]);
        $optionsResolver->setAllowedValues([
            'form_method' => ['get', 'post'],
        ]);
        $optionsResolver->setNormalizer('base_url', function ($options, $baseUrl) {
            return trim($baseUrl);
        });
        $optionsResolver->setNormalizer('guzzle', function ($options, $guzzleOpts) use ($apiKey) {
            isset($guzzleOpts['defaults']) or $guzzleOpts['defaults'] = [];
            $guzzleOpts['base_url'] = $options['base_url'];
            if (substr($options['base_url'], 0, 6) == 'https:') {
                $guzzleOpts['defaults']['verify'] = isset($guzzleOpts['verify']) ? $guzzleOpts['verify'] : false;
                if (isset($guzzleOpts['cert'])) {
                    $guzzleOpts['defaults']['cert'] = $guzzleOpts['cert'];
                }
            } else {
                unset($guzzleOpts['defaults']['verify'], $guzzleOpts['defaults']['cert']);
            }
            unset($guzzleOpts['verify'], $guzzleOpts['cert']);
            $guzzleOpts['defaults']['query'] = [
                'api_key' => $apiKey,
            ];
            $guzzleOpts['defaults']['allow_redirects'] = true;
            if ($options['testing']) {
                $guzzleOpts['defaults']['query']['test_mode'] = 1;
            }
            return $guzzleOpts;
        });
        $options = $optionsResolver->resolve($options);
        return $options;
    }


    /**
     * Shutdown script procedure that appends javascripts for catching google client ids and mocking it for testing.
     */
    private static function appendJavascript()
    {
        if (!(static::$leadJavascriptOptions['enabled'])) {
            return;
        }
        $scripts = [];
        if (static::$leadJavascriptOptions['mock_ga']) {
            $scripts[] = <<<JS
                function ga(callback){jQuery(function(){
                    callback({"get": function(){return Math.random()}})
                })}
JS;
        }
        $scripts[] = <<<JS
            try{
                ga(function(tracker){
                    jQuery('form[data-sculptor-lead]')
                    .append(jQuery('<input/>', {
                        type:'hidden',
                        name:'__sculptor[google_client_id]',
                        value: tracker.get('clientId')
                    })).append(jQuery('<input/>', {
                        type:'hidden',
                        name:'__sculptor[page_url]',
                        value: location.href + (location.hash ? '#' + location.hash : '')
                    })).append(jQuery('<input/>', {
                        type:'hidden',
                        name:'__sculptor[page_title]',
                        value: document.title
                    }))
                })
            }catch(e){
                try{console.log(e)}catch(e2){}
            }
JS;

        print "<script>\n //Sculptor API";
        foreach ($scripts as $script) {
            print "\n" . $script . "\n";
        }
        print '</script>';
    }

    /**
     * Send create new Lead API request
     * @param string $projectGuid
     * @param Lead $data
     * @return ResponseInterface|null
     */
    public function createLead($projectGuid, Lead $data)
    {
        $post = $this->extractLeadBody($data);
        return $this->callApiMethod('/lead/api/lead', $post, ['project_id' => $projectGuid]);
    }

    /**
     * Gets array of data to send to API, using some overrides (pageUrl, googleClientId)
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
     * Call REST method of Sculptor API
     * @param string $url
     * @param array $post
     * @param array $query
     * @return ResponseInterface|null Response if any
     */
    private function callApiMethod($url, $post, array $query = [])
    {
        $request = $this->httpClient->createRequest('POST', $url, [
            'body' => $post,
            'query' => $query
        ]);
        try {
            $response = $this->httpClient->send($request);
            if ($response->getStatusCode() != 200) {
                throw new BadResponseException("Failed to send lead data", $response);
            }
            return $response;
        } catch (\Exception $e) {
            $response = $e instanceof BadResponseException ? $e->getResponse() : null;
            $apiException = new ApiException($e->getMessage(), $e->getCode(), $this->httpClient, $request, $response);
            if (is_callable($callback = $this->errorHandler)) {
                call_user_func_array($callback, [$apiException]);
                return $response;
            } else {
                throw $apiException;
            }
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
     *
     * If error handler is null, ApiException will be thrown on createLead() errors
     *
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
     * Mainly for testing purposes
     * @return Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return array
     */
    public function getResolvedOptions()
    {
        return $this->resolvedOptions;
    }

}