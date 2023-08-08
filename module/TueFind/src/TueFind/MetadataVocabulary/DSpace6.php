<?php

namespace TueFind\MetadataVocabulary;

class DSpace6 extends \VuFind\MetadataVocabulary\AbstractBase
{
    protected $dspaceMap = [
        [
            'key' => 'utue.artikel.ppn',
            'source' => 'ppn',
        ],
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
        $rawData['language'] = $driver->getLanguagesIso639_2();
        $rawData['ppn'] = $driver->getUniqueID();

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
                    $value = $this->getMappedLanguages($value);
                }

                $dspaceMetadata['value'] = $value;
                $dspaceItem['metadata'][] = $dspaceMetadata;
            }
        }

        return $dspaceItem;
    }

    public function getMappedLanguages($lang): string
    {
        $langFile = getenv('VUFIND_HOME') . '/local/tuefind/languages/ISO639/ISO-639-2_utf-8.txt';
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
