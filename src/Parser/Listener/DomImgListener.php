<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Exception\ParserCrawlerAfterListenerException;
use Weglot\Parser\Parser;

class DomImgListener extends AbstractCrawlerAfterListener
{
    /**
     * {@inheritdoc}
     */
    protected function xpath()
    {
        return '//img[not(ancestor-or-self::*[@' .Parser::ATTRIBUTE_NO_TRANSLATE. '])]/@*[name()=\'src\' or name()=\'alt\']' ;
    }

    /**
     * {@inheritdoc}
     */
    protected function type(\DOMNode $node)
    {
        $type = null;
        switch ($node->localName) {
            case 'alt':
                $type = WordType::IMG_ALT;
                break;
            case 'src':
                $type = WordType::IMG_SRC;
                break;
            default:
                throw new ParserCrawlerAfterListenerException('Found no word type for this image attribute.');
                break;
        }
        return $type;
    }
}
