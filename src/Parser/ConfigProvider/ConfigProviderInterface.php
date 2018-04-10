<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 15:21
 */

namespace Weglot\Parser\ConfigProvider;

interface ConfigProviderInterface
{
    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param int $bot
     * @return $this
     */
    public function setBot($bot);

    /**
     * @return int
     */
    public function getBot();

    /**
     * @return array
     */
    public function asArray();
}
