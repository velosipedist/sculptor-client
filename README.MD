Sculptor REST API client.

Landing pages (Leads) API is only available for now.

## Requirements

 - PHP 5.4+
 - JQuery library on front-end

## Installation

Just download phar package from [latest of releases](//github.com/velosipedist/sculptor-client/releases) and include it in your project.

##Lead example

### Catch form submit
Just add `data-sculptor-lead` attribute at form you want to track:

```html
<form id='my-target-form' data-sculptor-lead><!--any inputs and markup--></form>
```

After this page loaded, the form will contain hidden inputs named like `__sculptor[***]`.

#### Javascript tuning

Use following method to rewrite default options:

```php
\velosipedist\SculptorClient\SculptorClient::configureLeadsJavascript([
  'enabled' => false, // default: true
  'mock_ga' => true, // default: false
]);
```

`enabled`
Is Leads API enabled. `true` by default, set `false` to disable scripts injections and, for example, write `__sculptor` data handling manually.

`mock_ga`
If `true`, then missing `ga` javascript function will be mocked to produce testing clientId's.
Useful with `testing` client option.

>> Warning: any of API clients instantiated on page will use ga mock,
>> and won't send data to real `ga` function 


### After lead goal is reached

After form was submitted to any php script for post-processing, we must create API connection and send form data.

```php
// handling successful form submisson.
// Form data maybe already sent, now we should register new Lead at Sculptor CRM
$api = new velosipedist\SculptorClient\SculptorClient('your API key');

$lead = new velosipedist\SculptorClient\Lead(
  $formData['name'],
  $formData['phone'],
  $formData['email'],
  $formData['cityname'],
  $formData['city_id'],
  'default', // or any other of registered slugs in project
  ['something'=>'custom in form data']
);

// if no error handler set, then Exception will be thrown on fail
try{
  $result = $api->createLead('Available Project ID', $lead);
  // API response has 'message' and 'data' keys
  $message = $result->json()['message'];
  print "<span class='alert alert-success'>$message</span>";
} catch (\Exception $e) {
  // something went wrong
  print "<span class='alert alert-danger'>{$e->getMessage()}</span>";
}

// if it is AJAX script, you can just print {"message": "OK"}, or even empty string with 200 status.
```

### Options

Any possible options can be passed at client initialization:
 
```php
$api = new SculptorClient('your API key', ['option1'=>'value' /* ... */]);
```

#### base_url

At most cases for override default https:// address with http:// protocol.

```php
$api = new SculptorClient('your API key', ['base_url' => 'http://sculptor.tochno-tochno.ru']);
```

#### guzzle

If you want to customize Guzzle default options, pass `guzzle` array:

```php
$api = new SculptorClient('your API key', [
  'guzzle' => ['cert' => 'path_to_https.crt', 'handler' => /* custom handler setup */]
]);
```

#### testing

Set it to `true` for sending API calls marked as test only.


### Errors handling and debugging

Most safe and non-destructive way to debug API calls is using [PhpConsole](https://github.com/barbushin/php-console) library developed for [Chrome extension](https://chrome.google.com/webstore/detail/php-console/nfhmhhlpfleoednkpnnnkolmclajemef).
Don't forget to protect it with client password to avoid API key leaks from open PhpConsole logging!

SculptorClient has special exception class to handle any runtime issue that needs debugging all request and response parameters.

#### 1. ApiException auto-handling
You can set default handler for every request like in previous code listing. `ApiException` will contain any errors data useful for debugging:

```php
$api->setErrorHandler(function(velosipedist\SculptorClient\exception\ApiException $e){
  // display client error explanation
  print "<span class='alert alert-warning'>Lead sending error: {$e->getMessage()}</span>";

  // inspect request sent, what can be wrong
  var_dump($e->getRequest());
  
  // inspect response, if got. May be null if request failed
  var_dump($e->getResponse());

  // or maybe client misconfigured
  var_dump($e->getClient());
});

//...
// now any API call will just return false on every request error
$result = $api->createLead($lead);
```

  > :exclamation: **IMPORTANT** :exclamation: Don't forget to remove debugging info on public production stage, to hide private keys!

#### 2. Catch exceptions manulaly

This is just more flexible way to debug client and handle errors on runtime. Just use try-catch construction:

```php
try {
  $result = $api->createLead('Available Project ID', $lead);
} catch(velosipedist\SculptorClient\exception\ApiException $e) {
  // you have the same data as in previous example
}
```

### Override some fields

Lead API requires to pageUrl & googleClientId fields to be sent. By default these values will be guessed from form request.

But you can override them explicitly if needed:

```php
// ...got form request and $api created
// maybe we want to substitute or just test API client.
// Set another id
$api->setGoogleClientId('my-known-client-id');

// Maybe referring page must be tuned by campaign conditions
$api->setPageUrl('http://domain.com/common/landing');

// now send the landing result
$result = $api->createLead('Available Project ID', $lead);

// ...etc
```

##Test form script

For development and testing purposes, use build/test.php script, right from build directory:

`php -S localhost:8081 test.php`

Make sure you have installed Composer dependencies first. Also you'll need config file `test-config.php` with following contents:

```php
<? return [
  'api_key' => '<your API key>',
];
```

Now you can open localhost:8081 and test API data sending, optionally overriding GA client id with custom value.
Leads created at this page will be marked as hidden for excluding from real leads list.