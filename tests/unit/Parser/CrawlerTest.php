<?php

use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Util\Site;

class CrawlerTest extends \Codeception\Test\Unit
{
    /**
     * @var Parser
     */
    protected $parser;

    protected function _before()
    {
        $url = [
            'source' => 'https://weglot.com/documentation/getting-started',
            'translated' => 'https://weglot.com/fr/documentation/getting-started'
        ];

        // Config manually
        $config = new ManualConfigProvider($url['source'], BotType::HUMAN);

        // Client
        $client = new Client(getenv('WG_API_KEY'));

        // Parser
        $this->parser = new Parser($client, $config);
    }

    public function testHead()
    {
        $translated = $this->parser->translate('<script>Test</script>', 'fr', 'en');
        $this->assertEquals('<script>Test</script>', $translated);

        $translated = $this->parser->translate('<head><script>Test</script></head>', 'fr', 'en');
        $this->assertEquals('<head><script>Test</script></head>', $translated);

        $translated = $this->parser->translate('<head class="myClass" data-context="out"><script>Test</script></head>', 'fr', 'en');
        $this->assertEquals('<head class="myClass" data-context="out"><script>Test</script></head>', $translated);
    }

    public function testBody()
    {
        $translated = $this->parser->translate('<p>Test</p>', 'fr', 'en');
        $this->assertEquals('<p>Test</p>', $translated);

        $translated = $this->parser->translate('<body><p>Test</p></body>', 'fr', 'en');
        $this->assertEquals('<body><p>Test</p></body>', $translated);

        $translated = $this->parser->translate('<body class="expanded" onload="load();"><p>Test</p></body>', 'fr', 'en');
        $this->assertEquals('<body class="expanded" onload="load();"><p>Test</p></body>', $translated);
    }

    public function testWrapping()
    {
        $translated = $this->parser->translate('<html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<html><body><p>Test</p></body></html>', $translated);

        $translated = $this->parser->translate('<html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<html><body><p>Test</p></body></html>', $translated);

        $translated = $this->parser->translate('<html lang="en" prefix="og: http://ogp.me/ns#"><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<html lang="en" prefix="og: http://ogp.me/ns#"><body><p>Test</p></body></html>', $translated);

        $translated = $this->parser->translate('<html><head><title>Test</title></head></html>', 'fr', 'en');
        $this->assertEquals('<html><head><title>Test</title></head></html>', $translated);

        $translated = $this->parser->translate('<html lang="en" prefix="og: http://ogp.me/ns#"><head><title>Test</title></head></html>', 'fr', 'en');
        $this->assertEquals('<html lang="en" prefix="og: http://ogp.me/ns#"><head><title>Test</title></head></html>', $translated);
    }

    public function testDoctype()
    {
        // HTML 5
        $translated = $this->parser->translate('<!DOCTYPE html><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html><html><body><p>Test</p></body></html>', str_replace("\n", '', $translated));
        $translated = $this->parser->translate('<!DOCTYPE html><html><head><title>Test</title></head><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html><html><head><title>Test</title></head><body><p>Test</p></body></html>', str_replace("\n", '', $translated));
        $translated = $this->parser->translate('<!DOCTYPE html><html><head><title>Test</title></head></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html><html><head><title>Test</title></head></html>', $translated);

        // HTML 4.01 Strict
        $translated = $this->parser->translate('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html><body><p>Test</p></body></html>', $translated);

        // HTML 4.01 Transitional
        $translated = $this->parser->translate('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><body><p>Test</p></body></html>', $translated);

        // HTML 4.01 Frameset
        $translated = $this->parser->translate('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd"><html><body><p>Test</p></body></html>', $translated);

        // xHTML 1.0 Strict
        $translated = $this->parser->translate('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html><body><p>Test</p></body></html>', $translated);

        // xHTML 1.0 Transitional
        $translated = $this->parser->translate('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><body><p>Test</p></body></html>', $translated);

        // xHTML 1.0 Frameset
        $translated = $this->parser->translate('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"><html><body><p>Test</p></body></html>', $translated);

        // xHTML 1.1
        $translated = $this->parser->translate('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html><body><p>Test</p></body></html>', 'fr', 'en');
        $this->assertEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html><body><p>Test</p></body></html>', $translated);
    }
}
