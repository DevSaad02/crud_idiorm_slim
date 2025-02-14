<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\UserController;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Actions\User\ListUsersAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        // $group->get('', ListUsersAction::class);
        // $group->get('/{id}', ViewUserAction::class);
        $group->get('', [UserController::class, 'index']);
        $group->get('/list', [UserController::class, 'getUsers']);
        $group->post('/store', [UserController::class, 'store']);
        $group->post('/edit', [UserController::class, 'edit']);
        $group->put('/update', [UserController::class, 'update']);
        $group->delete('/delete', [UserController::class, 'delete']);
    });
};
