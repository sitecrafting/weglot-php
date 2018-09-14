<?php

namespace Weglot\Parser\Listener;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Exception\ParserCrawlerAfterListenerException;
use Weglot\Parser\Parser;
use Weglot\Util\Text;

/**
 * Class AbstractCrawlerAfterListener.
 * @package Weglot\Parser\Listener
 */
abstract class AbstractCrawlerAfterListener
{
    /**
     * @param ParserCrawlerAfterEvent $event
     *
     * @throws ParserCrawlerAfterListenerException
     * @throws InvalidWordTypeException
     */
    public function __invoke(ParserCrawlerAfterEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();
        $xpath = $this->xpath();

        if ($xpath === '' || strpos($xpath, Parser::ATTRIBUTE_NO_TRANSLATE) === false) {
            throw new ParserCrawlerAfterListenerException('XPath query is empty or doesn\'t exclude non-translable blocks.');
        }

        $nodes = $crawler->filterXPath($xpath);
        foreach ($nodes as $node) {
            $value = $this->value($node);
            $value = $this->fix($node, $value);

            if ($this->validation($node, $value)) {
                $event->getContext()->addWord($value, $this->replaceCallback($node), $this->type($node));
            }
        }
    }

    /**
     * Returns current listener XPath query
     *
     * @return string
     */
    abstract protected function xpath();

    /**
     * Return current node used value
     *
     * @param \DOMNode $node
     * @return string
     */
    protected function value(\DOMNode $node)
    {
        if ($node instanceof \DOMAttr) {
            return $node->value;
        }
        return $node->textContent;
    }

    /**
     * Fix given value based on node type
     *
     * @param \DOMNode $node
     * @param string $value
     * @return string
     */
    protected function fix(\DOMNode $node, $value)
    {
        $fixed = $value;
        if ($node instanceof \DOMText) {
            $fixed = str_replace("\n", '', $fixed);
            $fixed = preg_replace('/\s+/u', ' ', $fixed);
            $fixed = str_replace('<', '&lt;', $fixed);
            $fixed = str_replace('>', '&gt;', $fixed);
        }

        return $fixed;
    }

    /**
     * Some default checks for our value depending on node type
     *
     * @param \DOMNode $node
     * @param string $value
     * @return bool
     */
    protected function validation(\DOMNode $node, $value)
    {
        $boolean =
            Text::fullTrim($value) !== '' &&
            !in_array($node->parentNode->localName, ['script', 'style', 'noscript', 'code']) &&
            !is_numeric(Text::fullTrim($value)) &&
            !preg_match('/^\d+%$/', Text::fullTrim($value)) &&
            !Text::contains(Text::fullTrim($value), '[vc_') &&
            !Text::contains(Text::fullTrim($value), '<?php');

        if ($node instanceof \DOMText) {
            $boolean = $boolean && strpos($value, Parser::ATTRIBUTE_NO_TRANSLATE) === false;
        }

        return $boolean;
    }

    /**
     * Callback used to replace text with translated version
     *
     * @param \DOMNode $node
     * @return callable
     */
    protected function replaceCallback(\DOMNode $node)
    {
        return function ($text) use ($node) {
            $attribute = '';
            if ($node instanceof \DOMText) {
                $attribute = 'nodeValue';
            } elseif ($node instanceof \DOMAttr) {
                $attribute = 'value';
            }

            if ($attribute === '') {
                throw new ParserCrawlerAfterListenerException('No callback behavior set for this node type.');
            }

            // reserved character in XML: &
            $text = str_replace('&amp;', '&', $text);
            $text = str_replace('&', '&amp;', $text);
            $text = str_replace('&lt;', '<', $text);
            $text = str_replace('&gt;', '>', $text);

            $node->$attribute = $text;
        };
    }

    /**
     * Get the type of the word given by this kind of node
     *
     * @param \DOMNode $node
     * @return string
     *
     * @throws ParserCrawlerAfterListenerException
     */
    protected function type(\DOMNode $node)
    {
        $type = null;
        if ($node instanceof \DOMText) {
            $type = WordType::TEXT;
        } elseif ($node instanceof \DOMAttr) {
            $type = WordType::VALUE;
        } else {
            throw new ParserCrawlerAfterListenerException('No word type set for this kind of node.');
        }
        return $type;
    }
}
