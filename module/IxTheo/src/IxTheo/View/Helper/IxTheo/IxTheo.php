<?php

namespace IxTheo\View\Helper\IxTheo;

use Psr\Container\ContainerInterface;

/**
 * Some IxTheo specific stuff
 */
class IxTheo extends \Laminas\View\Helper\AbstractHelper
{
    protected $container;
    protected $cachedSubscriptions = null;
    protected $tuefind;
    protected $searchFormRoutes = [
                ['Content/Content/news',false,''],
                ['Content/Content/open_access_journals',false,''],
                ['Content/Content/ixtheo_account',false,''],
                ['Content/Content/ixtheo_content',false,''],
                ['Content/Content/index_biblicus',false,''],
                ['Content/Content/theology_digital',false,''],
                ['Content/Content/canon_law',false,''],
                ['Content/Content/networking',false,''],
                ['Content/Content/open_text',true,'SolrAuth'],
                ['Content/Content/full_text_search',true,'Search2:fulltext']
              ];

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->tuefind = $container->get('ViewHelperManager')->get('tuefind');
    }


    protected function matcher($regex) {
        return function ($element) use ($regex) {
            return preg_match($regex, $element);
        };
    }


    protected function makeClassificationLink($key, $value) {
        return '<a href="/classification/' . preg_replace("/^ixtheo-/", "", $key) . '" target="_blank">' . $value . "</a>";
    }


    protected function makeClassificationText($text) {
        return "<em>" . $text . "</em>";
    }


    // Do not produce links for two letter categories that have further subcategories
    // since these are not present in browsing and faceting
    protected function makeClassificationOutputItem($key, $value, $has_subitems) {
        if ($has_subitems && preg_match("/^ixtheo-[A-Z][A-Z]$/", $key))
            return $this->makeClassificationText($value);
        else
            return $this->makeClassificationLink($key, $value);
    }


    protected function makeClassificationLinkMap($map, $base_regex) {
        $link_entries = [];
        $items = array_filter($map, $this->matcher("/^" . $base_regex . "$/"), ARRAY_FILTER_USE_KEY);
        foreach ($items as $key => $value) {
            $new_base_regex = $key . "[A-Z]";
            $submap = array_filter($map, $this->matcher("/^" . $new_base_regex . "/"), ARRAY_FILTER_USE_KEY);
            array_push($link_entries, $this->makeClassificationOutputItem($key, $value, !empty($submap)));
            if (!empty($submap))
                array_push($link_entries, $this->makeClassificationLinkMap($submap, $new_base_regex));
        }
        return $link_entries;
    }


    public function getNestedIxtheoClassificationArray($translator) {
        $locale = $translator->getLocale();
        $translations = $translator->getAllMessages('default', $locale);
        $ixtheo_classes = array_filter($translations->getArrayCopy(), $this->matcher("/^ixtheo-/"),
                                       ARRAY_FILTER_USE_KEY);
        // Remove unneeded elements
        unset($ixtheo_classes['ixtheo-[Unassigned]']);
        $list = $this->makeClassificationLinkMap($ixtheo_classes, "ixtheo-[A-Z]");
        return $list;
    }


    public function getSubscriptionBundlesTitles() {
        $runner = $this->container->get('VuFind\SearchRunner');
        $request = new \Laminas\Stdlib\Parameters([ 'lookfor' => 'format:"Subscription Bundle"' ]);
        $subscription_bundles = $runner->run($request)->getResults();
        $html = $this->getView()->render('myresearch/subscription_bundles.phtml',
                                        ['subscription_bundles' =>  $subscription_bundles]);
        return $html;
    }


    /**
     * Helper to decide whether a record id subscribed or not
     * (can be used for titles or bundles)
     *
     * - Also returns false if user is not logged in.
     * - Use getAll per user + cache result for better performance
     *   if a lot of items are shown at once (e.g. check search result items)
     */
    protected function isRecordIdSubscribed($recordId) {
        $user = $this->container->get(\VuFind\Auth\Manager::class)->getUserObject();
        if (!$user)
            return false;

        if ($this->cachedSubscriptions === null) {
            $table = $this->container->get(\VuFind\Db\Service\PluginManager::class)
                ->get(\IxTheo\Db\Service\SubscriptionServiceInterface::class);

            $this->cachedSubscriptions = $table->getAll($user, 'asc');
        }

        foreach ($this->cachedSubscriptions as $subscription) {
            if ($subscription->getJournalControlNumberOrBundleName() == $recordId)
                return true;
        }

        return false;
    }


    public function isRecordSubscribed($driver): bool {
        return $this->isRecordIdSubscribed($driver->getUniqueId());
    }


    public function isRecordSubscribedViaBundle(\IxTheo\RecordDriver\SolrDefault $driver, &$subscribedBundleIds=[]): bool {
        $bundleIds = $driver->getBundleIds();
        foreach ($bundleIds as $bundleId) {
            if ($this->isRecordIdSubscribed($bundleId))
                $subscribedBundleIds[] = $bundleId;
        }
        return count($subscribedBundleIds) > 0;
    }

    public function overrideSelectedSearchTab($tabs): array {
        $fullRouteName = $this->tuefind->getFullRouteName();
        foreach($this->searchFormRoutes as $oneRoute) {
            if($oneRoute[0] == $this->tuefind->getFullRouteName() && $oneRoute[1] === true && !empty($oneRoute[2])) {
                foreach($tabs as &$tab) {
                    if(!isset($tab['url'])) {
                        $tab['url'] = "/";
                    }
                    if($tab['id'] == $oneRoute[2]) {
                        $tab['selected'] = 1;
                    }else{
                        $tab['selected'] = 0;
                    }
                }
            }
        }
        return $tabs;
    }

    public function changeSearchFormAction($actionName,$tabs): string {
        $fullRouteName = $this->tuefind->getFullRouteName();
        if($actionName == 'search-results') {
            foreach($this->searchFormRoutes as $oneRoute) {
                foreach($tabs as $tab) {
                    if($fullRouteName == $oneRoute[0] && $tab['selected'] == 1 && $tab['id'] == 'SolrAuth') {
                        $actionName = 'authority-search';
                    }
                    if($fullRouteName == $oneRoute[0] && $tab['selected'] == 1 && $tab['id'] == 'Search2:fulltext') {
                        $actionName = 'search2-results';
                    }
                }
            }
        }
        return $actionName;
    }

    public function availableToShowSearchForm(): bool {
        $showSearchForm = true;
        foreach($this->searchFormRoutes as $oneRoute) {
            if($oneRoute[0] == $this->tuefind->getFullRouteName() && $oneRoute[1] === false) {
                $showSearchForm = false;
            }
        }
        return $showSearchForm;
    }

    public function getBeaconFileName(): string {
        $instance = basename(getenv('VUFIND_LOCAL_DIR'));

        // Note: RelBib uses an entire different page template + docs directory,
        // so it is not covered here.
        switch ($instance) {
            case 'bibstudies':
                return 'bibstudies-beacon.txt';
            case 'churchlaw':
                return 'canonlaw-beacon.txt';
            default:
                return 'ixtheo-beacon.txt';
        }
    }
}
