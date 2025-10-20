<?php

$urls = [
    'https://ptah.ub.uni-tuebingen.de', // Landing page
    'https://ptah.ub.uni-tuebingen.de/Search/Results?lookfor=&type=AllFields&limit=500', // empty search
    'https://ptah.ub.uni-tuebingen.de/Record/1040597300', // full view (title)
    'https://ptah.ub.uni-tuebingen.de/AuthorityRecord/035286210', // full view (authority)
];


$skipUrls = [
    '/Content/',
];

foreach ($urls as $url) {
    print 'Checking URL: ' . $url . PHP_EOL;
    $dom = new DOMDocument();
    $dom->loadHTMLFile($url);
    $links = $dom->getElementsByTagName('a');
    foreach ($links as $link) {
        $rel = $link->getAttribute('rel');
        $href = $link->getAttribute('href');
        if ($rel == null || !preg_match('"nofollow"i', $rel)) {
            foreach ($skipUrls as $skipUrl) {
                if (strstr($href, $skipUrl) !== false) {
                    continue 2;
                }
            }

            $log = $dom->saveHTML($link);
            if ($href != null) {
                $log = $href;
            }
            print "\t" . 'Missing rel="nofollow": ' . $log . PHP_EOL;
        }
    }
}
