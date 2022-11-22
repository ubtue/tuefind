<?php

namespace TueFind\MetadataVocabulary;

class DSpace6 extends \VuFind\MetadataVocabulary\AbstractBase
{
    protected $tuefind;

    protected $dspaceMap = [
        [
            'key' => 'dc.contributor.author',
            'source' => 'author',
        ],
        [
            'key' => 'dc.date.issued',
            'source' => 'date',
        ],
        [
            'key' => 'dc.language.iso',
            'source' => 'language',
        ],
        [
            'key' => 'dc.publisher',
            'source' => 'publisher',
        ],
        [
            'key' => 'dc.title',
            'source' => 'title',
        ],
    ];

    // Examples, see:
    // - https://wiki.lyrasis.org/display/DSDOC6x/REST+API#RESTAPI-ItemObject
    // - https://wiki.lyrasis.org/display/DSDOC6x/REST+API#RESTAPI-MetadataEntryObject
    public function getMappedData(\VuFind\RecordDriver\AbstractBase $driver)
    {
        $rawData = parent::getGenericData($driver);
        $rawData['language'] = $driver->getDefaultSolrLanguages();

        $dspaceItem = ['name' => $rawData['title'], 'type' => 'item', 'metadata' => []];

        foreach ($this->dspaceMap as $mapEntry) {
            $rawDataKey = $mapEntry['source'];
            if (!isset($rawData[$rawDataKey])) {
                continue;
            }

            $values = $rawData[$rawDataKey];
            if (!is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                $dspaceMetadata = ['key' => $mapEntry['key']];

                if ($mapEntry['source'] == 'language') {
                    $value = $this->getFormatedLanguages($value);
                }

                $dspaceMetadata['value'] = $value;
                $dspaceItem['metadata'][] = $dspaceMetadata;
            }
        }

        return $dspaceItem;
    }

    public function getFormatedLanguages($lang): string
    {
        $langFile =  $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/other/language.txt';
        $langs = file($langFile);
        foreach($langs as $langLine) {
            $explang = explode('|',$langLine);
            if(isset($explang[0]) && $explang[0] == $lang) {
                if(isset($explang[2]) && strlen($explang[2]) == 2) {
                    return $explang[2];
                }
            }
        }
        return '';
    }
}
