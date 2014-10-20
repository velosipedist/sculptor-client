<?php
require_once 'sculptor.phar';
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
        'http://test.sculptor.tochno-tochno.ru'
    );
    $api->setErrorHandler(function (ApiException $e) {
        print "<div class='alert alert-danger'>";
        var_dump($e->getMessage());
//        var_dump($e->getRequest()->getBody());
        print "</div>";
        return false;
    });
    $result = $api->createLead(new Lead(
        $_POST['name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['city']
    ));
    if ($result) {
        print "<div class='alert alert-success'>";
        var_dump($result->json());
        print "</div>";
    }
}
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
        <div class="col-sm-6">
            Fill me:
            <form method="post" id="only-form">
                <div class="form-controls"><input type="text" name="name" placeholder="Name"/></div>
                <div class="form-controls"><input type="text" name="phone" placeholder="Phone"/></div>
                <div class="form-controls"><input type="text" name="email" placeholder="Email"/></div>
                <div class="form-controls"><input type="text" name="city" placeholder="City"/></div>
                <input type="submit" value="Send" class="btn btn-primary" name="send"/>
            </form>
        </div>
    </div>
</div>
</body>
</html>