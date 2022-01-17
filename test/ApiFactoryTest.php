<?php

namespace LaminasTest\ApiTools\Documentation;

use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Documentation\Api;
use Laminas\ApiTools\Documentation\ApiFactory;
use Laminas\ApiTools\Documentation\Field;
use Laminas\ApiTools\Documentation\Operation;
use Laminas\ApiTools\Documentation\Service;
use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

use function array_filter;
use function array_key_exists;
use function array_shift;
use function in_array;
use function is_array;
use function sprintf;
use function var_export;

class ApiFactoryTest extends TestCase
{
    /** @var ApiFactory */
    protected $apiFactory;

    /**
     * @var array
     * @psalm-var array<array-key, array{code:string, message:string}>
     */
    protected $expectedStatusCodes = [
        [
            'code'    => '200',
            'message' => 'OK',
        ],
        [
            'code'    => '201',
            'message' => 'Created',
        ],
        [
            'code'    => '204',
            'message' => 'No Content',
        ],
        [
            'code'    => '400',
            'message' => 'Client Error',
        ],
        [
            'code'    => '401',
            'message' => 'Unauthorized',
        ],
        [
            'code'    => '403',
            'message' => 'Forbidden',
        ],
        [
            'code'    => '404',
            'message' => 'Not Found',
        ],
        [
            'code'    => '406',
            'message' => 'Not Acceptable',
        ],
        [
            'code'    => '415',
            'message' => 'Unsupported Media Type',
        ],
        [
            'code'    => '422',
            'message' => 'Unprocessable Entity',
        ],
    ];

    protected function setUp(): void
    {
        $mockModule = $this->createMock(ApiToolsProviderInterface::class);

        $moduleManager = $this->createMock(ModuleManager::class);
        $moduleManager->method('getModules')->willReturn(['Test']);
        $moduleManager->method('getModule')->with('Test')->willReturn($mockModule);

        $moduleUtils = $this->createMock(ModuleUtils::class);
        $moduleUtils
            ->method('getModuleConfigPath')
            ->with('Test')
            ->willReturn(__DIR__ . '/TestAsset/module-config/module.config.php');

        $this->apiFactory = new ApiFactory(
            $moduleManager,
            include __DIR__ . '/TestAsset/module-config/module.config.php',
            $moduleUtils
        );
    }

    /**
     * @param int|int[]|string|string[] $expectedCodes
     * @param int[]|string[] $actualCodes
     * @psalm-param int|string|list<int|string> $expectedCodes
     * @psalm-param list<int|string> $actualCodes
     */
    public function assertContainsStatusCodes($expectedCodes, array $actualCodes, string $message = ''): void
    {
        if (! is_array($expectedCodes)) {
            $expectedCodes = [$expectedCodes];
        }

        $expectedCodePairs = array_filter($this->expectedStatusCodes, function ($code) use ($expectedCodes) {
            if (! is_array($code)) {
                return false;
            }
            if (! array_key_exists('code', $code)) {
                return false;
            }
            if (! in_array($code['code'], $expectedCodes)) {
                return false;
            }

            return true;
        });

        $this->assertNotEmpty($expectedCodePairs, sprintf(
            'No codes provided, or no known codes match: %s',
            var_export($expectedCodes, true)
        ));

        foreach ($expectedCodePairs as $code) {
            $this->assertContains($code, $actualCodes, sprintf(
                "Failed to find code %s in actual codes:\n%s\n",
                $code['code'],
                var_export($actualCodes, true)
            ));
        }
    }

    public function testCreateApiList(): void
    {
        $apiList = $this->apiFactory->createApiList();
        $this->assertCount(1, $apiList);
        $api = array_shift($apiList);
        $this->assertArrayHasKey('name', $api);
        $this->assertEquals('Test', $api['name']);
        $this->assertArrayHasKey('versions', $api);
        $this->assertIsArray($api['versions']);
        $this->assertEquals(['1'], $api['versions']);
    }

