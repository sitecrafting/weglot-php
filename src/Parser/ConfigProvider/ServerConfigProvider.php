<?php

namespace Weglot\Parser\ConfigProvider;

use Weglot\Parser\Util\Server;

/**
 * Class ServerConfigProvider
 * @package Weglot\Parser\ConfigProvider
 */
class ServerConfigProvider extends AbstractConfigProvider
{
    /**
     * ServerConfigProvider constructor.
     * @param null|string $title    Don't set this title if you want the Parser to parse title from DOM
     */
    public function __construct($title = null)
    {
        list($url, $bot) = $this->loadFromServer();
        parent::__construct($url, $bot, $title);
    }

    /**
     * @return array
     */
    protected function loadFromServer()
    {
        return [
            Server::fullUrl($_SERVER),
            Server::detectBot($_SERVER)
        ];
    }
}
