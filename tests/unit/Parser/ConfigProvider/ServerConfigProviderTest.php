<?php

use Weglot\Parser\ConfigProvider\ServerConfigProvider;

class ServerConfigProviderTest extends AbstractConfigProviderTest
{
    protected function _before()
    {
        $_SERVER['SERVER_NAME'] = 'www.google.com';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PROTOCOL'] = 'http//';
        $_SERVER['SERVER_PORT'] = 443;
        $_SERVER['HTTP_USER_AGENT'] = 'Google';

        $this->config = new ServerConfigProvider();
        $this->config->loadFromServer();
    }
}
