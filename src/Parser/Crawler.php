<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler as BaseCrawler;

class Crawler extends BaseCrawler
{
    /**
     * @var bool
     */
    protected $hasHead = false;

    /**
     * @var bool
     */
    protected $hasBody = false;

    /**
     * {@inheritdoc}
     */
    public function addHtmlContent($content, $charset = 'UTF-8')
    {
        $xml = false;

        try {
            $xml = simplexml_load_string($content);
        } catch (\Exception $e) {
            // ignore
        }

        if ($xml !== false) {
            $elements = $xml->xpath('//body');
            $this->hasBody = count($elements) > 0;

            $elements = $xml->xpath('//head');
            $this->hasHead = count($elements) > 0;
        }

        parent::addHtmlContent($content, $charset);
    }


    /**
     * {@inheritdoc}
     */
    public function html()
    {
        if ($this->count() === 0) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $html = '';
        foreach ($this->getNode(0)->childNodes as $child) {
            $html .= $child->ownerDocument->saveHTML($child);
        }

        return $this->cleaningHtml($html);
    }

    /**
     * Cleaning HTML from parts it should not contains
     *
     * @param string $html
     *
     * @return string
     */
    protected function cleaningHtml($html)
    {
        $xml = false;
        $childrens = null;

        try {
            $xml = simplexml_load_string($html);
        } catch (\Exception $e) {
            // ignore
        }

        if ($xml !== false) {
            if (!$this->hasHead && $xml->getName() === 'head') {
                $childrens = $xml->xpath('//head/child::*');
            }
            if (!$this->hasBody && $xml->getName() === 'body') {
                $childrens = $xml->xpath('//body/child::*');
            }

            if ($childrens !== null) {
                $temp = '';
                foreach ($childrens as $children) {
                    $temp .= $children->saveXML();
                }
                $html = html_entity_decode($temp);
            }
        }

        return $html;
    }
}
