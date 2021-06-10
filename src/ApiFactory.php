<?php

namespace Laminas\ApiTools\Documentation;

use Laminas\ApiTools\Configuration\ModuleUtils as ConfigModuleUtils;
use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\ModuleManager\ModuleManager;

use function array_diff_key;
use function array_merge;
use function array_push;
use function dirname;
use function file_exists;
use function in_array;
use function is_array;
use function is_subclass_of;
use function ksort;
use function preg_match;
use function preg_quote;
use function sprintf;
use function str_replace;
use function strpos;
use function strstr;

class ApiFactory
{
    /** @var ModuleManager */
    protected $moduleManager;

    /** @var array */
    protected $config;

    /** @var ConfigModuleUtils */
    protected $configModuleUtils;

    /** @var array */
    protected $docs = [];

    /**
     * @param array $config
     */
    public function __construct(ModuleManager $moduleManager, $config, ConfigModuleUtils $configModuleUtils)
    {
        $this->moduleManager     = $moduleManager;
        $this->config            = $config;
        $this->configModuleUtils = $configModuleUtils;
    }

    /**
     * Create list of available API modules
     *
     * @return array
     */
    public function createApiList()
    {
        $apiToolsModules = [];
        $q               = preg_quote('\\');
        foreach ($this->moduleManager->getModules() as $moduleName) {
            $module = $this->moduleManager->getModule($moduleName);
            if ($module instanceof ApiToolsProviderInterface) {
                $versionRegex   = '#' . preg_quote($moduleName) . $q . 'V(?P<version>[^' . $q . ']+)' . $q . '#';
                $versions       = [];
                $serviceConfigs = [];
                if ($this->config['api-tools-rest']) {
                    $serviceConfigs = array_merge($serviceConfigs, $this->config['api-tools-rest']);
                }
                if ($this->config['api-tools-rpc']) {
                    $serviceConfigs = array_merge($serviceConfigs, $this->config['api-tools-rpc']);
                }

                foreach ($serviceConfigs as $serviceName => $serviceConfig) {
                    if (! preg_match($versionRegex, $serviceName, $matches)) {
                        continue;
                    }
                    $version = $matches['version'];
                    if (! in_array($version, $versions)) {
                        $versions[] = $version;
                    }
                }

                $apiToolsModules[] = [
                    'name'     => $moduleName,
                    'versions' => $versions,
                ];
            }
        }
        return $apiToolsModules;
    }

    /**
     * Create documentation details for a given API module and version
     *
     * @param string $apiName
     * @param int|string $apiVersion
     * @return Api
     */
    public function createApi($apiName, $apiVersion = 1)
    {
        $api = new Api();

        $api->setVersion($apiVersion);
        $api->setName($apiName);

        $serviceConfigs = [];
        if (! empty($this->config['api-tools-rest'])) {
            $serviceConfigs = array_merge($serviceConfigs, $this->config['api-tools-rest']);
        }
        if (! empty($this->config['api-tools-rpc'])) {
            $serviceConfigs = array_merge($serviceConfigs, $this->config['api-tools-rpc']);
        }

        // Sort services by name
        ksort($serviceConfigs);

        foreach ($serviceConfigs as $serviceName => $serviceConfig) {
            if (
                strpos($serviceName, $apiName . '\\') === 0
                && strpos($serviceName, '\V' . $api->getVersion() . '\\')
                && isset($serviceConfig['service_name'])
            ) {
                $service = $this->createService($api, $serviceConfig['service_name']);
                if ($service) {
                    $api->addService($service);
                }
            }
        }

        return $api;
    }

