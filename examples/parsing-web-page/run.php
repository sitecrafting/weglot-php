<?php

require_once __DIR__. '/vendor/autoload.php';

use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;

// DotEnv
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Url to parse
$url = 'https://weglot.com/documentation/getting-started';

// Config with $_SERVER variables
$_SERVER['SERVER_NAME'] = 'weglot.com';
$_SERVER['REQUEST_URI'] = '/documentation/getting-started';
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PROTOCOL'] = 'http//';
$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTP_USER_AGENT'] = 'Google';
$config = new ServerConfigProvider();

// Config manually
$config = new ManualConfigProvider($url, BotType::HUMAN);

// Fetching url content
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$content = curl_exec($ch);
curl_close($ch);

// Client
$client = new Client(getenv('WG_API_KEY'));
$parser = new Parser($client, 'en', 'de', $config);

// Run the Parser
$translatedContent = $parser->translate($content);

// dumping returned object
var_dump($translatedContent);
