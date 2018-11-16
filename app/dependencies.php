<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings');
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['App\Controller\Instagram'] = function ($c) use($app) {
    $settings = $c->get('settings');
    return new \App\Controller\Instagram($settings);
};


global $app;