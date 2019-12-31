<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Documentation;

use Laminas\ApiTools\Documentation\ApiFactory;
use PHPUnit_Framework_TestCase as TestCase;

class ApiFactoryTest extends TestCase
{
    protected $apiFactory;

    protected $expectedStatusCodes = array(
        array(
            'code' => '200',
            'message' => 'OK',
        ),
        array(
            'code' => '204',
            'message' => 'No Content',
        ),
        array(
            'code' => '400',
            'message' => 'Client Error',
        ),
        array(
            'code' => '401',
            'message' => 'Unauthorized',
        ),
        array(
            'code' => '403',
            'message' => 'Forbidden',
        ),
        array(
            'code' => '404',
            'message' => 'Not Found',
        ),
        array(
            'code' => '406',
            'message' => 'Not Acceptable',
        ),
        array(
            'code' => '415',
            'message' => 'Unsupported Media Type',
        ),
        array(
            'code' => '422',
            'message' => 'Unprocessable Entity',
        ),
    );

    public function setup()
    {
        $mockModule = $this->getMock('Laminas\ApiTools\Provider\ApiToolsProviderInterface');

        $moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getModules', 'getModule'))
            ->getMock();
        $moduleManager->expects($this->any())
            ->method('getModules')
            ->will($this->returnValue(array('Test')));
        $moduleManager->expects($this->any())
            ->method('getModule')
            ->will($this->returnValue($mockModule));

        $moduleUtils = $this->getMockBuilder('Laminas\ApiTools\Configuration\ModuleUtils')
            ->disableOriginalConstructor()
            ->setMethods(array('getModuleConfigPath'))
            ->getMock();
        $moduleUtils->expects($this->any())
            ->method('getModuleConfigPath')
            ->will($this->returnValue(__DIR__ . '/TestAsset/module-config/module.config.php'));

        $this->apiFactory = new ApiFactory(
            $moduleManager,
            include __DIR__ . '/TestAsset/module-config/module.config.php',
            $moduleUtils
        );
    }

    public function assertContainsStatusCodes($expectedCodes, $actualCodes, $message = '')
    {
        if (!is_array($expectedCodes)) {
            $expectedCodes = array($expectedCodes);
        }

        $expectedCodePairs = array_filter($this->expectedStatusCodes, function ($code) use ($expectedCodes) {
            if (!is_array($code)) {
                return false;
            }
            if (!array_key_exists('code', $code)) {
                return false;
            }
            if (!in_array($code['code'], $expectedCodes)) {
                return false;
            }

            return true;
        });

        if (empty($expectedCodePairs)) {
            $this->fail(sprintf(
                'No codes provided, or no known codes match: %s',
                var_export($expectedCodes, 1)
            ));
        }

        foreach ($expectedCodePairs as $code) {
            if (!in_array($code, $actualCodes, true)) {
                $this->fail(sprintf(
                    "Failed to find code %s in actual codes:\n%s\n",
                    $code['code'],
                    var_export($actualCodes, 1)
                ));
            }
        }

        return true;
    }

    public function testCreateApiList()
    {
        $apiList = $this->apiFactory->createApiList();
        $this->assertCount(1, $apiList);
        $api = array_shift($apiList);
        $this->assertArrayHasKey('name', $api);
        $this->assertEquals('Test', $api['name']);
        $this->assertArrayHasKey('versions', $api);
        $this->assertInternalType('array', $api['versions']);
        $this->assertEquals(array('1'), $api['versions']);
    }

    public function testCreateApi()
    {
        $api = $this->apiFactory->createApi('Test', 1);
        $this->assertInstanceOf('Laminas\ApiTools\Documentation\Api', $api);

        $this->assertEquals('Test', $api->getName());
        $this->assertEquals(1, $api->getVersion());
        $this->assertCount(3, $api->getServices());
    }

    public function testCreateService()
    {
        $docConfig = include __DIR__ . '/TestAsset/module-config/documentation.config.php';
        $api = $this->apiFactory->createApi('Test', 1);

        $service = $this->apiFactory->createService($api, 'FooBar');
        $this->assertInstanceOf('Laminas\ApiTools\Documentation\Service', $service);

        $this->assertEquals('FooBar', $service->getName());
        $this->assertEquals($docConfig['Test\V1\Rest\FooBar\Controller']['description'], $service->getDescription());

        $fields = $service->getFields();
        $this->assertCount(2, $fields);
        $this->assertInstanceOf('Laminas\ApiTools\Documentation\Field', $fields[0]);

        $ops = $service->getOperations();
        $this->assertCount(2, $ops);

        foreach ($ops as $operation) {
            $this->assertInstanceOf('Laminas\ApiTools\Documentation\Operation', $operation);
            $statusCodes = $operation->getResponseStatusCodes();
            switch ($operation->getHttpMethod()) {
                case 'GET':
                    $this->assertFalse($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(array('406', '415', '200'), $statusCodes);
                    break;
                case 'POST':
                    $this->assertTrue($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(array('406', '415', '400', '422', '401', '403', '200'), $statusCodes);
                    break;
                default:
                    $this->fail('Unexpected HTTP method encountered: ' . $operation->getHttpMethod());
                    break;
            }
        }

        $eOps = $service->getEntityOperations();
        $this->assertCount(4, $eOps);

        foreach ($eOps as $operation) {
            $this->assertInstanceOf('Laminas\ApiTools\Documentation\Operation', $operation);
            $statusCodes = $operation->getResponseStatusCodes();
            switch ($operation->getHttpMethod()) {
                case 'GET':
                    $this->assertFalse($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(array('406', '415', '404', '200'), $statusCodes);
                    break;
                case 'PATCH':
                case 'PUT':
                    $this->assertTrue($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(array('406', '415', '400', '422', '401', '403', '200'), $statusCodes);
                    break;
                case 'DELETE':
                    $this->assertTrue($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(array('406', '415', '401', '403', '204'), $statusCodes);
                    break;
                default:
                    $this->fail('Unexpected entity HTTP method encountered: ' . $operation->getHttpMethod());
                    break;
            }
        }
    }
}
