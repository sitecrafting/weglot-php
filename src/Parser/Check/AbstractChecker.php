<?php

namespace Weglot\Parser\Check;

use SimpleHtmlDom\simple_html_dom_node;
use Weglot\Parser\Parser;
use Weglot\Parser\Util\Text;

/**
 * Class AbstractChecker
 * @package Weglot\Parser\Check
 */
abstract class AbstractChecker
{
    /**
     * @var simple_html_dom_node
     */
    protected $node;

    /**
     * @var string
     */
    protected $property;

    /**
     * AbstractChecker constructor.
     * @param simple_html_dom_node $node
     * @param string $property
     */
    public function __construct(simple_html_dom_node $node, $property)
    {
        $this
            ->setNode($node)
            ->setProperty($property);
    }

    /**
     * @param simple_html_dom_node $node
     * @return $this
     */
    public function setNode(simple_html_dom_node $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * @return simple_html_dom_node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param string $property
     * @return $this
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        return $this->defaultCheck() && $this->check();
    }

    /**
     * @return bool
     */
    protected function defaultCheck()
    {
        $property = $this->property;

        return (
            Text::fullTrim($this->node->$property) != '' &&
            !$this->hasAncestorAttribute($this->node, Parser::ATTRIBUTE_NO_TRANSLATE)
        );
    }

    /**
     * @return bool
     */
    abstract protected function check();

    /**
     * @TODO: move this function to weglot/simplehtmldom library
     *
     * @param simple_html_dom_node $node
     * @param string $attribute
     * @return bool
     */
    private function hasAncestorAttribute(simple_html_dom_node $node, $attribute)
    {
        $currentNode = $node;

        if (isset($currentNode->$attribute)) {
            return true;
        }

        while ($currentNode->parent() && $currentNode->parent()->tag != 'html') {
            if (isset($currentNode->parent()->$attribute)) {
                return true;
            } else {
                $currentNode = $currentNode->parent();
            }
        }
        return false;
    }
}
