<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function ($request, $response, $args) {

    $Home = new Home();
    $Server = new Server();

    return $this->view->render($response, 'index.twig', [
            'tools'    => $Home->getTools(),
            'projects' => $Home->getProjects(),
            'vhosts'   => $Home->getVHosts(),
            'servers'  => $Server->getInformations(),
            'version'  => $Home->getVersion()
            ]
        );
});

$app->get('/phpinfo', function ($request, $response, $args) {
        phpinfo();
        return $this->view->render($response, 'phpinfo.twig', []);
});
