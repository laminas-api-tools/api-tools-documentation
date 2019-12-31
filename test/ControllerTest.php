<?php

namespace LaminasTest\ApiTools\Documentation;

use Laminas\ApiTools\Documentation\ApiFactory;
use Laminas\ApiTools\Documentation\Controller;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\BasePath;
use Laminas\View\Helper\ServerUrl;
use Laminas\View\Model\ModelInterface;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    /**
     * @var MvcEvent
     */
    private $event;

    /**
     * @var ServerUrl
     */
    private $serverUrl;

    /**
     * @var BasePath
     */
    private $basePath;

    /**
     * @var ApiFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiFactory;

    protected function setUp()
    {
        $this->apiFactory = $this->getMockBuilder(ApiFactory::class)->disableOriginalConstructor()->getMock();
        $this->serverUrl = new ServerUrl();
        $this->basePath = new BasePath();

        $this->event = new MvcEvent();

        if (class_exists('Laminas\Router\RouteMatch', true)) {
            $this->event->setRouteMatch(new \Laminas\Router\RouteMatch([]));
        } elseif (class_exists('Laminas\Mvc\Router\RouteMatch', true)) {
            $this->event->setRouteMatch(new \Laminas\Mvc\Router\RouteMatch([]));
        }
    }

    public function testViewModelMissingBasePath()
    {
        $this->serverUrl->setScheme('https');
        $this->serverUrl->setHost('localhost');
        $this->basePath->setBasePath('/controller_test');

        $sut = new Controller($this->apiFactory, $this->serverUrl);
        $sut->setEvent($this->event);

        /** @var ModelInterface $viewModel */
        $viewModel = $sut->showAction();
        self::assertInstanceOf(ModelInterface::class, $viewModel);

        self::assertEquals('https://localhost', $viewModel->getVariable('baseUrl'));
    }

    public function testSetBaseUrlIntoViewModel()
    {
        $this->serverUrl->setScheme('https');
        $this->serverUrl->setHost('localhost');
        $this->basePath->setBasePath('/controller_test');

        $sut = new Controller($this->apiFactory, $this->serverUrl, $this->basePath);
        $sut->setEvent($this->event);

        /** @var ModelInterface $viewModel */
        $viewModel = $sut->showAction();
        self::assertInstanceOf(ModelInterface::class, $viewModel);

        self::assertEquals('https://localhost/controller_test', $viewModel->getVariable('baseUrl'));
    }
}
