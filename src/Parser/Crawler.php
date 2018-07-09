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
        if (preg_match('/<head(?:.*?)?>(?:.*?)<\/head>/i', $content)) {
            $this->hasHead = true;
        }
        if (preg_match('/<body(?:.*?)?>(?:.*?)<\/body>/i', $content)) {
            $this->hasBody = true;
        }

        parent::addHtmlContent($content, $charset);
    }


    /**
     * {@inheritdoc}
     */
    public function html()
    {
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
