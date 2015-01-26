Sculptor API client.

Landing pages (Leads) API is only available for now.

##Leads example

###Catch Google ClientID
First we need to catch some form submitting event, for getting Google Analytics ClientID:

```php
// use it on page that contains form matching with selector
velosipedist\SculptorClient\SculptorClient::injectClientId('#my-target-form');

//... somewhere before or after this injection
print "<form id='my-target-form'><!--any inputs and markup--></form>";
```

After this page loaded, the form will contain hidden input with name `__sculptor_google_client_id`.

###After goal reached

After form was submitted to any php script for post-processing, we must create API connection and send form data.

```php
// handling successful form submisson.
// Form data maybe already sent, now we should register new Lead at Sculptor CRM
$api = new velosipedist\SculptorClient\SculptorClient('your API key', 'Available Project ID');

// prepare if something going wrong
$api->setErrorHandler(function($e){
  print "<span class='alert alert-warning'>Lead sending error: {$e->getMessage()}</span>";
  return false;
});

// enable PHPConsole logs
$api->setDebugMode(true);

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
$result = $api->createLead($lead);
if($result){
  // API response has 'message' and 'data' keys
  $message = $result->json()['message'];
  print "<span class='alert alert-success'>$message</span>";
}

// if it is AJAX script, you can just print {"message": "OK"}, or even empty string with 200 status.
```

### Errors handling and debugging

SculptorClient has two debugging approaches that can be used separately or simultaneously.

#### 1. PhpConsole (recommended)

Most safe and non-destructive way to debug API calls is using [PhpConsole](https://github.com/barbushin/php-console) library developed for [Chrome extension](https://chrome.google.com/webstore/detail/php-console/nfhmhhlpfleoednkpnnnkolmclajemef).

Just enable debug mode:

```php
$api->setDebugMode(true);
```

And set API key as password in Chrome extension prompt.

#### 2. ApiException handling
It is less secure way that can be used if you have no possibility to use Chrome extension.

You can set default handler for every request like in previous code listing. `ApiException` will contain any errors data useful for debugging:

```php
$api->setErrorHandler(function(velosipedist\SculptorClient\exception\ApiException $e){
  // display client error explanation
  print "<span class='alert alert-warning'>Lead sending error: {$e->getMessage()}</span>";

  // inspect request sent, what can be wrong
  var_dump($e->getRequest());

  // or maybe client misconfigured
  var_dump($e->getClient());
});

//...
// now any API call will just return false on every request error
$result = $api->createLead($lead);
```

  > :exclamation: **IMPORTANT** :exclamation: Don't forget to remove debugging info on public production stage, to hide private keys!

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
$result = $api->createLead($lead);

// ...etc
```