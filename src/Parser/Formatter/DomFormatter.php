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
        $translated_words = $this->getTranslated()->getOutputWords();

        for ($i = 0; $i < \count($nodes); ++$i) {
            $currentNode = $nodes[$i];

            if ($translated_words[$i] !== null) {
                $currentTranslated = $translated_words[$i]->getWord();

                $this->metaContent($currentNode, $currentTranslated);
                $this->imageSource($currentNode, $currentTranslated, $i);
            }
        }
    }

    /**
     * @param array $details
     * @param string $translated
     */
    protected function metaContent(array $details, $translated) {
        $property = $details['property'];

        if ($details['class']::ESCAPE_SPECIAL_CHAR) {
            $details['node']->$property = htmlspecialchars($translated);
        } else {
            $details['node']->$property = $translated;
        }

        if(array_key_exists('attributes' , $details)) {
            foreach ($details['attributes'] as $k => $attributes) {
                $attributeString = "";
                foreach ($attributes as $key => $attribute) {
                    $attributeString .= $key."=\"".$attribute."\" ";
                }
                $details['node']->$property = str_replace($k.'=""', $attributeString, $details['node']->$property);
            }
        }

    }

    protected function imageSource(array $details, $translated, $index) {
        $words = $this->getTranslated()->getInputWords();

            if ($details['class'] === '\Weglot\Parser\Check\Dom\ImageSource') {
                $details['node']->src = $translated;
                if ($details['node']->hasAttribute('srcset') &&
                    $details['node']->srcset != '' &&
                    $translated != $words[$index]->getWord()) {
                    $details['node']->srcset = '';
                }
        }
    }
}
