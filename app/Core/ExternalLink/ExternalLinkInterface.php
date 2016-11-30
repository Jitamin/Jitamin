<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hiject\Core\ExternalLink;

/**
 * External Link Interface.
 */
interface ExternalLinkInterface
{
    /**
     * Get link title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get link URL.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set link URL.
     *
     * @param string $url
     */
    public function setUrl($url);
}
