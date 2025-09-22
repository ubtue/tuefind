<?php

namespace TueFind\View\Helper\TueFind;

use \TueFind\RecordDriver\SolrAuthMarc as AuthorityRecordDriver;
use \TueFind\RecordDriver\SolrMarc as TitleRecordDriver;
use VuFindSearch\Query\Query;
use VuFindSearch\Command\SearchCommand;
use \VuFindSearch\ParamBag;

/**
 * View Helper for TueFind, containing functions related to authority data + schema.org
 */
class Authority extends \Laminas\View\Helper\AbstractHelper
                implements \VuFind\I18n\Translator\TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $dbTableManager;

    protected $recordLoader;

    protected $searchService;

    protected $viewHelperManager;

    protected $normdataTranslationCache = [];

    /**
     * This list is used to register authors
     * who explicitly requested to hide certain titles
     * from their authority page.
     * These titles will only be hidden from the authority page,
     * but they will still be searchable as a title,
     * which is the reqested behaviour.
     */
    protected $authorTitlesBlacklist = [
        '815326920' => [
            '1666824623',
            '1663081204',
        ],
    ];

    public function __construct(\VuFindSearch\Service $searchService,
                                \Laminas\View\HelperPluginManager $viewHelperManager,
                                \VuFind\Record\Loader $recordLoader,
                                \VuFind\Db\Table\PluginManager $dbTableManager)
    {
        $this->dbTableManager = $dbTableManager;
        $this->recordLoader = $recordLoader;
        $this->searchService = $searchService;
        $this->viewHelperManager = $viewHelperManager;
    }

    protected function formatPlace(array $place): string
    {
        // prepare / override given information
        $name = $place['name'];
        $district = $place['district'] ?? '';
        $type = $place['type'] ?? '';
        if ($type == 'DIN-ISO-3166') {
            $type = 'Country';
            $name = \Locale::getDisplayRegion($place['name'], $this->getTranslatorLocale());
            if (empty($district))
                $district = $place['name'];
        } else {
            $type = $this->translateNormdata($type);
        }

        // build label
        $label = '';
        if (!empty($type))
            $label .= $this->translate($type) . ': ';
        $label .= $name;
        if (!empty($district))
            $label .= ' (' . $district . ')';
        return $label;
    }

    /**
     * Get authority birth information for display
     *
     * @return string
     */
    public function getBirth(AuthorityRecordDriver &$driver): string
    {
        $display = '';

        $birthDate = $driver->getBirthDateOrYear();
        if ($birthDate != '') {
            $display .= $this->getDateTimeProperty($birthDate, 'birthDate');
            $birthPlace = $driver->getBirthPlace();
            if ($birthPlace != null)
                $display .= ', <span property="birthPlace">' . htmlspecialchars($this->formatPlace($birthPlace)) . '</span>';
        }

        return $display;
    }

    /**
     * Get rendered html for datetime property (for view + schema.org)
     *
     * schema.org timestamps must be provided as a ISO8601 timestamp,
     * so if the timestamp differs, we create an additional element
     * which is hidden + marked-up for schema.org.
     *
     * @param string $datetimeCandidate
     * @param string $propertyId
     * @return string
     */
    private function getDateTimeProperty($datetimeCandidate, $propertyId): string
    {
        $iso8601DateTime = $this->getView()->tuefind()->convertDateTimeToIso8601($datetimeCandidate);
        if ($iso8601DateTime == $datetimeCandidate)
            return '<span property="' . htmlspecialchars($propertyId) . '">' . $datetimeCandidate . '</span>';

        $html = '<span>' . htmlspecialchars($datetimeCandidate) . '</span>';
        $html .= '<span class="tf-schema-org-only" property="' . htmlspecialchars($propertyId) . '">' . htmlspecialchars($iso8601DateTime) . '</span>';
        return $html;
    }

    /**
     * Get authority death information for display
     *
     * @return string
     */
    public function getDeath(AuthorityRecordDriver &$driver): string
    {
        $display = '';
        $deathDate = $driver->getDeathDateOrYear();
        if ($deathDate != '') {
            $display .= $this->getDateTimeProperty($deathDate, 'deathDate');
            $deathPlace = $driver->getDeathPlace();
            if ($deathPlace != null)
                $display .= ', <span property="deathPlace">' . htmlspecialchars($this->formatPlace($deathPlace)) . '</span>';
        }
        return $display;
    }

    public function getOtherNames(AuthorityRecordDriver &$driver): string
    {
        $otherNames = $driver->getUseFor();
        $headingTimespan = $driver->getHeadingTimespan();
        $limit = 5;
        $i = 0;
        $display = '';
        $clearName = '';
        if(!empty($otherNames)) {
            $display .= '<ul class="tf-other-names-list">';
            foreach ($otherNames as $name) {
                if($i < $limit) {
                    if(!empty($headingTimespan)) {
                        $clearNameArray = explode($headingTimespan, $name);
                        if(isset($clearNameArray[0])) {
                            $clearName = $clearNameArray[0];
                        }
                    }else{
                        $clearName = $name;
                    }
                    $display .= '<li>'.trim($clearName).'</li>';
                }
                $i++;
            }
            if($i > $limit) {
                $display .= '<li><a href="#other-names">'.$this->translate('more').'</a></li>';
            }
            $display .= '</ul>';
        }

        return $display;
    }

    public function getBiographicalReferences(AuthorityRecordDriver &$driver): string
    {
        $tuefindHelper = $this->viewHelperManager->get('tuefind');

        $references = $driver->getBiographicalReferences();
        if (count($references) == 0)
            return '';

        usort($references, function($a, $b) { return strcmp($a['title'], $b['title']); });

        $display = '';
        foreach ($references as $reference) {
            $image = $tuefindHelper->getDetailsIcon($reference['title']);
            if(!empty($reference['url'])) {
                if ($image == null) {
                    $display .= '<a href="' . $reference['url'] . '" target="_blank" property="sameAs"><i class="fa fa-external-link"></i> ' . htmlspecialchars($reference['title']) . '</a><br>';
                }
                else {
                    $display .= '<a href="' . $reference['url'] . '" target="_blank" property="sameAs"> <img class="detailsIcon" src="'.$image.'"/>' . htmlspecialchars($reference['title']) . '</a><br>';
                }
            }else{
                $display .= htmlspecialchars($reference['title']) . '<br>';
            }

        }

        return $display;
    }

    public function getArchivedMaterial(AuthorityRecordDriver &$driver): string
    {
        $references = $driver->getArchivedMaterial();
        if (count($references) == 0)
            return '';

        usort($references, function($a, $b) { return strcmp($a['title'], $b['title']); });

        $display = '';
        foreach ($references as $reference) {
            $title = $reference['title'];
            if (preg_match('"Kalliope"', $title))
                $title = 'Kalliope';
            elseif (preg_match('"Archivportal-D"', $title))
                $title = 'Archivportal-D';

            $display .= '<a href="' . $reference['url'] . '" target="_blank" property="sameAs"><i class="fa fa-external-link"></i> ' . htmlspecialchars($title) . '</a><br>';
        }

        return $display;
    }

    public function getExternalSubsystems(AuthorityRecordDriver &$driver, $currentSubsystem): string
    {
        $externalSubsystems = $driver->getExternalSubsystems();
        usort($externalSubsystems, function($a, $b) { return strcmp($a['title'], $b['title']); });

        $subSystemHTML = '';
        if(!empty($externalSubsystems) && !empty($currentSubsystem)) {
            foreach ($externalSubsystems as $system) {
                if ($system['label'] != $currentSubsystem) {
                    $subSystemHTML .= '<a href="'.$system['url'].'" target="_blank" property="sameAs"><i class="fa fa-external-link"></i> '.htmlspecialchars($system['title']).'</a><br />';
                }
            }
        }

        return $subSystemHTML;
    }

    public function getName(AuthorityRecordDriver &$driver): string
    {
        if ($driver->isMeeting()) {
            return '<span property="name">' . htmlspecialchars($driver->getMeetingName()) . '</span>';
        } else {
            $name = $driver->getHeadingShort();
            $timespan = $driver->getHeadingTimespan();

            $heading = '<span property="name">' . htmlspecialchars($name) . '</span>';
            if ($timespan != null)
                $heading .= ' ' . htmlspecialchars($timespan);
            return $heading;
        }
    }

    public function translateNormdata($normdataString): string
    {
        $language = $this->getTranslatorLocale();

        if ($language == 'de')
            return $normdataString;

        $dir = '/usr/local/ub_tools/bsz_daten/';
        $path = $dir . 'normdata_translations_' . $language . '.txt';
        $fallbackPath = $dir . 'normdata_translations_en.txt';
        if (!is_file($path) && is_file($fallbackPath))
            $path = $fallbackPath;

        if (!isset($this->normdataTranslationCache[$language]) && is_file($path)) {
            $languageCache = [];
            $rawTranslations = file($path);
            foreach ($rawTranslations as $rawTranslation) {
                $parts = explode('|', $rawTranslation);
                $languageCache[$parts[0]] = trim($parts[1]);
            }
            $this->normdataTranslationCache[$language] = $languageCache;
        }

        return $this->normdataTranslationCache[$language][$normdataString] ?? $normdataString;
    }

    public function getOccupations(AuthorityRecordDriver &$driver): string
    {
        $occupations = $driver->getOccupationsAndTimespans();
        $occupationsDisplay = '';
        foreach ($occupations as $occupation) {
            if ($occupationsDisplay != '')
                $occupationsDisplay .= ' / ';

            $value = $this->translateNormdata($occupation['name']);
            if (!empty($occupation['timespan']))
                $value .= ' (' . $occupation['timespan'] . ')';
            $occupationsDisplay .= '<span property="hasOccupation">' . htmlspecialchars($value) . '</span>';
        }
        return $occupationsDisplay;
    }

    public function getCorporateRelations(AuthorityRecordDriver &$driver): string
    {
        $relations = $driver->getCorporateRelations();
        $relationsDisplay = '';

        $urlHelper = $this->viewHelperManager->get('url');
        foreach ($relations as $relation) {
            if ($relationsDisplay != '')
                $relationsDisplay .= '<br>';

            $relationsDisplay .= '<span property="affiliation" typeof="Organization">';

            $recordExists = isset($relation['id']) && $this->recordExists($relation['id']);
            if ($recordExists) {
                $url = $urlHelper('solrauthrecord', ['id' => $relation['id']]);
                $relationsDisplay .= '<a property="sameAs" href="' . $url . '">';
            }

            $name = $this->translateNormdata($relation['name']);
            foreach ($relation['adds'] as $add) {
                $name .= '. ' . $this->translateNormdata($add);
            }

            $relationsDisplay .= '<span property="name">' . htmlspecialchars($name) . '</span>';
            if (!empty($relation['location'])) {
                $relationsDisplay .= ' (<span property="location">' . htmlspecialchars($relation['location']) . '</span>)';
            } else if (!empty($relation['institution'])) {
                $relationsDisplay .= '. <span property="department">' . htmlspecialchars($relation['institution']) . '</span>';
            }

            if (!empty($relation['type']) || !empty($relation['timespan'])) {
                $relationsDisplay .= ':';
                if (!empty($relation['type']))
                    $relationsDisplay .= ' ' . $relation['type'];
                if (!empty($relation['timespan']))
                    $relationsDisplay .= ' ' . $relation['timespan'];
            }

            if ($recordExists)
                $relationsDisplay .= '</a>';

            $relationsDisplay .= '</span>';
        }
        return $relationsDisplay;
    }

    public function getPersonalRelations(AuthorityRecordDriver &$driver): string
    {
        $relations = $driver->getPersonalRelations();
        $relationsDisplay = '';

        $urlHelper = $this->viewHelperManager->get('url');
        foreach ($relations as $relation) {
            if ($relationsDisplay != '')
                $relationsDisplay .= '<br>';

            $relationsDisplay .= '<span property="relatedTo" typeof="Person">';

            $recordExists = isset($relation['id']) && $this->recordExists($relation['id']);
            if ($recordExists) {
                $url = $urlHelper('solrauthrecord', ['id' => $relation['id']]);
                $relationsDisplay .= '<a property="sameAs" href="' . $url . '">';
            }

            $relationsDisplay .= '<span property="name">' . $relation['name'] . '</span>';

            if (isset($relation['type']))
                $relationsDisplay .= ' (' . htmlspecialchars($this->translateNormdata($relation['type'])) . ')';

            if ($recordExists)
                $relationsDisplay .= '</a>';

            $relationsDisplay .= '</span>';
        }
        return $relationsDisplay;
    }

    public function getGeographicalRelations(AuthorityRecordDriver &$driver): string
    {
        $placesString = '';

        $places = $driver->getGeographicalRelations();
        foreach ($places as $place) {
            $placesString .= htmlentities($this->formatPlace($place)) . '<br>';
        }

        return $placesString;
    }

    public function getSchemaOrgType(AuthorityRecordDriver &$driver): string
    {
        switch ($driver->getType()) {
        case 'person':
            return 'Person';
        case 'corporate':
            return 'Organization';
        case 'meeting':
            return 'Event';
        default:
            return 'Thing';
        }
    }

    public function getNewestTitlesAbout(AuthorityRecordDriver &$driver, $offset=0, $limit=10)
    {
        // We use 'Solr' as identifier here, because the RecordDriver's identifier would be "SolrAuth"
        $identifier = 'Solr';
        $query = new Query($this->getTitlesAboutQueryParams($driver), null, 'AllFields');
        $searchCommand = new SearchCommand($identifier, $query,
            $offset, $limit, new ParamBag(['sort' => 'publishDate DESC']));
        return $this->searchService->invoke($searchCommand)->getResult();
    }

    public function getNewestTitlesBy(AuthorityRecordDriver &$driver, $offset=0, $limit=10)
    {
        // We use 'Solr' as identifier here, because the RecordDriver's identifier would be "SolrAuth"
        $identifier = 'Solr';
        $query = new Query($this->getTitlesByQueryParams($driver), null, 'AllFields');
        $searchCommand = new SearchCommand($identifier, $query,
            $offset, $limit, new ParamBag(['sort' => 'publishDate DESC']));
        return $this->searchService->invoke($searchCommand)->getResult();
    }

    public function getRelatedAuthors(AuthorityRecordDriver &$driver, $limit)
    {
        $params = new \VuFindSearch\ParamBag();
        $params->set('fl', 'id,author_and_id_facet');
        $params->set('sort', 'score desc,publishDateSort desc');
        $params->set('facet', 'true');
        $params->set('facet.field', 'author_and_id_facet');
        $params->set('facet.limit', 9999);
        $params->set('hl', "true");
        $params->set('facet.sort', 'count');
        $params->set('spellcheck', 'false');
        $params->set('facet.mincount', 1);

        // Make sure we set offset+limit to 0, because we only want the facet counts
        // and not the rows itself for performance reasons.
        // (This could get very slow, e.g. Martin Luther where we have thousands of related datasets.)

        $identifier = 'Solr';
        $query = new Query($this->getTitlesByQueryParams($driver), null, 'AllFields');
        $searchCommand = new SearchCommand($identifier, $query,
            0, 0, $params);
        $titleRecords = $this->searchService->invoke($searchCommand)->getResult();


        $relatedAuthors = $titleRecords->getFacets()['author_and_id_facet'];
        
        $referenceAuthorKey = $driver->getUniqueID() . ':' . $driver->getTitle();

        $referenceAuthorID = $driver->getUniqueID();

        // This is not an array but an ArrayObject, so unset() will cause an error
        // if the index does not exist => we need to check it with isset first.
        if (isset($relatedAuthors[$referenceAuthorKey])) {
            unset($relatedAuthors[$referenceAuthorKey]);
        }

        // custom sort, since solr can only sort by count but not alphabetically,
        // since the value starts with an id instead of a name.
        $finalAuthorArray = [];
        $fixedAuthorCount = 0;
        foreach($relatedAuthors as $oneRelatedAuthor=>$counts) {
            $explodeData = explode(':', $oneRelatedAuthor);
            $relatedAuthor['relatedAuthorTitle'] = '';
            $relatedAuthor['relatedAuthorID'] = '';
            if(isset($explodeData[1]) && $explodeData[1] != $driver->getUniqueID()) {
                $relatedAuthor['relatedAuthorTitle'] = $explodeData[0];
                $relatedAuthor['relatedAuthorID'] = $explodeData[1];
                $fixedAuthorCount++;
            }
            if(count($finalAuthorArray) < $limit && !empty($relatedAuthor['relatedAuthorTitle'])) {
                $finalAuthorArray[] = $relatedAuthor;
            }
        }

        return array($finalAuthorArray,$fixedAuthorCount);
    }

    /**
     * Call this number with a variable number of arguments,
     * each containing either an author name/heading or an authority record driver.
     * ("..." == PHP splat operator)
     */
    public function getRelatedJointQueryParams(...$authors): string
    {
        $parts = [];
        foreach ($authors as $author) {
            $parts[] = '(' . $this->getTitlesByQueryParams($author) . ')';
        }
        return implode(' AND ', $parts);
    }

    public function getTimespans(AuthorityRecordDriver &$driver): string
    {
        return implode('<br>', $driver->getTimespans());
    }

    protected function getTitlesAboutQueryParams(&$author, $fuzzy=false): string
    {
        if ($author instanceof AuthorityRecordDriver) {
            $queryString = '(';
            $queryString .= 'topic_id:"' . $author->getUniqueId() . '"';
            if ($fuzzy) {
                $queryString .= 'OR topic_all:"' . $author->getTitle() . '"';
            }
            $queryString .= ')';
            if (isset($this->authorTitlesBlacklist[$author->getUniqueId()])) {
                foreach ($this->authorTitlesBlacklist[$author->getUniqueId()] as $title) {
                    $queryString .= ' AND -id:' . $title;
                }
            }
        } else {
            $queryString = 'topic_all:"' . $author . '"';
        }
        return $queryString;
    }


    protected function getTitlesAboutQueryParamsChartDate(&$author): string
    {
        if ($author instanceof AuthorityRecordDriver) {
            $queryString = 'topic_id:"' . $author->getUniqueId() . '"';
        } else {
            $queryString = 'topic_all:"' . $author . '"';
        }
        return $queryString;
    }

    protected function getTitlesByQueryParams(&$author, $fuzzy=false): string
    {
        if ($author instanceof AuthorityRecordDriver) {
            $queryString = '(';
            $queryString .= 'author_id:"' . $author->getUniqueId() . '"';
            $queryString .= ' OR author2_id:"' . $author->getUniqueId() . '"';
            $queryString .= ' OR author_corporate_id:"' . $author->getUniqueId() . '"';
            $queryString .= ' OR author3_id:"' . $author->getUniqueId() . '"';
            if ($fuzzy) {
                $queryString .= ' OR author:"' . $author->getTitle() . '"';
                $queryString .= ' OR author2:"' . $author->getTitle() . '"';
                $queryString .= ' OR author_corporate:"' . $author->getTitle() . '"';
                $queryString .= ' OR author3:"' . $author->getTitle() . '"';
            }
            $queryString .= ')';

            if (isset($this->authorTitlesBlacklist[$author->getUniqueId()])) {
                foreach ($this->authorTitlesBlacklist[$author->getUniqueId()] as $title) {
                    $queryString .= ' AND -id:' . $title;
                }
            }
        } else {
            $queryString = 'author:"' . $author . '"';
            $queryString .= ' OR author2:"' . $author . '"';
            $queryString .= ' OR author_corporate:"' . $author . '"';
            $queryString .= ' OR author3:"' . $author . '"';
        }

        return $queryString;
    }

    public function getTitlesAboutUrl(AuthorityRecordDriver &$driver): string
    {
        $urlHelper = $this->viewHelperManager->get('url');
        return $urlHelper('search-results', [], ['query' => ['lookfor' => $this->getTitlesAboutQueryParams($driver)]]);
    }

    /**
     * Get URL to search result with all titles for this authority record.
     * Moved here because it needs to be the same in several locations, e.g.:
     * - authority page
     * - biblio result-list
     * - biblio core (data-authors)
     */
    public function getTitlesByUrl(AuthorityRecordDriver &$driver): string
    {
        $urlHelper = $this->viewHelperManager->get('url');
        return $urlHelper('search-results', [], ['query' => ['lookfor' => $this->getTitlesByQueryParams($driver)]]);
    }

    public function getTitlesByUrlNameOrID($authorName, $authorId = null): string
    {
        $urlHelper = $this->viewHelperManager->get('url');
        return $urlHelper('search-results', [], ['query' => ['lookfor' => $this->getTitlesByQueryParamsNameOrID($authorName, $authorId)]]);
    }

    protected function getTitlesByQueryParamsNameOrID($authorName, $authorId = null): string
    {
        if ($authorId != null) {
            $queryString = 'author_id:"' . $authorId . '"';
            $queryString .= ' OR author2_id:"' . $authorId . '"';
            $queryString .= ' OR author_corporate_id:"' . $authorId . '"';
            $queryString .= ' OR author:"' . $authorName . '"';
            $queryString .= ' OR author2:"' . $authorName . '"';
            $queryString .= ' OR author_corporate:"' . $authorName . '"';
        } else {
            $queryString = 'author:"' . $authorName . '"';
            $queryString .= ' OR author2:"' . $authorName . '"';
            $queryString .= ' OR author_corporate:"' . $authorName . '"';
        }
        return $queryString;
    }

    public function getChartData(AuthorityRecordDriver &$driver): array
    {
        $params = ["facet.field"=>"publishDate",
                   "facet.mincount"=>"1",
                   "facet"=>"on",
                   "facet.sort"=>"count"];

        $identifier = 'Solr';

        $query = new \VuFindSearch\Query\Query($this->getTitlesByQueryParams($driver), 'AllFields');
        $searchCommand = new SearchCommand($identifier, $query,
            0, 0, new ParamBag($params));
        $publishingData = $this->searchService->invoke($searchCommand)->getResult();
        $allFacets = $publishingData->getFacets();
        $publishArray = $allFacets['publishDate'];
        $publishDates = array_keys($publishArray);

        $query = new \VuFindSearch\Query\Query($this->getTitlesAboutQueryParamsChartDate($driver), 'AllFields');
        $searchCommand = new SearchCommand($identifier, $query,
            0, 0, new ParamBag($params));
        $aboutData = $this->searchService->invoke($searchCommand)->getResult();

        $allFacetsAbout = $aboutData->getFacets();
        $aboutArray = $allFacetsAbout['publishDate'];

        $aboutDates = array_keys($aboutArray);

        $allDates = array_merge($publishDates, $aboutDates);
        $allDates = array_unique($allDates);

        $allDatesKeys = array_values($allDates);
        asort($allDatesKeys);

        $chartData = [];
        foreach($allDatesKeys as $oneDate) {
            if(!empty($oneDate)){
                $by = '';
                $about = '';
                if (array_key_exists($oneDate, $publishArray)) {
                    $by = $publishArray[$oneDate];
                }
                if (array_key_exists($oneDate, $aboutArray)) {
                    $about = $aboutArray[$oneDate];
                }
                $chartData[] = array($oneDate,$by,$about);
            }
        }

        return $chartData;
    }

    /**
     * This will be overridden in the corresponding IxTheo View Helper to
     * consider the correct fields based on the translatorLocale.
     */
    protected function getTopicsCloudFieldname($translatorLocale=null): string
    {
        return 'topic_cloud';
    }

    protected function getTopicsCloudField($row, $language=null): array {
        $key = $this->getTopicsCloudFieldname($language);
        return array_unique($row[$key] ?? []);
    }

    public function getTopicsData(AuthorityRecordDriver &$driver): array
    {
        $translatorLocale = $this->getTranslatorLocale();
        $topicsCloudFieldname = $this->getTopicsCloudFieldname($translatorLocale);

        $settings = [
            'maxNumber' => 12,
            'minNumber' => 1,
            'firstTopicLength' => 10,
            'firstTopicWidth' => 10,
            'maxTopicRows' => 1000,
            'minWeight' => 1,
            'filter' => $topicsCloudFieldname,
            'paramBag' => [
                'sort' => 'publishDate DESC',
                'fl' => 'id,'.$topicsCloudFieldname,
            ],
            'searchType' => 'AllFields'
        ];

        $identifier = 'Solr';

        // Note: This query might be critical to peformance. Also set 'fl' parameter
        //       to reduce the result size and avoid out of memory problems.
        //       Example: Martin Luther, 133813363

        $query = new \VuFindSearch\Query\Query($this->getTitlesByQueryParams($driver), null, $settings['searchType']);
        $searchCommand = new SearchCommand($identifier, $query,
            0 , $settings['maxTopicRows'], new ParamBag($settings['paramBag']));
        $result = $this->searchService->invoke($searchCommand)->getResult();

        $countedTopics = [];
        foreach ($result->getResponseDocs() as $oneRecord) {
            $keywords = $this->getTopicsCloudField($oneRecord, $translatorLocale);
            foreach ($keywords as $keyword) {
                if(strpos($keyword, "\\") !== false) {
                    $keyword = str_replace("\\", "", $keyword);
                }
                if (isset($countedTopics[$keyword])) {
                    ++$countedTopics[$keyword];
                } else {
                    $countedTopics[$keyword] = 1;
                }
            }
        }

        arsort($countedTopics);

        $urlHelper = $this->viewHelperManager->get('url');
        $tuefindHelper = $this->viewHelperManager->get('tuefind');

        $searchType = 'AllFields';
        $lookfor = $this->getTitlesByQueryParams($driver);

        if ($tuefindHelper->getTueFindFlavour() == 'ixtheo') {
            $settings['filter'] = 'key_word_chain_bag';
        }

        $topicLink = $urlHelper('search-results').'?lookfor='.urlencode($lookfor).'&type='.urlencode($searchType).'&filter[]='.urlencode($settings['filter']).':';

        $topicsArray = [];
        foreach($countedTopics as $topic => $topicCount) {
            $originalTopicName = $topic;
            $pos = strripos($topic, '"');
            if ($pos !== false) {
                $topic = preg_replace( '/"([^"]*)"/', "«$1»", $topic);
            }

            $topicsArray[] = ['topicTitle'=>$topic, 'topicCount'=>$topicCount, 'topicLink'=>$topicLink.urlencode($originalTopicName)];
        }

        $mainTopicsArray = [];
        if(!empty($topicsArray)){
            $topWeight = $settings['maxNumber'];
            $firstWeight = $topicsArray[0]['topicCount'];
            for($i=0;$i<count($topicsArray);$i++) {
                if($i == 0) {
                    if(mb_strlen($topicsArray[$i]['topicTitle']) > $settings['firstTopicLength']) {
                        $topicsArray[$i]['topicTitle'] = mb_strimwidth($topicsArray[$i]['topicTitle'], 0, $settings['firstTopicWidth'] + 3, '...');
                    }
                }
                $one = $topicsArray[$i];
                if($topWeight > $settings['minNumber']) {
                    $topWeight--;
                }else{
                    if(count($topicsArray) < 30) {
                        $topWeight = $settings['maxNumber']-1;
                    }
                }
                $one['topicNumber'] = $topWeight;
                $mainTopicsArray[] = $one;
            }
            $mainTopicsArray[0]['topicNumber'] = $settings['maxNumber'];
        }
        return [$mainTopicsArray, $settings];
    }

    public function userHasRightsOnRecord(\VuFind\Db\Row\User $user, TitleRecordDriver &$titleRecord): bool
    {
        $userAuthorities = $this->dbTableManager->get('user_authority')->getByUserId($user->id);
        $userAuthorityIds = [];
        foreach ($userAuthorities as $userAuthority) {
            $userAuthorityIds[] = $userAuthority->authority_id;
        }

        $recordAuthorIds = array_merge($titleRecord->getPrimaryAuthorsIds(), $titleRecord->getSecondaryAuthorsIds(), $titleRecord->getCorporateAuthorsIds());
        $matchingAuthorIds = array_intersect($userAuthorityIds, $recordAuthorIds);
        return count($matchingAuthorIds) > 0;
    }

    public function recordExists($authorityId)
    {
        $loadResult = $this->recordLoader->load($authorityId, 'SolrAuth', /* $tolerate_missing=*/ true);
        if ($loadResult instanceof \VuFind\RecordDriver\Missing)
            return false;

        return $loadResult;
    }

}
