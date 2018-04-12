<?php

namespace Weglot\Parser\Check;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Parser\Util\Text as TextUtil;

/**
 * Class Meta_desc
 * @package Weglot\Parser\Check
 */
class MetaContent extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const DOM = 'meta[name="description"],meta[property="og:title"],meta[property="og:description"],meta[property="og:site_name"],meta[name="twitter:title"],meta[name="twitter:description"]';

    /**
     * {@inheritdoc}
     */
    const PROPERTY = 'content';

    /**
     * {@inheritdoc}
     */
    const WORD_TYPE = WordType::META_CONTENT;

    /**
     * {@inheritdoc}
     */
    protected function check()
    {
        return (!is_numeric(TextUtil::fullTrim($this->node->placeholder))
            && !preg_match('/^\d+%$/', TextUtil::fullTrim($this->node->placeholder)));
    }
}
