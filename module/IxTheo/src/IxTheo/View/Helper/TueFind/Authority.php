<?php

namespace IxTheo\View\Helper\TueFind;

class Authority extends \TueFind\View\Helper\TueFind\Authority {

    public function getTopicsCloudFieldname($translatorLocale=null): string
    {
        return 'topic_cloud_' . $translatorLocale;
    }

    private function getFieldTopicCloud($row, $language=null): array {
        $key = 'topic_cloud';
        if(isset($row) && !empty($row)) {
            if($language !== null) {
                $key = 'topic_cloud_'.$language;
            }
        }
        return array_unique($row[$key] ?? []);
    }
}
