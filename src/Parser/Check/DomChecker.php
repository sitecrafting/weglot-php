<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 12/04/2018
 * Time: 15:45
 */

namespace Weglot\Parser\Check;

use SimpleHtmlDom\simple_html_dom_node;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\WordEntry;
use Weglot\Parser\Util\Text;

class DomChecker extends AbstractChecker
{
    const CHECKERS_NAMESPACE = '\\Weglot\\Parser\\Check\\Dom\\';

    /**
     * @var array
     */
    protected $discoverCaching;

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

        $files = array_diff(scandir(__DIR__ . '/Dom'), ['AbstractChecker.php', '..', '.']);
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
            $class,
            $class::DOM,
            $class::PROPERTY,
            $class::WORD_TYPE
        ];
    }

    /**
     * @return array
     * @throws InvalidWordTypeException
     */
    public function handle()
    {
        $nodes = [];
        $checkers = $this->getCheckers();

        foreach ($checkers as $class) {
            list($class, $dom, $property, $wordType) = $this->getClassDetails($class);

            $discoveringNodes = $this->discoverCachingGet($dom);
            foreach ($discoveringNodes as $k => $node) {
                $instance = new $class($node, $property);

                if ($instance->handle()) {
                    $this->getParser()->getWords()->addOne(new WordEntry($node->$property, $wordType));

                    $nodes[] = [
                        'node' => $node,
                        'class' => $class,
                        'property' => $property,
                    ];
                }
            }
        }

        return $nodes;
    }
}