    /**
     * Create documentation details for a given service in a given version of
     * an API module
     *
     * @param string $serviceName
     * @return Service
     */
    public function createService(Api $api, $serviceName)
    {
        $service = new Service();
        $service->setApi($api);

        $serviceData = null;
        $isRest      = false;
        $isRpc       = false;
        $hasSegments = false;
        $hasFields   = false;

        foreach ($this->config['api-tools-rest'] as $serviceClassName => $restConfig) {
            if (
                (strpos($serviceClassName, $api->getName() . '\\') === 0)
                && isset($restConfig['service_name'])
                && ($restConfig['service_name'] === $serviceName)
                && (strstr($serviceClassName, '\\V' . $api->getVersion() . '\\') !== false)
            ) {
                $serviceData = $restConfig;
                $isRest      = true;
                $hasSegments = true;
                break;
            }
        }

        if (! $serviceData) {
            foreach ($this->config['api-tools-rpc'] as $serviceClassName => $rpcConfig) {
                if (
                    (strpos($serviceClassName, $api->getName() . '\\') === 0)
                    && isset($rpcConfig['service_name'])
                    && ($rpcConfig['service_name'] === $serviceName)
                    && (strstr($serviceClassName, '\\V' . $api->getVersion() . '\\') !== false)
                ) {
                    $serviceData           = $rpcConfig;
                    $serviceData['action'] = $this->marshalActionFromRouteConfig(
                        $serviceName,
                        $serviceClassName,
                        $rpcConfig
                    );
                    $isRpc                 = true;
                    break;
                }
            }
        }

        if (! $serviceData || ! isset($serviceClassName)) {
            return false;
        }

        $authorizations = $this->getAuthorizations($serviceClassName);

        $docsArray = $this->getDocumentationConfig($api->getName());

        $service->setName($serviceData['service_name']);
        if (isset($docsArray[$serviceClassName]['description'])) {
            $service->setDescription($docsArray[$serviceClassName]['description']);
        }

        $route = $this->config['router']['routes'][$serviceData['route_name']]['options']['route'];
        $service->setRoute(str_replace('[/v:version]', '', $route)); // remove internal version prefix, hacky
        if ($isRpc) {
            $hasSegments = $this->hasOptionalSegments($route);
        }

        if (isset($serviceData['route_identifier_name'])) {
            $service->setRouteIdentifierName($serviceData['route_identifier_name']);
        }

        $fields = [];
        if (isset($this->config['api-tools-content-validation'][$serviceClassName])) {
            $validators = $this->config['api-tools-content-validation'][$serviceClassName];
            foreach ($validators as $validatorKey => $validatorName) {
                if (isset($this->config['input_filter_specs'][$validatorName])) {
                    foreach ($this->mapFields($this->config['input_filter_specs'][$validatorName]) as $fieldData) {
                        $fields[$validatorKey][] = $this->getField($fieldData);
                    }
                    $hasFields = true;
                }
            }
        }

        $baseOperationData = $serviceData['collection_http_methods'] ?? $serviceData['http_methods'];

        $ops = [];
        foreach ($baseOperationData as $httpMethod) {
            $op = new Operation();
            $op->setHttpMethod($httpMethod);

            if ($isRest) {
                $description = $docsArray[$serviceClassName]['collection'][$httpMethod]['description'] ?? '';
                $op->setDescription($description);

                $requestDescription = $docsArray[$serviceClassName]['collection'][$httpMethod]['request'] ?? '';
                $op->setRequestDescription($requestDescription);

                $responseDescription = $docsArray[$serviceClassName]['collection'][$httpMethod]['response'] ?? '';

                $op->setResponseDescription($responseDescription);
                $op->setRequiresAuthorization(
                    $authorizations['collection'][$httpMethod] ?? false
                );

                $op->setResponseStatusCodes($this->getStatusCodes(
                    $httpMethod,
                    false,
                    $hasFields,
                    $op->requiresAuthorization()
                ));
            }

            if ($isRpc) {
                $description = $docsArray[$serviceClassName][$httpMethod]['description'] ?? '';
                $op->setDescription($description);

                $requestDescription = $docsArray[$serviceClassName][$httpMethod]['request'] ?? '';
                $op->setRequestDescription($requestDescription);

                $responseDescription = $docsArray[$serviceClassName][$httpMethod]['response'] ?? '';
                $op->setResponseDescription($responseDescription);

                $op->setRequiresAuthorization(
                    $authorizations['actions'][$serviceData['action']][$httpMethod] ?? false
                );
                $op->setResponseStatusCodes($this->getStatusCodes(
                    $httpMethod,
                    $hasSegments,
                    $hasFields,
                    $op->requiresAuthorization()
                ));
            }

            $ops[] = $op;
        }

        $service->setFields($fields);
        $service->setOperations($ops);

        if (isset($serviceData['entity_http_methods'])) {
            $ops = [];
            foreach ($serviceData['entity_http_methods'] as $httpMethod) {
                $op = new Operation();
                $op->setHttpMethod($httpMethod);

                $description = $docsArray[$serviceClassName]['entity'][$httpMethod]['description'] ?? '';
                $op->setDescription($description);

                $requestDescription = $docsArray[$serviceClassName]['entity'][$httpMethod]['request'] ?? '';
                $op->setRequestDescription($requestDescription);

                $responseDescription = $docsArray[$serviceClassName]['entity'][$httpMethod]['response'] ?? '';
                $op->setResponseDescription($responseDescription);

                $op->setRequiresAuthorization(
                    $authorizations['entity'][$httpMethod] ?? false
                );
                $op->setResponseStatusCodes($this->getStatusCodes(
                    $httpMethod,
                    true,
                    $hasFields,
                    $op->requiresAuthorization()
                ));
                $ops[] = $op;
            }
            $service->setEntityOperations($ops);
        }

        if (isset($this->config['api-tools-content-negotiation']['accept_whitelist'][$serviceClassName])) {
            $service->setRequestAcceptTypes(
                $this->config['api-tools-content-negotiation']['accept_whitelist'][$serviceClassName]
            );
        }

        if (isset($this->config['api-tools-content-negotiation']['content_type_whitelist'][$serviceClassName])) {
            $service->setRequestContentTypes(
                $this->config['api-tools-content-negotiation']['content_type_whitelist'][$serviceClassName]
            );
        }

        return $service;
    }

