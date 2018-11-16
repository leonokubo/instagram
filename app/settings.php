<?php
$settings = [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => true, // Allow the web server to send the content-length header
        'url_datasync' => 'http://billing.backend:8815/datasync/callback/HUBTIM',
        'authorization_tim' => 'njzlmdrjymrmntjly2jkmjaznwe0mmy0odlkngrhy',
        'globalURL' => 'http://globalsdp-homol.whitelabel.com.br:8888',
        'sdpOiURL'  => 'globalsdp-homol.whitelabel.com.br:8888/oi/v2/backend/billing',
        'template_path' => realpath(dirname(__DIR__) . '/app/view/.'),
        // Configuracao Log4
        'logger' => [
            'configuration_path' => realpath(dirname(__DIR__) . '/lib/log4php/configuration.xml')
        ],
        'redis' => [
            "scheme" => "tcp",
            "host" => "cloudvision-redis",
            "port" => "6379",
            'database' => 13,
            'alias' => 'billing',
        ],
        'gcFunction' => [
            'URL'    => 'https://us-central1-automl-vision-219821.cloudfunctions.net/instagram-check',
            'PROJECTID'                  => 'automl-vision-219821',
            'FUNCTION'                   => 'instagram-check',
            'PAYMENT_STATUS_NEW_COB'     => '12',
            'PAYMENT_STATUS_CHECKCREDIT' => '11'
        ],
        'MySQL' => [
            'HOST' => 'billing.mysql',
            'USER' => 'root',
            'PASS' => 'admin',
            'PORT' => '3306'
        ],
    ],
];
return $settings;