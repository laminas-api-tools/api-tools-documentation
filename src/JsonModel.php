<?php

namespace Laminas\ApiTools\Documentation;

use Laminas\View\Model\JsonModel as BaseJsonModel;

class JsonModel extends BaseJsonModel
{
    public function terminate()
    {
        return true;
    }

    public function getVariables()
    {
        switch ($this->variables['type']) {
            case 'apiList':
                return $this->variables['apis'];
            case 'api':
            case 'service':
                return $this->variables['documentation']->toArray();
        }
    }
}
