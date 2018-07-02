<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Parser;

final class DomLinkListener extends AbstractCrawlerAfterListener
{
    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'href',
        'data-value',
        'data-tooltip',
        'data-title',
        'data-text',
        'data-hover',
        'data-content'
    ];

    /**
     * @var array
     */
    protected $hrefExtensions = [
        'pdf',
        'rar',
        'docx'
    ];

    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        $selectors = [];
        foreach ($this->attributes as $attribute) {
            $selectors[] = 'name() = \'' .$attribute. '\'';
        }

        return '//a[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@*[' .implode(' or ', $selectors). ']';
    }

    /**
     * {@inheritdoc}
     */
    protected function type(\DOMNode $node)
    {
        $type = WordType::TEXT;
        if ($node->localName === 'href') {
            $type = WordType::PDF_HREF;
        }
        return $type;
    }

    /**
     * {@inheritdoc}
     */
    protected function validation(\DOMNode $node, $value)
    {
        $extended = true;
        $boolean = parent::validation($node, $value);

        if ($node->localName === 'href') {
            $extended = false;
            foreach ($this->hrefExtensions as $extension) {
                $start = (\strlen($extension) + 1) * -1;
                $extended = $extended || (strtolower(substr($value, $start)) === ('.' .$extension));
            }
        }

        return $boolean && $extended;
    }
}
