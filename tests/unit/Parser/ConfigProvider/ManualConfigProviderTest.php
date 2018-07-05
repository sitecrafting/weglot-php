<?php

use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;

class ManualConfigProviderTest extends AbstractConfigProviderTest
{
    protected function _before()
    {
        $this->config = new ManualConfigProvider(
            'https://www.google.com/',
            BotType::HUMAN
        );
    }
}