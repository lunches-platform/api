<?php

require_once __DIR__.'/vendor/autoload.php';

$app = new \Lunches\Silex\Application();

$app['debug'] = true;
$app['root_dir'] = __DIR__ . '/';
$app['shared_dir'] = $app['root_dir'] . 'shared';
$app['db.options'] = [
    'host' => '127.0.0.1',
    'driver'   => 'pdo_mysql',
    'user'   => 'root',
    'password' => 'root',
    'dbname'   => 'lunches',
    'driverOptions' => [ 1002=>'SET NAMES utf8' ]
];
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new JDesrosiers\Silex\Provider\CorsServiceProvider(), [
    'cors.allowOrigin' => '*',
]);
$app->register(new \Lunches\Silex\CloudinaryServiceProvider('df0ff62zx', '182632897348152', 'oNJJFfwvphafDODbTYyMbVQZXPc'));

$app['doctrine.em'] = function () use ($app) {
    return \Doctrine\ORM\EntityManager::create(
        $app['db.options'],
        $app['doctrine.config'],
        $app['doctrine.event_manager']
    );
};
$app['doctrine.config'] = function () use ($app) {
    return Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__.'/src'], true, $app['shared_dir']);
};

$app['doctrine.event_manager'] = function () use ($app) {

    /** @var \Doctrine\ORM\Configuration $config */
    $config = $app['doctrine.config'];
    $reader = $config->getMetadataDriverImpl()->getReader();

    $timestampable = new \Gedmo\Timestampable\TimestampableListener();
    $timestampable->setAnnotationReader($reader);

    $events = new \Doctrine\Common\EventManager();
    $events->addEventSubscriber($timestampable);

    return $events;
};

$app['lunches.controller.products'] = function () use ($app) {
    return new \Lunches\Controller\ProductsController(
        $app['doctrine.em']
    );
};
$app['lunches.controller.ingredients'] = function () use ($app) {
    return new \Lunches\Controller\IngredientsController(
        $app['doctrine.em']
    );
};
$app['lunches.controller.images'] = function () use ($app) {
    return new \Lunches\Controller\ImagesController(
        $app['doctrine.em']
    );
};
$app['lunches.controller.product-images'] = function () use ($app) {
    return new \Lunches\Controller\ProductImagesController(
        $app['doctrine.em']
    );
};
$app['lunches.controller.menus'] = function () use ($app) {
    return new \Lunches\Controller\MenusController(
        $app['doctrine.em']
    );
};
$app['lunches.controller.orders'] = function () use ($app) {
    return new \Lunches\Controller\OrdersController(
        $app['doctrine.em'],
        $app['lunches.factory.order'],
        $app['lunches.validator.order']
    );
};
$app['lunches.controller.prices'] = function () use ($app) {
    return new \Lunches\Controller\PricesController(
        $app['doctrine.em'],
        $app['lunches.factory.price']
    );
};
$app['lunches.controller.users'] = function () use ($app) {
    return new \Lunches\Controller\UsersController(
        $app['doctrine.em']
    );
};
$app['lunches.controller.transactions'] = function () use ($app) {
    return new \Lunches\Controller\TransactionsController(
        $app['doctrine.em']
    );
};
$app['lunches.validator.order'] = function () use ($app) {
    return new \Lunches\Validator\OrderValidator();
};

$app['lunches.factory.order'] = function () use ($app) {
    return new \Lunches\Model\OrderFactory($app['doctrine.em']);
};
$app['lunches.factory.price'] = function () use ($app) {
    return new \Lunches\Model\PriceFactory($app['doctrine.em']);
};

return $app;
