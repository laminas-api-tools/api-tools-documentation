<?php

namespace LaminasTest\ApiTools\Documentation;

use Laminas\ApiTools\Documentation\ApiFactory;
use Laminas\ApiTools\Documentation\Controller;
use Laminas\ApiTools\Documentation\ControllerFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\ServerUrl;
use PHPUnit\Framework\TestCase;

class ControllerFactoryTest extends TestCase
{
    public function testCreateController(): void
    {
        $apiFactory = $this->getMockBuilder(ApiFactory::class)->disableOriginalConstructor()->getMock();

        $viewHelpers = new ServiceManager();
        $viewHelpers->setService('ServerUrl', new ServerUrl());
        $viewHelpers->setService('BasePath', new BasePath());

        $container = new ServiceManager();
        $container->setService('ViewHelperManager', $viewHelpers);
        $container->setService(
            ApiFactory::class,
            $apiFactory
        );

        $controller = (new ControllerFactory())->createService($container);

        self::assertInstanceOf(Controller::class, $controller);
    }
}
