<?php

require_once __DIR__. '/vendor/autoload.php';

use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;

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
$content = curl_exec($ch);
curl_close($ch);

// Client
$client = new Client(getenv('WG_API_KEY'));
$parser = new Parser($client, 'en', 'fr', $config);

// Run the Parser
$translatedContent = $parser->translate($content);

// dumping returned object
var_dump($translatedContent);
