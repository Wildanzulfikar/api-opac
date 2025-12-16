<?php
use Slim\App;
use App\Controllers\BiblioController;
use App\Controllers\LoanController;
use App\Controllers\ItemController;

return function (App $app) {
    $app->get('/health', function ($req, $res) {
        $res->getBody()->write(json_encode(['status' => 'ok']));
        return $res->withHeader('Content-Type', 'application/json');
    });

    $app->get('/biblio', [BiblioController::class, 'index']);
    $app->get('/biblio/{id}', [BiblioController::class, 'show']);
    $app->post('/biblio', [BiblioController::class, 'store']);
    $app->put('/biblio/{id}', [BiblioController::class, 'update']);
    $app->delete('/biblio/{id}', [BiblioController::class, 'delete']);

    $app->get('/loan', [LoanController::class, 'index']);
    $app->get('/loan/{id}', [LoanController::class, 'show']);
    $app->post('/loan', [LoanController::class, 'store']);
    $app->put('/loan/{id}', [LoanController::class, 'update']);
    $app->delete('/loan/{id}', [LoanController::class, 'delete']);
    $app->put('/loan/{id}/return', [LoanController::class, 'markReturned']);

    $app->get('/item', [ItemController::class, 'index']);
    $app->get('/item/{id}', [ItemController::class, 'show']);
    $app->post('/item', [ItemController::class, 'store']);
    $app->put('/item/{id}', [ItemController::class, 'update']);
    $app->delete('/item/{id}', [ItemController::class, 'delete']);
};
