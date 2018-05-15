<?php

use Weglot\Util\Url;

class UrlTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Url
     */
    protected $url;

    protected $profile = [
        'url' => 'https://weglot.com/es/pricing',
        'default' => 'en',
        'languages' => ['fr', 'de', 'es'],
        'prefix' => '',
        'exclude' => [],
        'results' => [
            'getHost' => 'https://weglot.com',
            'getBaseUrl' => '/pricing',
            'isTranslable' => true,
            'detectCurrentLanguage' => 'es',
            'detectBaseUrl' => '/pricing',
            'currentRequestAllUrls' => [
                'https://weglot.com/pricing',
                'https://weglot.com/fr/pricing',
                'https://weglot.com/de/pricing',
                'https://weglot.com/es/pricing'
            ]
        ]
    ];

    protected $profiles = [
        [
            'url' => 'https://weglot.com/es/pricing',
            'default' => 'en',
            'languages' => ['fr', 'de', 'es'],
            'prefix' => '',
            'exclude' => [],
            'results' => [
                'getBaseUrl' => '/pricing',
                'isTranslable' => true,
                'detectCurrentLanguage' => 'es',
                'detectBaseUrl' => '/pricing',
                'currentRequestAllUrls' => [
                    'https://weglot.com/pricing',
                    'https://weglot.com/fr/pricing',
                    'https://weglot.com/de/pricing',
                    'https://weglot.com/es/pricing'
                ]
            ]
        ],
        [
            'url' => 'https://www.ratp.fr/de/horaires',
            'default' => 'fr',
            'languages' => ['en', 'de', 'es'],
            'prefix' => '',
            'exclude' => []
        ],
        [
            'url' => '',
            'default' => 'en',
            'languages' => ['fr', 'de', 'es'],
            'prefix' => '/web',
            'exclude' => []
        ],
        [
            'url' => '',
            'default' => 'en',
            'languages' => ['fr', 'de', 'es'],
            'prefix' => '/web',
            'exclude' => [
                ''
            ],
            'results' => [
                'baseUrl' => ''
            ]
        ],
        [
            'url' => '',
            'default' => 'en',
            'languages' => ['fr', 'en', 'es'],
            'prefix' => '',
            'exclude' => [
                ''
            ]
        ]
    ];

    public function testSimpleUrlDefaultEn()
    {
        $profile = [
            'url' => 'https://weglot.com/es/pricing',
            'default' => 'en',
            'languages' => ['fr', 'de', 'es'],
            'prefix' => '',
            'exclude' => [],
            'results' => [
                'getHost' => 'https://weglot.com',
                'getBaseUrl' => '/pricing',
                'isTranslable' => true,
                'detectCurrentLanguage' => 'es',
                'detectBaseUrl' => 'https://weglot.com/pricing',
                'currentRequestAllUrls' => [
                    'en' => 'https://weglot.com/pricing',
                    'fr' => 'https://weglot.com/fr/pricing',
                    'de' => 'https://weglot.com/de/pricing',
                    'es' => 'https://weglot.com/es/pricing'
                ]
            ]
        ];

        $url = $this->_urlInstance($profile);
        $this->_checkResults($url, $profile);
    }

    public function testSimpleUrlDefaultFr()
    {
        $profile = [
            'url' => 'https://www.ratp.fr/en/horaires',
            'default' => 'fr',
            'languages' => ['en'],
            'prefix' => '',
            'exclude' => [],
            'results' => [
                'getHost' => 'https://www.ratp.fr',
                'detectBaseUrl' => 'https://www.ratp.fr/horaires',
                'getBaseUrl' => '/horaires',
                'isTranslable' => true,
                'detectCurrentLanguage' => 'en',
                'currentRequestAllUrls' => [
                    'fr' => 'https://www.ratp.fr/horaires',
                    'en' => 'https://www.ratp.fr/en/horaires',
                ]
            ]
        ];

        $url = $this->_urlInstance($profile);
        $this->_checkResults($url, $profile);
    }

    /**
     * @param array $profile
     * @return Url
     */
    protected function _urlInstance(array $profile)
    {
        return (new Url(
            $profile['url'],
            $profile['default'],
            $profile['languages'],
            $profile['prefix']
        ))
            ->setExcludedUrls($profile['exclude']);
    }

    /**
     * @param Url $url
     * @param array $profile
     * @return void
     */
    protected function _checkResults(Url $url, array $profile)
    {
        $cloned = clone $url;
        $this->assertEquals($profile['results']['currentRequestAllUrls'], $cloned->currentRequestAllUrls());

        $this->assertNull($url->getHost());
        $this->assertNull($url->getBaseUrl());

        $this->assertEquals($profile['results']['detectBaseUrl'], $url->detectUrlDetails());

        $this->assertEquals($profile['results']['getHost'], $url->getHost());
        $this->assertEquals($profile['results']['getBaseUrl'], $url->getBaseUrl());

        $this->assertEquals($profile['results']['isTranslable'], $url->isTranslable());

        $this->assertEquals($profile['results']['detectCurrentLanguage'], $url->detectCurrentLanguage());

        $this->assertEquals($profile['results']['currentRequestAllUrls'], $url->currentRequestAllUrls());
    }
}
