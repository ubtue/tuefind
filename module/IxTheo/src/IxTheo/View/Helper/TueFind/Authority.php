<?php

namespace IxTheo\View\Helper\TueFind;

class Authority extends \TueFind\View\Helper\TueFind\Authority {

    protected function getTopicsCloudFieldname($translatorLocale=null): string
    {
        if ($translatorLocale == null)
            return 'topic_cloud';
        else
            return 'topic_cloud_' . $translatorLocale;
    }
}
