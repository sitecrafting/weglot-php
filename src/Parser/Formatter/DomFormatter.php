<?php

namespace Weglot\Parser\Formatter;

use Weglot\Parser\Check\Dom\ImageSource;
use Weglot\Parser\Check\Dom\MetaContent;

/**
 * Class DomFormatter
 * @package Weglot\Parser\Formatter
 */
class DomFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $nodes)
    {
        $words = $this->getTranslated()->getInputWords();
        $translated_words = $this->getTranslated()->getOutputWords();

        for ($i = 0; $i < count($nodes); ++$i) {
            $currentNode = $nodes[$i];
            $property = $currentNode['property'];

            if ($translated_words[$i] !== null) {
                $current_translated = $translated_words[$i]->getWord();

                if ($currentNode['class'] instanceof MetaContent) {
                    $currentNode['node']->$property = htmlspecialchars($current_translated);
                } else {
                    $currentNode['node']->$property = $current_translated;
                }

                if ($currentNode['class'] instanceof ImageSource) {
                    $currentNode['node']->src = $current_translated;
                    if ($currentNode['node']->hasAttribute('srcset') &&
                        $currentNode['node']->srcset != '' &&
                        $current_translated != $words[$i]->getWord()) {
                        $currentNode['node']->srcset = '';
                    }
                }
            }
        }
    }
}
