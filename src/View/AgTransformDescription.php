<?php

namespace Laminas\ApiTools\Documentation\View;

use Laminas\View\Helper\AbstractHelper;
use Michelf\MarkdownExtra;

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
     * @param  string $description The raw Laminas API Tools description.
     * @return string The resulting transformed description.
     */
    public function __invoke($description)
    {
        return MarkdownExtra::defaultTransform($description);
    }
}
