<?php
declare(strict_types=1);

return [

    'settings' => [

        'db' => [
            'prat' => [
                'driver' => $_ENV['prat.driver'],
                'host' => $_ENV['prat.host'],
                'dbname' => $_ENV['prat.database'],
                'user' => $_ENV['prat.username'],
                'password' => $_ENV['prat.password'],
            ],
            'pat' => [
                'driver' => $_ENV['pat.driver'],
                'host' => $_ENV['pat.host'],
                'dbname' => $_ENV['pat.database'],
                'user' => $_ENV['pat.username'],
                'password' => $_ENV['pat.password'],
            ],
            'auth' => [
                'driver' => $_ENV['auth.driver'],
                'host' => $_ENV['auth.host'],
                'dbname' => $_ENV['auth.database'],
                'user' => $_ENV['auth.username'],
                'password' => $_ENV['auth.password'],
            ],
            'rdv' => [
                'driver' => $_ENV['rdv.driver'],
                'host' => $_ENV['rdv.host'],
                'dbname' => $_ENV['rdv.database'],
                'user' => $_ENV['rdv.username'],
                'password' => $_ENV['rdv.password'],
            ],
        ],
    ]
];
