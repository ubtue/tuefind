<?php

namespace TueFind\Sitemap;

class Generator extends \VuFind\Sitemap\Generator
{
    protected function getNewSitemap()
    {
        // Use custom Sitemap class from TueFind namespace
        return new Sitemap($this->frequency);
    }
}
