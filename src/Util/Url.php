<?php

namespace Weglot\Util;

/**
 * Class Url
 * @package Weglot\Util
 */
class Url
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var null|string
     */
    protected $baseUrl = null;

    /**
     * @var
     */
    protected $default;

    /**
     * @var array
     */
    protected $languages = [];

    /**
     * @var string
     */
    protected $pathPrefix = '';

    /**
     * Url constructor.
     * @param string $url           Current visited url
     * @param string $default       Default language represented by ISO 639-1 code
     * @param array $languages      All available languages
     * @param string $pathPrefix    Prefix to access website root path (ie. : `/my/custom/path`, don't forget: starting `/` and no ending `/`)
     */
    public function __construct($url, $default, $languages = [], $pathPrefix = '')
    {
        $this
            ->setUrl($url)
            ->setDefault($default)
            ->setLanguages($languages)
            ->setPathPrefix($pathPrefix);
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
     * @param string $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param string $pathPrefix
     * @return $this
     */
    public function setPathPrefix($pathPrefix)
    {
        $this->pathPrefix = $pathPrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * @return null|string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Check current locale, based on URI segments from the given URL
     *
     * @return mixed
     */
    public function detectCurrentLanguage()
    {
        $uriPath = parse_url($this->getUrl(), PHP_URL_PATH);
        $uriSegments = explode('/', $uriPath);

        $hypothesis = $uriSegments[0];
        if (in_array($hypothesis, $this->languages)) {
            return $hypothesis;
        }
        return $this->default;
    }

    /**
     * Generate possible base URL then store it into $baseUrl
     *
     * @return string   Path prefix + base URL
     */
    public function detectBaseUrl()
    {
        $pathPrefixRegex = str_replace('/', '\/', $this->pathPrefix);
        $languages = implode('|', $this->languages);

        $baseUrl = preg_replace('#' .$pathPrefixRegex. '\/?(' .$languages. ')#i', '', $this->url);

        if ($baseUrl === $this->pathPrefix) {
            $baseUrl = '/';
        }

        $this->baseUrl = $baseUrl;
        return $this->pathPrefix . $this->baseUrl;
    }

    /**
     * Returns array with all possible URL for current Request
     *
     * @return array
     */
    public function currentRequestAllUrls()
    {
        if ($this->baseUrl === null) {
            $this->detectBaseUrl();
        }

        $urls = [];
        $urls[$this->default] = $this->baseUrl;
        foreach ($this->languages as $language) {
            $urls[$language] = $this->pathPrefix . '/' . $language . $this->baseUrl;
        }

        return $urls;
    }

    /**
     * Render hreflang links for SEO
     *
     * @return string
     */
    public function generateHrefLangsTags()
    {
        $render = '';
        $urls = $this->currentRequestAllUrls();

        foreach ($urls as $language => $url) {
            $render .= '<link rel="alternate" href="' .$url. '" hreflang="' .$language. '"/>'."\n";
        }

        return $render;
    }
}
