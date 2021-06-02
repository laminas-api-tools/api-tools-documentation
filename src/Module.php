<?php

namespace Laminas\ApiTools\Documentation;

class Module
{
    /** @return array<string, mixed> */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
