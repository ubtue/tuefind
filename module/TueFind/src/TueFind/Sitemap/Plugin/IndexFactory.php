<?php

namespace TueFind\Sitemap\Plugin;

use Psr\Container\ContainerInterface;

class IndexFactory extends \VuFind\Sitemap\Plugin\IndexFactory
{
    protected function getIdFetcher(
        ContainerInterface $container,
        $retrievalMode
    ): \VuFind\Sitemap\Plugin\Index\AbstractIdFetcher {
        $class = $retrievalMode === 'terms'
            ? \VuFind\Sitemap\Plugin\Index\TermsIdFetcher::class : \TueFind\Sitemap\Plugin\Index\CursorMarkIdFetcher::class;
        return new $class($container->get(\VuFindSearch\Service::class));
    }
}
