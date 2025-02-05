<?php

use App\Factory\ViewFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Yiisoft\EventDispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\View\WebView;
use Yiisoft\Yii\Web\Emitter\EmitterInterface;
use Yiisoft\Yii\Web\Emitter\SapiEmitter;
use Yiisoft\Yii\Web\MiddlewareDispatcher;
use Yiisoft\Router\RouterInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Router\UrlMatcherInterface;
use App\Factory\MiddlewareDispatcherFactory;
use App\Factory\AppRouterFactory;
use Yiisoft\Yii\Web\Session\Session;
use Yiisoft\Yii\Web\Session\SessionInterface;

return [
    \Psr\Container\ContainerInterface::class => function (\Psr\Container\ContainerInterface $container) {
        return $container;
    },

    // PSR-17 factories:
    RequestFactoryInterface::class => Psr17Factory::class,
    ServerRequestFactoryInterface::class => Psr17Factory::class,
    ResponseFactoryInterface::class => Psr17Factory::class,
    StreamFactoryInterface::class => Psr17Factory::class,
    UriFactoryInterface::class => Psr17Factory::class,
    UploadedFileFactoryInterface::class => Psr17Factory::class,

    // custom stuff
    EmitterInterface::class => SapiEmitter::class,
    RouterInterface::class => new AppRouterFactory(),
    UrlMatcherInterface::class => Reference::to(RouterInterface::class),
    UrlGeneratorInterface::class => Reference::to(RouterInterface::class),
    MiddlewareDispatcher::class => new MiddlewareDispatcherFactory(),
    SessionInterface::class => [
        '__class' => Session::class,
        '__construct()' => [
            ['cookie_secure' => 0]
        ],
    ],

    // event dispatcher
    ListenerProviderInterface::class => Provider::class,
    EventDispatcherInterface::class => Dispatcher::class,

    // view
    WebView::class => new ViewFactory(),

    // user
    \Yiisoft\Yii\Web\User\IdentityRepositoryInterface::class => \App\Repository\UserRepository::class,
    \Yiisoft\Yii\Web\User\User::class => function (\Psr\Container\ContainerInterface $container) {
        $session = $container->get(SessionInterface::class);
        $identityRepository = $container->get(\Yiisoft\Yii\Web\User\IdentityRepositoryInterface::class);
        $eventDispatcher = $container->get(EventDispatcherInterface::class);
        $user = new Yiisoft\Yii\Web\User\User($identityRepository, $eventDispatcher);
        $user->setSession($session);
        return $user;
    },
];
