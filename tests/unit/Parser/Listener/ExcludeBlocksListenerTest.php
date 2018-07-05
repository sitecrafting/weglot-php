<?php

use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use Weglot\Parser\Parser;
use Weglot\Parser\Event\AbstractEvent;

class ExcludeBlocksListenerTest extends AbstractParserCrawlerAfterEventTest
{
    protected $excluded = ['section.advantages'];

    protected function parserNew(Client $client, ConfigProviderInterface $config)
    {
        $this->parser = new Parser($client, $config, $this->excluded);
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $crawler = $event->getContext()->getCrawler();
        foreach ($this->excluded as $exception) {
            $nodes = $crawler->filter($exception);
            foreach ($nodes as $node) {
                $this->assertTrue($node->hasAttribute(Parser::ATTRIBUTE_NO_TRANSLATE));
            }
        }
    }

    public function checks($translated)
    {
        $this->assertEquals($this->excluded, $this->parser->getExcludeBlocks());
        $this->assertContains('Integrated within minutes', $translated);
        $this->assertContains('Manage your content', $translated);
        $this->assertContains('Reliable and powerful', $translated);
    }
}
