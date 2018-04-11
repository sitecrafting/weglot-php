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
     * @param null|string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return null|string
     */
    public function getTitle();

    /**
     * @param bool $autoDiscoverTitle
     * @return $this
     */
    public function setAutoDiscoverTitle($autoDiscoverTitle);

    /**
     * @return bool
     */
    public function getAutoDiscoverTitle();

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
