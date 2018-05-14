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
     * @var array
     */
    protected $excludedUrls = [];

    /**
     * Url constructor.
     * @param string $url           Current visited url
     * @param string $default       Default language represented by ISO 639-1 code
     * @param array $languages      All available languages
     * @param string $pathPrefix    Prefix to access website root path (ie. : `/my/custom/path`, don't forget: starting `/` and no ending `/`)
     * @param array $excludedUrls   An array of urls that should not be translated
     */
    public function __construct($url, $default, $languages = [], $pathPrefix = '', $excludedUrls = [])
    {
        $this
            ->setUrl(urldecode($url))
            ->setDefault($default)
            ->setLanguages($languages)
            ->setPathPrefix($pathPrefix)
            ->setExcludedUrls($excludedUrls);
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
     * @param array $excludedUrls
     * @return $this
     */
    public function setExcludedUrls($excludedUrls)
    {
        $this->excludedUrls = $excludedUrls;
        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedUrls()
    {
        return $this->excludedUrls;
    }

    /**
     * @return null|string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Check if we need to translate given URL
     *
     * @return bool
     */
    public function isTranslable()
    {
        foreach ($this->getExcludedUrls() as $regex) {
            $escapedRegex = str_replace('/', '\/', $regex);
            $fullRegex = sprintf('/%s/', $escapedRegex);
            if (preg_match($fullRegex, $this->getUrl()) === 1) {
                return false;
            }
        }
        return true;
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
        if (in_array($hypothesis, $this->getLanguages())) {
            return $hypothesis;
        }
        return $this->getDefault();
    }

    /**
     * Generate possible base URL then store it into $baseUrl
     *
     * @return string   Path prefix + base URL
     */
    public function detectBaseUrl()
    {
        $pathPrefixRegex = str_replace('/', '\/', $this->getPathPrefix());
        $languages = implode('|', $this->getLanguages());

        $baseUrl = preg_replace('#' .$pathPrefixRegex. '\/?(' .$languages. ')#i', '', $this->getUrl());

        if ($baseUrl === $this->getPathPrefix()) {
            $baseUrl = '/';
        }

        $this->baseUrl = $baseUrl;
        return $this->getPathPrefix() . $this->getBaseUrl();
    }

    /**
     * Returns array with all possible URL for current Request
     *
     * @return array
     */
    public function currentRequestAllUrls()
    {
        if ($this->getBaseUrl() === null) {
            $this->detectBaseUrl();
        }

        $urls = [];
        $urls[$this->getDefault()] = $this->getBaseUrl();
        foreach ($this->getLanguages() as $language) {
            $urls[$language] = $this->getPathPrefix() . '/' . $language . $this->getBaseUrl();
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
