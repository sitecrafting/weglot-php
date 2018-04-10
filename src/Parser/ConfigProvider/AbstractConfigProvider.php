<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 15:24
 */

namespace Weglot\Parser\ConfigProvider;

abstract class AbstractConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $bot;

    /**
     * AbstractConfigProvider constructor.
     * @param string $title
     * @param string $url
     * @param int $bot
     */
    public function __construct($title, $url, $bot)
    {
        $this
            ->setTitle($title)
            ->setUrl($url)
            ->setBot($bot);
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param int $bot
     * @return $this
     */
    public function setBot($bot)
    {
        $this->bot = $bot;

        return $this;
    }

    /**
     * @return int
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * {@inheritdoc}
     */
    public function asArray()
    {
        return [
            'title' => $this->getTitle(),
            'request_url' => $this->getUrl(),
            'bot' => $this->getBot()
        ];
    }
}
