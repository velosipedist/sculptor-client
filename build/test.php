<?php
require_once 'sculptor_old.phar';
require_once 'faker.phar';
//require_once __DIR__ . '/../vendor/autoload.php';
use velosipedist\SculptorClient\exception\ApiException;
use velosipedist\SculptorClient\Lead;
use velosipedist\SculptorClient\SculptorClient;

SculptorClient::injectClientId('#only-form');
if (isset($_POST['send'])) {
    $config = require __DIR__ . '/test-config.php';
    $api = new SculptorClient(
        $config['api_key'],
        $config['project_id'],
        'post',
        'http://test.sculptor.tochno-tochno.ru',
        ['verify' => false]
    );
    $api->setDebugMode(true);
    $api->setErrorHandler(function (ApiException $e) {
        print "<div class='alert alert-danger'>";
        print $e->getMessage();
        print "</div>";
    });
    $result = $api->createLead(new Lead(
        $_POST['name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['city'],
        null,
        'default',
        ['zip' => $_POST['zip']]
    ));
    if ($result) {
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript">
        //simulate Google lib
        window.ga = function (callback) {
            callback.apply(null, [window.ga]);
        };
        window.ga.get = function (anything) {
            return 'test-client-id';
        }
    </script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <br/>

            <div class="well">Fill me:
                <form method="post" id="only-form">
                    <div class="form-group">
                        <input class="form-control" type="text" name="name" placeholder="Name"
                               value="<?= $faker->firstName . ' ' . $faker->lastName ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="phone" placeholder="Phone"
                                                   value="<?= $faker->phoneNumber ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="email" placeholder="Email"
                                                   value="<?= $faker->email ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="city" placeholder="City"
                                                   value="<?= $faker->city ?>"/>
                    </div>
                    <div class="form-group"><input class="form-control" type="text" name="zip" placeholder="ZIP CODE"
                                                   value="<?= $faker->postcode ?>"/>
                    </div>
                    <input type="submit" value="Send" class="btn btn-primary" name="send"/>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>