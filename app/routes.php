<?php
$app->get('/instagram/{profile:[a-zA-Z0-9]+}/[{maxId}]', App\Controller\Instagram::class . ':api');

