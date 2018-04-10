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
     * @param string $title
     */
    public function __construct($title)
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
