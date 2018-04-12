<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 12/04/2018
 * Time: 15:45
 */

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use Weglot\Parser\Util\Text;

class DomChecker
{
    const CHECKERS_NAMESPACE = '\\Weglot\\Parser\\Check\\';

    /**
     * @var simple_html_dom
     */
    protected $dom;

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
     * @return array
     */
    protected function getCheckers()
    {
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
     * @param array $words
     * @param array $nodes
     */
    public function handle(array &$words, array &$nodes)
    {
        $discoverCaching = [];
        $checkers = $this->getCheckers();

        foreach ($checkers as $class) {
            $details = $this->getClassDetails($class);
            $property = $details['property'];

            if (!isset($discoverCaching[$element['dom']])) {
                $discoverCaching[$details['dom']] = $this->getDom()->find($details['dom']);
            }

            foreach ($discoverCaching[$details['dom']] as $k => $node) {
                $instance = new $class($node, $property);

                if ($instance->handle()) {
                    $words[] = [
                        't' => $details['wordType'],
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
    }
}
