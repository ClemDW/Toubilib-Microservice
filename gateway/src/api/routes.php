<?php
declare(strict_types=1);

use Slim\App;
use Gateway\Api\Actions\ListerPraticiensAction;

return function (App $app): App {

    $app->get('/praticiens', ListerPraticiensAction::class);

    return $app;
};