    /**
     * @param array $fields
     * @param string $prefix To unwind nesting of fields
     * @return array
     */
    private function mapFields(array $fields, $prefix = '')
    {
        if (isset($fields['name'])) {
            // detect usage of "name" as a field group name
            if (is_array($fields['name']) && isset($fields['name']['name'])) {
                return $this->mapFields($fields['name'], 'name');
            }

            if ($prefix) {
                $fields['name'] = sprintf('%s/%s', $prefix, $fields['name']);
            }
            return [$fields];
        }

        $flatFields = [];

        foreach ($fields as $idx => $field) {
            if (
                isset($field['type'], $field['input_filter'])
                && ($field['type'] === CollectionInputFilter::class
                    || is_subclass_of($field['type'], CollectionInputFilter::class))
            ) {
                $filteredFields = array_diff_key($field['input_filter'], ['type' => 0]);
                $fullindex      = $prefix ? sprintf('%s/%s[]', $prefix, $idx) : $idx . '[]';
                $flatFields     = array_merge($flatFields, $this->mapFields($filteredFields, $fullindex));
                continue;
            }

            if (
                isset($field['type'])
                && is_subclass_of($field['type'], InputFilterInterface::class)
            ) {
                $filteredFields = array_diff_key($field, ['type' => 0]);
                $fullindex      = $prefix ? sprintf('%s/%s', $prefix, $idx) : $idx;
                $flatFields     = array_merge($flatFields, $this->mapFields($filteredFields, $fullindex));
                continue;
            }

            $flatFields = array_merge($flatFields, $this->mapFields($field, $prefix));
        }

        return $flatFields;
    }

    /**
     * @param array $fieldData
     * @return Field
     */
    private function getField(array $fieldData)
    {
        $field = new Field();

        $field->setName($fieldData['name']);
        if (isset($fieldData['description'])) {
            $field->setDescription($fieldData['description']);
        }

        if (isset($fieldData['type'])) {
            $field->setType($fieldData['type']);
        }

        if (isset($fieldData['field_type'])) {
            $field->setFieldType($fieldData['field_type']);
        }

        if (isset($fieldData['example'])) {
            $field->setExample($fieldData['example']);
        }

        $required = isset($fieldData['required']) ? (bool) $fieldData['required'] : false;
        $field->setRequired($required);

        return $field;
    }

