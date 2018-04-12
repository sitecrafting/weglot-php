<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 12/04/2018
 * Time: 15:45
 */

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use SimpleHtmlDom\simple_html_dom_node;
use Weglot\Parser\Util\Text;

class DomChecker
{
    const CHECKERS_NAMESPACE = '\\Weglot\\Parser\\Check\\';

    /**
     * @var simple_html_dom
     */
    protected $dom;

    /**
     * @var array
     */
    protected $discoverCaching;

    public function __construct(simple_html_dom $dom)
    {
        $this->setDom($dom);
    }

    /**
     * @param simple_html_dom $dom
     * @return $this
     */
    public function setDom(simple_html_dom $dom)
    {
        $this->dom = $dom;

        return $this;
    }

    /**
     * @return simple_html_dom
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @return $this
     */
    public function resetDiscoverCaching()
    {
        $this->discoverCaching = [];

        return $this;
    }

    /**
     * @param string $domToSearch
     * @return simple_html_dom_node
     */
    public function discoverCachingGet($domToSearch)
    {
        if (!isset($discoverCaching[$domToSearch])) {
            $this->discoverCaching[$domToSearch] = $this->getDom()->find($domToSearch);
        }

        return $this->discoverCaching[$domToSearch];
    }

    /**
     * @return array
     */
    protected function getCheckers()
    {
        $this->resetDiscoverCaching();

        $files = array_diff(scandir(__DIR__. '/Check'), ['AbstractChecker.php', '..', '.']);
        return array_map(function ($filename) {
            return Text::removeFileExtension($filename);
        }, $files);
    }

    /**
     * @param string $class
     * @return array
     */
    private function getClassDetails($class)
    {
        $class = self::CHECKERS_NAMESPACE. $class;
        return [
            'class' => $class,
            'dom' => $class::DOM,
            'property' => $class::PROPERTY,
            'wordType' => $class::WORD_TYPE

        ];
    }

    /**
     * @return array
     */
    public function handle()
    {
        $words = [];
        $nodes = [];
        $checkers = $this->getCheckers();

        foreach ($checkers as $class) {
            list($class, $dom, $property, $wordType) = $this->getClassDetails($class);

            $nodes = $this->discoverCachingGet($dom);
            foreach ($nodes as $k => $node) {
                $instance = new $class($node, $property);

                if ($instance->handle()) {
                    $words[] = [
                        't' => $wordType,
                        'w' => $node->$property,
                    ];

                    $nodes[] = [
                        'node' => $node,
                        'class' => $class,
                        'property' => $property,
                    ];
                }
            }
        }

        return [
            $words,
            $nodes
        ];
    }
}
