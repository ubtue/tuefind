<?php

namespace TueFind\View\Helper\TueFind;

use Psr\Container\ContainerInterface;
use VuFind\Search\SearchTabsHelper;
use VuFind\View\Helper\Root\SearchTabs;

/**
 * General View Helper for TueFind, containing miscellaneous functions
 */
class TueFind extends \Laminas\View\Helper\AbstractHelper
              implements \VuFind\I18n\Translator\TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Php version of perl's MIME::Base64::URLSafe, that provides an url-safe
     * base64 string encoding/decoding (compatible with python base64's urlsafe methods)
     *
     * @see https://www.php.net/manual/de/function.base64-encode.php
     *
     * @param string $string
     * @return string
     */
    public function base64UrlEncode(string $string): string {
        $data = base64_encode($string);
        $data = str_replace(['+','/','='],['-','_','.'], $data);
        return $data;
    }

    /**
     * Php version of perl's MIME::Base64::URLSafe, that provides an url-safe
     * base64 string encoding/decoding (compatible with python base64's urlsafe methods)
     *
     * @see https://www.php.net/manual/de/function.base64-encode.php
     *
     * @param string $string
     *
     * @return string
     */
    public function base64UrlDecode(string $string): string {
        $data = str_replace(['-','_','.'], ['+','/','='], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * Convert a Date/Time string to ISO 8601 format.
     *
     * If the given datetime consists only of a year, return the plain year.
     * This is supported by ISO 8601 (see RFC 3339: date = datespec-full / datespec-year /...)
     *
     * Else return a full ISO 8601 conform string with month/day/etc.
     *
     * On any error, the original given datetime string is returned.
     *
     * @param string $datetimeRaw
     * @return string
     */
    public function convertDateTimeToIso8601($datetimeRaw) {
        $datetimeCleaned = preg_replace('"[\[\]]"', '', $datetimeRaw);
        if (preg_match('"^\d{4}$"', $datetimeCleaned))
            return $datetimeCleaned;

        $datetime = strtotime($datetimeRaw);
        if ($datetime === false)
            return $datetimeRaw;

        return date('c', $datetime);
    }

    /**
     * Check if a facet value is equal to '[Unassigned]' or its translation
     *
     * @param string $value
     * @return bool
     */
    public function isUnassigned($value) {
        return ($value == '[Unassigned]') || ($value == $this->translate('[Unassigned]'));
    }

    /**
     * Get TueFind configuration from INI file.
     *
     * @param string $id Config file id, default 'tuefind'
     *                   use e.g. 'config' for vufind's config.ini instead
     *
     * @return \Laminas\Config\Config
     */
    public function getConfig($id = 'tuefind', ?array $options = null) {
        return $this->container->get('VuFind\Config\PluginManager')->get($id, $options);
    }

    /**
     * Get name of the current controller
     * (If no Controller is found in URL, returns default value 'index')
     *
     * @return string
     */
    public function getControllerName() {
        $default = 'index';
        $route_match = $this->container->get('application')->getMvcEvent()->getRouteMatch();
        if ($route_match == null)
            return $default;
        else
            return $route_match->getParam('controller', $default);
    }


    public function getRouteParams() {
        $defaultRouteParams = [
            'controller' => null,
            'action' => null
        ];
        $route_match = $this->container->get('application')->getMvcEvent()->getRouteMatch();
        if ($route_match == null){
            return $defaultRouteParams;
        }else{
            return $route_match->getParams();
        }
    }

    /**
     * Calculate percentage of a count related to a solr search result
     *
     * @param int $count
     * @param \VuFind\Search\Solr\Results $results
     *
     * @return double
     */
    public function getOverallPercentage($count, \VuFind\Search\Solr\Results $results) {
        return ($count * 100) / $results->getResultTotal();
    }

    /**
     * Calculate percentage and get localized string
     *
     * @param \Laminas\View\Renderer\PhpRenderer $view
     * @param int $count
     * @param \VuFind\Search\Solr\Results $results
     *
     * @return string
     */
    public function getLocalizedOverallPercentage(\Laminas\View\Renderer\PhpRenderer $view,
                                           $count, \VuFind\Search\Solr\Results $results) {
        $percentage = $this->getOverallPercentage($count, $results);
        return $percentage > 0.1 ? $view->localizedNumber($percentage, 1) : "&lt; 0.1";
    }

    /**
     * Get Team Email Address
     *
     * @return string
     */
    public function getTeamEmail() {
        $config = $this->container->get('VuFind\Config')->get('config');
        $team_email = isset($config->Site->email_team) ? $config->Site->email_team : '';
        return $team_email;
    }

    /**
     * Appropriately format the roles for authors
     */
    public function formatRoles(array $roles): string {
        if (count($roles) == 0) {
            return '';
        }

        $translate = function ($element) {
            $translatedRoles = [];
            if (!is_array($element)) {
                $translatedRoles[] = $this->translate('CreatorRoles::' . $element);
            } else {
                foreach ($element as $str) {
                    $translatedRoles[] = $this->translate('CreatorRoles::' . $str);
                }
            }
            return implode(', ', $translatedRoles);
        };
        return ' (' . implode(', ', array_unique(array_map($translate, $roles))) . ')';
    }

    /**
     * Search for specific RSS feed icon, return generic RSS icon if not found
     *
     * @param string $rssFeedId
     *
     * @return string
     */
    public function getRssFeedIcon($rssFeedId='rss') {
        $imgSrc = $this->getView()->imageLink('rss/' . $rssFeedId . '.png');
        if ($imgSrc == null)
            $imgSrc = $this->getView()->imageLink('rss/rss.png');

        return $imgSrc;
    }

     /**
     * Search for specific icon in details table, return generic icon if not found
     *
     * @param string $detailsId
     *
     * @return string
     */
    public function getDetailsIcon($detailsId='details') {
        if (str_contains($detailsId, "(")) {
            $detailsId = trim(explode("(", $detailsId)[0]);
        }
        return $this->getView()->imageLink('details/' . $detailsId . '.png');
    }

    /**
     * Filter unwanted stuff from RSS item description (especially images)
     *
     * @param string $htmlPart
     *
     * @return string
     */
    private function filterRssItemDescription(string $htmlPart): string {
        $html = '<html><meta charset="UTF-8"/><body id="htmlPartWrapper">'.$htmlPart.'</body></html>';

        $dom = new \DOMDocument();
        $dom->recover = true;
        $dom->strictErrorChecking = false;
        if (!@$dom->loadHTML($html))
            return $htmlPart;

        $wrapper = $dom->getElementById('htmlPartWrapper');

        // Elements need to be copied before removing to avoid iterator problem
        $images = $wrapper->getElementsByTagName('img');
        $imageReferences = [];
        foreach ($images as $image)
            $imageReferences[] = $image;
        foreach ($imageReferences as $imageReference)
            $imageReference->parentNode->removeChild($imageReference);

        return $dom->saveHTML($wrapper);
    }

    /**
     * Get URL to redirect page which also saves the redirect with timestamp for later analysis.
     * Uses special variant of base64 without url-specific '/' and '+' characters.
     *
     * @param string $targetUrl
     * @param string $group
     *
     * @return string
     */
    public function getRedirectUrl(string $targetUrl, string $group=null): string {
        $urlHelper = $this->container->get('ViewHelperManager')->get('url');
        return $urlHelper('redirect', ['url' => $this->base64UrlEncode($targetUrl), 'group' => $group]);
    }

    /**
     * Parse the RSS feed and return a short overview of the first few entries
     *
     * @param int  $maxItemCount            Max items to read from file
     * @param bool $onlyNewestItemPerFeed   Only the newest item per feed will be returned.
     *
     * @return array
     */
    public function getRssNewsEntries(int $maxItemCount=null, bool $onlyNewestItemPerFeed=false) {

        $rssTable = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('rss_item');
        $rssItems = $rssTable->getItemsSortedByPubDate($this->getTueFindInstance());

        $rssItemsToReturn = [];
        $i = 0;
        $processedFeeds = [];

        foreach ($rssItems as $rssItem) {

            if ($maxItemCount !== null && $i >= $maxItemCount)
                break;

            $rssItem['item_description'] = $this->filterRssItemDescription($rssItem['item_description']);
            // Do certain items need to be decoded with htmlspecialchars_decode?

            if ($onlyNewestItemPerFeed === false || !in_array($rssItem['feed_name'], $processedFeeds)) {
                $rssItemsToReturn[] = $rssItem;
                ++$i;
            }
            $processedFeeds[] = $rssItem['feed_name'];
        }

        return $rssItemsToReturn;
    }

    /**
     * Get URL of our own generated RSS feed (from rss_aggregator)
     *
     * @return string
     */
    public function getRssNewsUrl() {
        $rssFeedPath = $this->getConfig()->General->rss_feed_path;
        if (!is_file($rssFeedPath))
            return false;

        return str_replace(getenv('VUFIND_HOME') . '/public', '', $rssFeedPath);
    }

    /**
     * Implemented as a workaround, because getenv('TUEFIND_FLAVOUR')
     * does not work in apache environment.
     */
    public function getTueFindFlavour(): string {
        if ($this->getTueFindSubtype() == 'KRI')
            return 'krimdok';
        else
            return 'ixtheo';
    }


    /**
      * Get TueFind Instance as defined by VUFIND_LOCAL_DIR variable
      * @return string
      */
    public function getTueFindInstance() {
        return basename(getenv('VUFIND_LOCAL_DIR'));
    }

    public function getTueFindSubsystem(): string {
        $instance = $this->getTueFindInstance();
        $map = ['ixtheo' => 'ixtheo',
                'relbib' => 'relbib',
                'bibstudies' => 'biblestudies',
                'churchlaw' => 'canonlaw'];
        return $map[$instance] ?? $instance;
    }

    /**
      * Derive textual description of TueFind (Subsystems of IxTheo return IxTheo)
      * @return string or false of no matching value could be found
      */
    public function getTueFindType() {
        $instance = $this->getTueFindInstance();
        $instance = preg_replace('/\d+$/', "", $instance);
        switch ($instance) {
            case 'ixtheo':
            case 'bibstudies':
            case 'churchlaw':
                return 'IxTheo';
            case 'relbib':
                return 'RelBib';
            case 'krimdok':
               return 'KrimDok';
        }
        return false;
    }

    /**
      * Derive textual description of TueFind Subsystem
      * @return string
      */
    public function getTueFindSubtype() {
        $instance = $this->getTueFindInstance();
        $instance = preg_replace('/\d+$/', "", $instance);
        switch ($instance) {
            case 'ixtheo':
                return 'IXT';
            case 'bibstudies':
                return 'BIB';
            case 'churchlaw':
                return 'CAN';
            case 'relbib':
                return 'REL';
            case 'krimdok':
               return 'KRI';
        }
        throw new \Exception('can\'t determine TueFind subsystem type for "' . $instance . '"!');
    }

    /**
      * Derive the German FID denomination
      * @return string or false of no matching value could be found
      */
    public function getTueFindFID($short=false) {
        $instance = $this->getTueFindInstance();
        $instance = preg_replace('/\d+$/', "", $instance);
        $fid = false;
        switch($instance) {
            case 'ixtheo':
            case 'bibstudies':
            case 'churchlaw':
                $fid = 'Theologie';
                break;
            case 'relbib':
                $fid = 'Religionswissenschaft';
                break;
            case 'krimdok':
                $fid = 'Kriminologie';
                break;
        }

        if ($fid == false || $short)
            return $fid;
        else
            return 'FID ' . $fid;
    }

    /**
      * Get the user address from a logged in user
      * @return string
      */
    public function getUserEmail() {
        $auth = $this->container->get('ViewHelperManager')->get('auth');
        $manager = $auth->getManager();
        return ($user = $manager->getUserObject()) ? $user->email : '';
    }

    /**
    * Get the first name of the logged in user
    * @return string
    */
    public function getUserFirstName() {
        $auth = $this->container->get('ViewHelperManager')->get('auth');
        $manager = $auth->getManager();
        return ($user = $manager->getUserObject()) ? $user->firstname : '';
    }

    /**
     * Get the full name of the logged in user
     * @return string
     */
    public function getUserFullName() {
        $auth = $this->container->get('ViewHelperManager')->get('auth');
        $manager = $auth->getManager();
        return ($user = $manager->getUserObject()) ? $user->firstname . ' ' . $user->lastname : '';
    }

    /**
      * Get the last name of the logged in user
      * @return string
      */
    public function getUserLastName() {
        $auth = $this->container->get('ViewHelperManager')->get('auth');
        $manager = $auth->getManager();
        return ($user = $manager->getUserObject()) ? $user->lastname : '';
    }

    public function isRssSubscriptionEnabled(): bool {
        $setting = $this->getConfig()->General->rss_subscriptions ?? 'disabled';
        return $setting == 'enabled';
    }

    /**
     * Check if a searchbox tab is enabled, e.g. "SolrAuth".
     */
    public function isSearchTabEnabled($tabId): bool {
        $tabConfig = $this->getConfig('config')->SearchTabs ?? [];
        foreach ($tabConfig as $tabKey => $tabText) {
            if ($tabKey == $tabId)
                return true;
        }
        return false;
    }

    /**
     * Check if user account deletion is enabled in config file.
     */
    public function isUserAccountDeletionEnabled() {
        $config = $this->container->get('VuFind\Config')->get('config');
        return !empty($config->Authentication->account_deletion);
    }

    public function printSuperiorSeries($superior_record) {
        $superior_series = $superior_record->tryMethod('getSeries');
        if (is_array($superior_series)) {
            foreach ($superior_series as $current) {
                echo 'T3 - ' . (is_array($current) ? $current['name'] : $current) . "\r\n";
                if (!empty($current['number']))
                    echo 'SV - ' . $current['number'] . "\r\n";
            }
            return true;
        }
        return false;
    }

    public function printPublicationInformation($pubPlaces, $pubDates, $pubNames) {
        if (is_array($pubPlaces) && is_array($pubDates) && is_array($pubNames) &&
            !(empty($pubPlaces) && empty($pubDates) && empty($pubNames)))
        {
            $total = min(count($pubPlaces), count($pubDates), count($pubNames));
            // if we have pub dates but no other details, we still want to export the year:
            if ($total == 0 && count($pubDates) > 0) {
                $total = 1;
            }
            $dateTimeHelper = $this->container->get('ViewHelperManager')->get('dateTime');
            for ($i = 0; $i < $total; $i++) {
                if (isset($pubPlaces[$i])) {
                    echo "CY  - " . rtrim(str_replace(array('[', ']'), '', $pubPlaces[$i]), ': '). "\r\n";
                }
                if (isset($pubNames[$i])) {
                    echo "PB  - " . rtrim($pubNames[$i], ", ") . "\r\n";
                }
                $date = trim($pubDates[$i], '[]. ');
                if (strlen($date) > 4) {
                    $date = $dateTimeHelper->extractYear($date);
                }
                if ($date) {
                    echo 'PY  - ' . "$date\r\n";
                }
            }
            return true;
        }
        return false;
    }

    public function getKfL() {
        return $this->container->get(\TueFind\Service\KfL::class);
    }

    public function getPublicationByControlNumber(string $controlNumber) {
        $publicationTable = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('publication');
        return $publicationTable->getByControlNumber($controlNumber);
    }

    public function getUserAccessState($authorityId, $userId = null): array
    {
        $table = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('user_authority');
        $row = $table->getByAuthorityId($authorityId);

        $result = ['availability' => '', 'access_state' => ''];
        if ($row == null) {
            // Nobody got permission yet, feel free to take it
            $result['availability'] = 'free';
        } else {
            $result['access_state'] = $row->access_state;
            if (isset($userId) && ($userId == $row->user_id)) {
                $result['availability'] = 'mine';
            } else {
                $result['availability'] = 'other';
            }
        }

        return $result;
    }

    public function getUserAccessPublishButton(array $authorsIds): bool
    {
        $access = false;
        $auth = $this->container->get('ViewHelperManager')->get('auth');
        $manager = $auth->getManager();
        $user = $manager->getUserObject();
        if($user) {
            $table = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('user_authority');
            foreach($authorsIds as $authorityId) {
                $row = $table->getByUserIdAndAuthorityId($user->id,$authorityId);
                if(!empty($row) && $row->access_state == "granted") {
                    $access = true;
                }
            }
        }

        return $access;
    }

    public function showSecondaryAuthorsButton($secondaryAuthorsIds): bool
    {
        $showButton = false;
        $auth = $this->container->get('ViewHelperManager')->get('auth');
        $manager = $auth->getManager();
        $user = $manager->getUserObject();
        if($user) {
            $table = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('user_authority');
            foreach($secondaryAuthorsIds as $authorId) {
                $row = $table->getByUserIdAndAuthorityId($user->id,$authorId);
                if(!empty($row) && $row->access_state == "granted") {
                    $showButton = true;
                }
            }

        }
        return $showButton;
    }


    public function userAlreadyMadeAuthorityRequest($userId): bool
    {
        $table = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('user_authority');
        $row = $table->getByUserIdCurrent($userId);
        return (empty($row))? false: true;
    }


    public function getUserAccessPublishRecord($userId, $recordAuthors): bool
    {
        $authorsIds = [];
        foreach($recordAuthors as $authorArray) {
            if(!empty($authorArray) && is_array($authorArray)) {
                foreach($authorArray as $authors) {
                    if(isset($authors['id'])) {
                        $authorsIds[] = $authors['id'][0];
                    }
                }
            }
        }

        $table = $this->container->get(\VuFind\Db\Table\PluginManager::class)->get('user_authority');
        return $table->hasGrantedAuthorityRight($userId, $authorsIds);
    }

    public function showRSSBlock(): bool {
        $instance = $this->getTueFindInstance();
        $map = ['ixtheo'];
        return in_array($instance, $map);
    }

    public function getFullRouteName(): string {
        $currentRoute = $this->getRouteParams();
        return isset($currentRoute['page']) ? $currentRoute['controller'].'/'.$currentRoute['action'].'/'.$currentRoute['page'] : $currentRoute['controller'].'/'.$currentRoute['action'];
    }

    public function getPublicationEmail(): string {
        $config = $this->getConfig('tuefind');
        return $config->Publication->email ?? "";
    }

    public function getSelfArchivingEmail(): string {
        $config = $this->getConfig('tuefind');
        return $config->Publication->selfarchivingemail ?? "";
    }

    public function getSiteEmail(): string {
        $config = $this->container->get('VuFind\Config')->get('config');
        return $config->Site->email ?? "";
    }

    public function getHierarchicalDisplayText($filterDisplayText): string {
        return $this->container->get(\VuFind\Search\Solr\HierarchicalFacetHelper::class)->formatDisplayText($filterDisplayText)->getDisplayString();
    }

    public function isNewItem(string $searchClassId): bool {
        $hiddenFilters = $this->getView()->plugin('searchTabs')->getHiddenFilters($searchClassId);
        if(isset($hiddenFilters['first_indexed'])) {
            return true;
        }
        return false;
    }

    public function searchMenuNavActive(): array {
        $currentRoute = $this->getRouteParams();
        $navActive['historyActive'] = '';
        $navActive['newItemActive'] = '';
        $navActive['keyWordChainSearchActive'] = '';
        $className = 'active';
        if($currentRoute['controller'] == 'Search' && $currentRoute['action'] == 'History') {
            $navActive['historyActive'] = $className;
        }
        if($currentRoute['controller'] == 'Search' && $currentRoute['action'] == 'NewItem') {
            $navActive['newItemActive'] = $className;
        }
        if($currentRoute['controller'] == 'Keywordchainsearch' && $currentRoute['action'] == 'Home' || $currentRoute['controller'] == 'Browse' || $currentRoute['controller'] == 'Alphabrowse' && $currentRoute['action'] == 'Home') {
            $navActive['keyWordChainSearchActive'] = $className;
        }
        return $navActive;
    }
}
