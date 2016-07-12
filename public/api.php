<?php

$app = require_once __DIR__ . '/../bootstrap.php';
$app['debug'] = isset($_GET['debug_s']);

$app->get('/menus', 'lunches.controller.menus:getList');
$app->get('/menus/week/current', 'lunches.controller.menus:getOnCurrentWeek');
$app->get('/menus/week/next', 'lunches.controller.menus:getOnNextWeek');
$app->get('/menus/today', 'lunches.controller.menus:getToday');
$app->get('/menus/tomorrow', 'lunches.controller.menus:getTomorrow');
$app->get('/products', 'lunches.controller.products:getList');
$app->get('/products/{productId}/ingredients', 'lunches.controller.ingredients:getList');
$app->get('/orders/{orderId}', 'lunches.controller.orders:get')->bind('order');
$app->post('/orders', 'lunches.controller.orders:create');
$app->get('/ingredients', 'lunches.controller.ingredients:getList');
$app->get('/images/{imageId}', 'lunches.controller.images:get')->bind('image');
$app->post('/images', 'lunches.controller.images:create');


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