    /**
     * Retrieve the documentation for a given API module
     *
     * @param string $apiName
     * @return array
     */
    protected function getDocumentationConfig($apiName)
    {
        if (isset($this->docs[$apiName])) {
            return $this->docs[$apiName];
        }

        $moduleConfigPath = $this->configModuleUtils->getModuleConfigPath($apiName);
        $docConfigPath    = dirname($moduleConfigPath) . '/documentation.config.php';
        if (file_exists($docConfigPath)) {
            $this->docs[$apiName] = include $docConfigPath;
        } else {
            $this->docs[$apiName] = [];
        }

        return $this->docs[$apiName];
    }

    /**
     * Retrieve authorization data for the given service
     *
     * @param string $serviceName
     * @return array
     */
    protected function getAuthorizations($serviceName)
    {
        if (! isset($this->config['api-tools-mvc-auth']['authorization'][$serviceName])) {
            return [];
        }
        return $this->config['api-tools-mvc-auth']['authorization'][$serviceName];
    }

    /**
     * Determine the RPC action name based on the routing configuration
     *
     * @param string $serviceName
     * @param string $serviceClassName
     * @param array $config
     * @return string
     */
    protected function marshalActionFromRouteConfig($serviceName, $serviceClassName, array $config)
    {
        if (! isset($config['route_name'])) {
            return $serviceName;
        }
        if (! isset($this->config['router']['routes'][$config['route_name']])) {
            return $serviceName;
        }
        $route = $this->config['router']['routes'][$config['route_name']];
        if (! isset($route['options']['defaults']['action'])) {
            return $serviceName;
        }

        return $route['options']['defaults']['action'];
    }

    protected function hasOptionalSegments(string $route): bool
    {
        return preg_match('#\[.*?:.+\]#', $route);
    }

    /** @psalm-return array{code:string, message:string} */
    protected function getStatusCodes(
        string $httpMethod,
        bool $hasOptionalSegments,
        bool $hasValidation,
        bool $requiresAuthorization
    ): array {
        $statusCodes = [
            ['code' => '406', 'message' => 'Not Acceptable'],
            ['code' => '415', 'message' => 'Unsupported Media Type'],
        ];

        switch ($httpMethod) {
            case 'GET':
                array_push($statusCodes, ['code' => '200', 'message' => 'OK']);
                if ($hasOptionalSegments) {
                    array_push($statusCodes, ['code' => '404', 'message' => 'Not Found']);
                }
                break;
            case 'DELETE':
                array_push($statusCodes, ['code' => '204', 'message' => 'No Content']);
                if ($hasOptionalSegments) {
                    array_push($statusCodes, ['code' => '404', 'message' => 'Not Found']);
                }
                break;
            case 'POST':
                array_push($statusCodes, ['code' => '201', 'message' => 'Created']);
                if ($hasOptionalSegments) {
                    array_push($statusCodes, ['code' => '404', 'message' => 'Not Found']);
                }
                if ($hasValidation) {
                    array_push($statusCodes, ['code' => '400', 'message' => 'Client Error']);
                    array_push($statusCodes, ['code' => '422', 'message' => 'Unprocessable Entity']);
                }
                break;
            case 'PATCH':
            case 'PUT':
                array_push($statusCodes, ['code' => '200', 'message' => 'OK']);
                if ($hasOptionalSegments) {
                    array_push($statusCodes, ['code' => '404', 'message' => 'Not Found']);
                }
                if ($hasValidation) {
                    array_push($statusCodes, ['code' => '400', 'message' => 'Client Error']);
                    array_push($statusCodes, ['code' => '422', 'message' => 'Unprocessable Entity']);
                }
                break;
        }

        if ($requiresAuthorization) {
            array_push($statusCodes, ['code' => '401', 'message' => 'Unauthorized']);
            array_push($statusCodes, ['code' => '403', 'message' => 'Forbidden']);
        }

        return $statusCodes;
    }
}
