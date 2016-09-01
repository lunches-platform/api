<?php

use Lunches\Silex\Application;

$app = require_once __DIR__ . '/bootstrap.php';
$app['debug'] = isset($_GET['debug_s']);

/**
 * Menus
 */
$app->get('/menus', 'lunches.controller.menus:getList');
$app->get('/menus/week/current', 'lunches.controller.menus:getOnCurrentWeek');
$app->get('/menus/week/next', 'lunches.controller.menus:getOnNextWeek');
$app->get('/menus/today', 'lunches.controller.menus:getToday');
$app->get('/menus/tomorrow', 'lunches.controller.menus:getTomorrow');
$app->get('/menus/{date}', 'lunches.controller.menus:getConcrete');

/**
 * Products
 */
$app->get('/products', 'lunches.controller.products:getList');
$app->get('/products/{productId}/ingredients', 'lunches.controller.ingredients:getList');

/**
 * Prices
 */
$app->get('/prices', 'lunches.controller.prices:getList');
$app->get('/prices/{date}', 'lunches.controller.prices:get');
$app->put('/prices/{date}', 'lunches.controller.prices:create');

/**
 * Users
 */
$app->get('/users', 'lunches.controller.users:getList');
$app->get('/users/{username}', 'lunches.controller.users:get');
$app->put('/users/{username}', 'lunches.controller.users:update');
$app->post('/users', 'lunches.controller.users:create');

/**
 * User orders
 */
$app->get('/users/{user}/orders', 'lunches.controller.orders:getByUser');


/**
 * Transactions
 */
$app->get('/transactions', 'lunches.controller.transactions:getList');
$app->post('/transactions', 'lunches.controller.transactions:create');
$app->get('/transactions/{transactionId}', 'lunches.controller.transactions:get');
$app->delete('/transactions/{transactionId}', 'lunches.controller.transactions:delete');

/**
 * Orders
 */
$app->get('/orders', 'lunches.controller.orders:getList');
$app->get('/orders/{orderId}', 'lunches.controller.orders:get')->bind('order');
$app->post('/orders', 'lunches.controller.orders:create');
$app->post('/orders/{orderId}/cancel', 'lunches.controller.orders:cancel');
$app->post('/orders/{orderId}/reject', 'lunches.controller.orders:reject');
$app->put('/orders/{orderId}', 'lunches.controller.orders:update');

$app->get('/ingredients', 'lunches.controller.ingredients:getList');

/**
 * Images
 */
$app->get('/images/{imageId}', 'lunches.controller.images:get')->bind('image');
$app->post('/images', 'lunches.controller.images:create');

$app->get('/products/{productId}/images/{imageId}', 'lunches.controller.product-images:get')->bind('product-image');
$app->put('/products/{productId}/images/{imageId}', 'lunches.controller.product-images:create');
$app->get('/products/{productId}/images', 'lunches.controller.product-images:getList');
$app->put('/products/{productId}/images/{imageId}/cover', 'lunches.controller.product-images:markCover');

$app->get('/paymentCard', function(Application $app) {
    $card = $app['db']->fetchAssoc('SELECT number, holder FROM payment_card');

    return new \Symfony\Component\HttpFoundation\JsonResponse([
        'number' => $card['number'],
        'holder' => $card['holder'],
    ]);
});


$app->before(function (Symfony\Component\HttpFoundation\Request $request) {

    $header = $request->headers->get('Content-Type');
    if (0 === strpos($header, 'application/json') ||
        0 === strpos($header, 'application/x-www-form-urlencoded')
    ) {
        $data = json_decode($request->getContent(), true);
        $request->request->add(is_array($data) ? $data : []);
    }
}, 10000) ;
$app->after($app["cors"]);
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }
    $message = 'Server error';
    if ($code === 404) {
        $message = 'The requested page could not be found.';
    }

    return new \Symfony\Component\HttpFoundation\JsonResponse([
        'errMsg' => $message
    ]);
});

$app->run();
