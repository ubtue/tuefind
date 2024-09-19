<?php

namespace TueFind\Record;

use VuFind\Exception\RecordMissing as RecordMissingException;
use VuFindSearch\ParamBag;
use VuFindSearch\Command\RetrieveCommand;
use VuFindSearch\Command\SearchCommand;
use VuFindSearch\Query\Query;

class Loader extends \VuFind\Record\Loader {
    public function load($id, $source = DEFAULT_SEARCH_BACKEND,
        $tolerateMissing = false, ParamBag $params = null
    ) {
        if (null !== $id && '' !== $id) {
            $results = [];
            if (null !== $this->recordCache
            && $this->recordCache->isPrimary($source)
            ) {
                $results = $this->recordCache->lookup($id, $source);
            }
            if (empty($results)) {
                try {
                    $command = new RetrieveCommand($source, $id, $params);
                    $results = $this->searchService->invoke($command)->getResult()->getRecords();
                } catch (BackendException $e){
                    if(!$tolerateMissing){
                        throw $e;
                    }
                }
            }
            // fallback: search for record by ID with ISIL prefix, e.g. "(DE-599)ZDB2985306-0"
            // Note: The strpos call in the following line is just for performance reasons
            //       to avoid a Solr query in case the ID does not fit the case
            if (empty($results) && strpos($id, '(') === 0) {
                $query = new Query('ctrlnum:"' . $id . '"', null, 'Allfields');
                $command = new SearchCommand($source, $query);
                $results = $this->searchService->invoke($command)->getResult()->getRecords();
            }
            if (empty($results) && null !== $this->recordCache
            && $this->recordCache->isFallback($source)
            ) {
                $results = $this->recordCache->lookup($id, $source);
            }

            if (count($results) == 1) {
                return $results[0];
            }

            // TueFind: use fallback like in parent's "loadBatchForSource" function
            // (this change might also be sent to vufind.org for future versions)
            if ($this->fallbackLoader
                && $this->fallbackLoader->has($source)
            ) {
                $fallbackRecords = $this->fallbackLoader->get($source)
                    ->load([$id]);

                if (count($fallbackRecords) == 1) {
                    return $fallbackRecords[0];
                }
            }
        }
        if ($tolerateMissing) {
            $record = $this->recordFactory->get('Missing');
            $record->setRawData(['id' => $id]);
            $record->setSourceIdentifier($source);
            return $record;
        }
        throw new RecordMissingException(
            'Record ' . $source . ':' . $id . ' does not exist.'
        );
    }

    public function loadAuthorityRecordByGNDNumber($gndNumber) {
        $source = 'SolrAuth';

        if (null !== $gndNumber && '' !== $gndNumber) {
            $results = [];

            // no primary cache

            // use search instead of lookup logic
            if (empty($results)) {

                try {
                    $query = new Query('gnd:' . $gndNumber);
                    $command = new SearchCommand($source, $query);
                    $results = $this->searchService->invoke($command)->getResult()->getRecords();
                    if ($results->first() !== null)
                        return $results->first();
                    $results = [];
                } catch (BackendException $e){
                    if(!$tolerateMissing){
                        throw $e;
                    }
                }

            }

            // no fallback cache

            if (!empty($results)) {
                return $results[0];
            }

            // no fallback loader
        }
        // no "tolerate missing" logic

        throw new RecordMissingException(
            'Record ' . $source . ':' . $gndNumber . ' does not exist.'
        );
    }
}
