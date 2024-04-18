<?php

namespace TueFind\Sitemap\Plugin\Index;

use VuFindSearch\ParamBag;
use VuFindSearch\Query\Query;
use TueFindSearch\Command\GetSitemapFieldsCommand;

class CursorMarkIdFetcher extends \VuFind\Sitemap\Plugin\Index\CursorMarkIdFetcher
{
    /**
     * TueFind: Retrieve not only IDs, but also last modified dates
     */
    public function getIdsFromBackend(
        string $backend,
        string $cursorMark,
        int $countPerPage,
        array $filters
    ): array {
        // If the previous cursor mark matches the current one, we're finished!
        if ($cursorMark === $this->prevCursorMark) {
            return ['ids' => []];
        }
        $this->prevCursorMark = $cursorMark;

        $getKeyCommand = new \VuFindSearch\Command\GetUniqueKeyCommand($backend, []);
        $key = $this->searchService->invoke($getKeyCommand)->getResult();
        $params = new ParamBag(
            $this->defaultParams + [
                'rows' => $countPerPage,
                'sort' => $key . ' asc',
                'cursorMark' => $cursorMark,
            ]
        );
        // Apply filters:
        foreach ($filters as $filter) {
            $params->add('fq', $filter);
        }
        $command = new GetSitemapFieldsCommand(
            $backend,
            new Query('*:*'),
            0,
            $countPerPage,
            $params
        );

        $results = $this->searchService->invoke($command)->getResult();
        $ids = [];
        $lastmods = [];
        foreach ($results->getRecords() as $doc) {
            $ids[] = $doc->get($key);
            $lastmods[] = $doc->get('last_indexed');
        }
        $nextOffset = $results->getCursorMark();
        return compact('ids', 'nextOffset', 'lastmods');
    }
}
