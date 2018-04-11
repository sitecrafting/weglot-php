<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 15:20
 */

namespace Weglot\Parser\ConfigProvider;

use Weglot\Parser\Util\Server;

class ServerConfigProvider extends AbstractConfigProvider
{
    /**
     * ServerConfigProvider constructor.
     * @param null|string $title    Don't set this title if you want the Parser to parse title from DOM
     */
    public function __construct($title = null)
    {
        list($url, $bot) = $this->loadFromServer();
        parent::__construct($title, $url, $bot);
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
