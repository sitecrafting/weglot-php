<?php

namespace Weglot\Parser;

use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;

/**
 * Interface ParserInterface.
 * @package Weglot\Parser
 */
interface ParserInterface
{
    /**
     * Constructor.
     * @param Client $client
     * @param ConfigProviderInterface $config
     * @param array $excludeBlocks
     * @param array $listeners
     */
    public function __construct(Client $client, ConfigProviderInterface $config, array $excludeBlocks = [], array $listeners = []);

    /**
     * @param string $source
     * @param string $languageFrom
     * @param string $languageTo
     * @return string
     */
    public function translate($source, $languageFrom, $languageTo);

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     *
     * @see \Symfony\Component\EventDispatcher\EventDispatcherInterface::addListener()
     */
    public function addListener($eventName, $listener, $priority = 0);
}
