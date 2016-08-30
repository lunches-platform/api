<?php

/** @noinspection UnSafeIsSetOverArrayInspection */
if (!isset($env)) {
    $env = getenv('APP_ENV') ?: 'dev';
}
$configFile = __DIR__."/$env.php";
if (!file_exists($configFile)) {
    die('Application is not fully configured: can\'t find config file');
}
$envConfig = require_once $configFile;
if (!is_array($envConfig)) {
    die('Application is not fully configured: config file is not valid');
}

$app = new \Lunches\Silex\Application();

foreach ($envConfig as $name => $value) {
    /** @noinspection OffsetOperationsInspection */
    $app[$name] = $value;
}
$app['debug'] = true;
$app['root_dir'] = __DIR__ . '/../';
$app['shared_dir'] = $app['root_dir'] . 'shared';
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new JDesrosiers\Silex\Provider\CorsServiceProvider(), [
    'cors.allowOrigin' => '*',
]);
$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => $app['db.options'],
]);
$app->register(new \Lunches\Silex\CloudinaryServiceProvider(
    $app['cloudinary.cloudName'],
    $app['cloudinary.apiKey'],
    $app['cloudinary.apiSecret']
));

$app['doctrine.em'] = function () use ($app) {
    return \Doctrine\ORM\EntityManager::create(
        $app['db.options'],
        $app['doctrine.config'],
        $app['doctrine.event_manager']
    );
};
$app['doctrine.config'] = function () use ($app) {
    return Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__ . '/src'], true, $app['shared_dir']);
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
        $app['lunches.factory.order']
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

$app['lunches.factory.order'] = function () use ($app) {
    return new \Lunches\Model\OrderFactory($app['doctrine.em']);
};
$app['lunches.factory.price'] = function () use ($app) {
    return new \Lunches\Model\PriceFactory($app['doctrine.em']);
};
$app->register(new Lunches\Silex\ConsoleServiceProvider(), array(
    'console.name'              => 'MyApplication',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__ . '/public_html'
));

return $app;
