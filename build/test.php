<?php
require_once __DIR__ . '/../vendor/autoload.php';

use velosipedist\SculptorClient\exception\ApiException;
use velosipedist\SculptorClient\Lead;
use velosipedist\SculptorClient\SculptorClient;

SculptorClient::configureLeadsJavascript([
    'mock_ga' => true
]);

if (isset($_POST['send'])) {
    $config = require __DIR__ . '/test-config.php';
    $api = new SculptorClient($config['api_key'], [
        'testing' => true
    ]);
    $api->setErrorHandler(function (ApiException $e) {
        print "<div class='alert alert-danger'>";
        print $e->getMessage();
        print "</div>";
    });
    $lead = new Lead(
        $_POST['name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['city'],
        null,
        'default',
        ['zip' => $_POST['zip']]
    );
    if ($_POST['google_client_id']) {
        $api->setGoogleClientId(trim($_POST['google_client_id']));
    }
    $response = $api->createLead($config['project_id'], $lead);
    if ($response) {
        print "<div class='alert alert-success'>Success</div>";
    }
}
$faker = Faker\Factory::create('ru_RU');
?>
<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Test</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <br/>

            <div class="well">Fill me:
                <form method="post" id="only-form" data-sculptor-lead="1">
                    <div class="form-group">
                        <input class="form-control" type="text" name="name" placeholder="Name"
                               value="<?= $faker->firstName . ' ' . $faker->lastName ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="phone"
                                                   placeholder="Phone"
                                                   value="<?= $faker->phoneNumber ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="email"
                                                   placeholder="Email"
                                                   value="<?= $faker->email ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="city"
                                                   placeholder="City"
                                                   value="<?= $faker->city ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="zip"
                                                   placeholder="ZIP CODE"
                                                   value="<?= $faker->postcode ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="google_client_id"
                                                   placeholder="GA Client ID override"
                                                   value=""/>
                    </div>
                    <input type="submit" value="Send" class="btn btn-primary" name="send"/>
                    <a class="btn btn-default" href="/"><i class="glyphicon glyphicon-refresh"></i></a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>