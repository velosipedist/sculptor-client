Sculptor API client.

For now landing pages (Leads) API is only available.

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

$lead = new velosipedist\SculptorClient\Lead(
  $formData['name'],
  $formData['phone'],
  $formData['email'],
  $formData['cityname'],
  $formData['city_id'],
  null, // if we don't know city global id
  ['something'=>'custom in form data']
);

// if no error handler set, then Exception will be thrown on fail
$result = $api->createLead($lead);
if($result){
  print "<span class='alert alert-success'>Lead saved successfully</span>";
}

// if it is AJAX script, you can just print {"message": "OK"}, or even empty string with 200 status.
```

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