<?php

namespace TueFind\Sitemap\Plugin;

class Index extends \VuFind\Sitemap\Plugin\Index
{
    /**
     * Generate urls for the sitemap.
     *
     * @return \Generator
     */
    public function getUrls(): \Generator
    {
        // Initialize variables for message displays within the loop below:
        $currentPage = $recordCount = 0;

        // Loop through all backends
        foreach ($this->backendSettings as $current) {
            $recordUrl = $this->baseUrl . $current['url'];
            $this->verboseMsg(
                'Adding records from ' . $current['id']
                . " with record base url $recordUrl"
            );
            $offset = $this->idFetcher->getInitialOffset();
            $this->idFetcher->setupBackend($current['id']);
            while (true) {
                $result = $this->idFetcher->getIdsFromBackend(
                    $current['id'],
                    $offset,
                    $this->countPerPage,
                    $this->filters
                );

                // TueFind: Also yield additional fields
                foreach ($result['ids'] as $index => $item) {
                    $loc = htmlspecialchars($recordUrl . urlencode($item));
                    if (strpos($loc, 'http') === false) {
                        $loc = 'http://' . $loc;
                    }
                    $recordCount++;

                    if (isset($result['lastmods'][$index])) {
                        yield ['url' => $loc, 'lastmod' => $result['lastmods'][$index]];
                    } else {
                        yield $loc;
                    }
                }
                $currentPage++;
                $this->verboseMsg("Page $currentPage, $recordCount processed");
                if (!isset($result['nextOffset'])) {
                    break;
                }
                $offset = $result['nextOffset'];
            }
        }
    }
}
