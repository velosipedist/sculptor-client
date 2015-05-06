<?php
use velosipedist\SculptorClient\exception\ApiException;
use velosipedist\SculptorClient\Lead;
use velosipedist\SculptorClient\SculptorClient;

if (preg_match('/\.(?:png|jpg|jpeg|gif|ico)$/i', $_SERVER["REQUEST_URI"])) {
    return false;
}
require_once __DIR__ . '/../vendor/autoload.php';

\PhpConsole\Helper::register();

SculptorClient::configureLeadsJavascript([
    'mock_ga' => true
]);
$config = require __DIR__ . '/test-config.php';
$api = new SculptorClient($config['api_key'], [
    'testing' => true
]);
$projectChoices = $api->listProjects();
if (isset($_POST['send'])) {
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
    $response = $api->createLead($_POST['project_id'], $lead);
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
    <title>Sculptor API Test</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-12">
            <br/>

            <form method="post" id="only-form" data-sculptor-lead="1">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="form-group">
                            <select name="project_id" class="form-control">
                                <? foreach ($projectChoices as $p): ?>
                                    <option value="<?= $p->getGuid() ?>"
                                        <?= (isset($_POST['project_id']) && ($p->getGuid() == $_POST['project_id'])
                                            ? 'selected' : '') ?>
                                        ><?= $p->getName() ?></option>
                                <? endforeach ?>
                            </select>
                        </div>
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
                                                       value="<?= (isset($_POST['google_client_id']) ?
                                                           $_POST['google_client_id'] : '') ?>"/>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <input type="submit" value="Send" class="btn btn-primary" name="send"/>
                        <a class="btn btn-default" href="/"><i class="glyphicon glyphicon-refresh"></i></a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
</body>
</html>