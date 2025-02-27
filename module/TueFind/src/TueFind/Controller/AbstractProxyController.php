<?php

namespace TueFind\Controller;


/**
 * Abstract proxy controller with functions that allow using a cache
 * and sending additional HTTP headers when resolving URLs.
 */
class AbstractProxyController extends \VuFind\Controller\AbstractBase implements \VuFind\Http\CachingDownloaderAwareInterface
{
    use \VuFind\Http\CachingDownloaderAwareTrait;
}