    public function testCreateApi(): void
    {
        $api = $this->apiFactory->createApi('Test', 1);
        $this->assertInstanceOf(Api::class, $api);

        $this->assertEquals('Test', $api->getName());
        $this->assertEquals(1, $api->getVersion());
        $this->assertCount(7, $api->getServices());
    }

    public function testCreateRestService(): void
    {
        $docConfig = include __DIR__ . '/TestAsset/module-config/documentation.config.php';
        $api       = $this->apiFactory->createApi('Test', 1);

        $service = $this->apiFactory->createService($api, 'FooBar');
        $this->assertInstanceOf(Service::class, $service);

        $this->assertEquals('FooBar', $service->getName());
        $this->assertEquals($docConfig['Test\V1\Rest\FooBar\Controller']['description'], $service->getDescription());

        $fields = $service->getFields('input_filter');
        Assert::isNonEmptyList($fields);
        $this->assertCount(5, $fields);
        $this->assertInstanceOf(Field::class, $fields[0]);
        $this->assertEquals('foogoober/subgoober', $fields[2]->getName());
        $this->assertEquals('foofoogoober/subgoober/subgoober', $fields[3]->getName());

        $ops = $service->getOperations();
        $this->assertCount(2, $ops);

        foreach ($ops as $operation) {
            $this->assertInstanceOf(Operation::class, $operation);
            $statusCodes = $operation->getResponseStatusCodes();
            switch ($operation->getHttpMethod()) {
                case 'GET':
                    $this->assertFalse($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(['406', '415', '200'], $statusCodes);
                    break;
                case 'POST':
                    $this->assertTrue($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(
                        ['406', '415', '400', '422', '401', '403', '201'],
                        $statusCodes
                    );
                    break;
                default:
                    $this->fail('Unexpected HTTP method encountered: ' . $operation->getHttpMethod());
            }
        }

        $eOps = $service->getEntityOperations();
        $this->assertCount(4, $eOps);

        foreach ($eOps as $operation) {
            $this->assertInstanceOf(Operation::class, $operation);
            $statusCodes = $operation->getResponseStatusCodes();
            switch ($operation->getHttpMethod()) {
                case 'GET':
                    $this->assertFalse($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(['406', '415', '404', '200'], $statusCodes);
                    break;
                case 'PATCH':
                case 'PUT':
                    $this->assertTrue($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(
                        ['406', '415', '400', '422', '401', '403', '200'],
                        $statusCodes
                    );
                    break;
                case 'DELETE':
                    $this->assertTrue($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(['406', '415', '401', '403', '204'], $statusCodes);
                    break;
                default:
                    $this->fail('Unexpected entity HTTP method encountered: ' . $operation->getHttpMethod());
            }
        }
    }

    public function testCreateRestServiceWithCollection(): void
    {
        $docConfig = include __DIR__ . '/TestAsset/module-config/documentation.config.php';
        $api       = $this->apiFactory->createApi('Test', 1);

        $service = $this->apiFactory->createService($api, 'FooBarCollection');
        $this->assertInstanceOf(Service::class, $service);

        $this->assertEquals('FooBarCollection', $service->getName());
        $this->assertEquals(
            $docConfig['Test\V1\Rest\FooBarCollection\Controller']['description'],
            $service->getDescription()
        );

        $fields = $service->getFields('input_filter');
        Assert::isNonEmptyList($fields);
        $this->assertCount(2, $fields);

        $field = $fields[0];
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('FooBarCollection[]/FooBar', $field->getName());

        $field = $fields[1];
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('AnotherCollection[]/FooBar', $field->getName());
    }

    public function testCreateRestArtistsService(): void
    {
        $docConfig = include __DIR__ . '/TestAsset/module-config/documentation.config.php';

        $api = $this->apiFactory->createApi('Test', 1);

        $service = $this->apiFactory->createService($api, 'Bands');
        self::assertInstanceOf(Service::class, $service);

        self::assertEquals('Bands', $service->getName());
        self::assertEquals(
            $docConfig['Test\V1\Rest\Bands\Controller']['description'],
            $service->getDescription()
        );

        $fields = $service->getFields('input_filter');
        Assert::isNonEmptyList($fields);
        $this->assertCount(11, $fields);

        $name = $fields[0];
        $this->assertInstanceOf(Field::class, $name);
        $this->assertSame('name', $name->getName());

        $firstName = $fields[1];
        $this->assertInstanceOf(Field::class, $firstName);
        $this->assertSame('artists[]/first_name', $firstName->getName());

        $lastName = $fields[2];
        $this->assertInstanceOf(Field::class, $lastName);
        $this->assertSame('artists[]/last_name', $lastName->getName());

        $title = $fields[3];
        $this->assertInstanceOf(Field::class, $title);
        $this->assertSame('debut_album/title', $title->getName());

        $releaseDate = $fields[4];
        $this->assertInstanceOf(Field::class, $releaseDate);
        $this->assertSame('debut_album/release_date', $releaseDate->getName());

        $trackNumber = $fields[5];
        $this->assertInstanceOf(Field::class, $trackNumber);
        $this->assertSame('debut_album/tracks[]/number', $trackNumber->getName());

        $trackTitle = $fields[6];
        $this->assertInstanceOf(Field::class, $trackTitle);
        $this->assertSame('debut_album/tracks[]/title', $trackTitle->getName());

        $albumTitle = $fields[7];
        $this->assertInstanceOf(Field::class, $albumTitle);
        $this->assertSame('albums[]/title', $albumTitle->getName());

        $albumReleaseDate = $fields[8];
        $this->assertInstanceOf(Field::class, $albumReleaseDate);
        $this->assertSame('albums[]/release_date', $albumReleaseDate->getName());

        $albumTrackNumber = $fields[9];
        $this->assertInstanceOf(Field::class, $albumTrackNumber);
        $this->assertSame('albums[]/tracks[]/number', $albumTrackNumber->getName());

        $albumTrackTitle = $fields[10];
        $this->assertInstanceOf(Field::class, $albumTrackTitle);
        $this->assertSame('albums[]/tracks[]/title', $albumTrackTitle->getName());
    }

    public function testCreateRpcService(): void
    {
        $docConfig = include __DIR__ . '/TestAsset/module-config/documentation.config.php';
        $api       = $this->apiFactory->createApi('Test', 1);

        $service = $this->apiFactory->createService($api, 'Ping');
        $this->assertInstanceOf(Service::class, $service);

        $this->assertEquals('Ping', $service->getName());
        $this->assertEquals($docConfig['Test\V1\Rpc\Ping\Controller']['description'], $service->getDescription());

        $ops = $service->getOperations();
        $this->assertCount(1, $ops);

        foreach ($ops as $operation) {
            $this->assertInstanceOf(Operation::class, $operation);
            $statusCodes = $operation->getResponseStatusCodes();
            switch ($operation->getHttpMethod()) {
                case 'GET':
                    $this->assertEquals(
                        $docConfig['Test\V1\Rpc\Ping\Controller']['GET']['description'],
                        $operation->getDescription()
                    );
                    $this->assertEquals(
                        $docConfig['Test\V1\Rpc\Ping\Controller']['GET']['request'],
                        $operation->getRequestDescription()
                    );
                    $this->assertEquals(
                        $docConfig['Test\V1\Rpc\Ping\Controller']['GET']['response'],
                        $operation->getResponseDescription()
                    );
                    $this->assertFalse($operation->requiresAuthorization());
                    $this->assertContainsStatusCodes(['406', '415', '200'], $statusCodes);
                    break;
                default:
                    $this->fail('Unexpected HTTP method encountered: ' . $operation->getHttpMethod());
            }
        }
    }

    public function testGetFieldsForEntityMethods(): void
    {
        $api     = $this->apiFactory->createApi('Test', 1);
        $service = $this->apiFactory->createService($api, 'EntityFields');
        $this->assertInstanceOf(Service::class, $service);
        $this->assertEquals('EntityFields', $service->getName());
        $this->assertCount(1, $service->getFields('PUT'));
    }
}
