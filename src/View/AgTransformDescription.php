<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation\View;

use \Michelf\MarkdownExtra;
use Laminas\View\Helper\AbstractHelper;

/**
 * View helper used to transform a raw Laminas API Tools description into a specific format (only Markdown is currently
 * supported).
 *
 * @see https://github.com/michelf/php-markdown
 */
class AgTransformDescription extends AbstractHelper
{
    /**
     * Transform an Laminas API Tools raw description into a specific format (only Markdown is currently supported).
     *
     * @param  string $string The raw Laminas API Tools description.
     * @return string The resulting transformed description.
     */
    public function __invoke($description)
    {
        return MarkdownExtra::defaultTransform($description);
    }
}
