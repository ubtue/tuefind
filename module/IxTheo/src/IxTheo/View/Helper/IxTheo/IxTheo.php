<?php

namespace IxTheo\View\Helper\IxTheo;

use Interop\Container\ContainerInterface;
use VuFindSearch\Query\Query;

/**
 * Some IxTheo specific stuff
 */
class IxTheo extends \Zend\View\Helper\AbstractHelper
{
    protected $container;
    protected $cachedSubscriptions = null;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
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
        $request = new \Zend\Stdlib\Parameters([ 'lookfor' => 'format:"Subscription Bundle"' ]);
        $subscription_bundles = $runner->run($request)->getResults();
        $html = $this->getView()->render('myresearch/subscription_bundles.phtml',
                                        ['subscription_bundles' =>  $subscription_bundles]);
        return $html;
    }


    /**
     * Helper to decide whether to show unsubscribe button
     * - Never show "unsubscribe" when user is not logged in.
     * - Use getAll per user + cache result for better performance
     *   if a lot of items are shown at once (e.g. check search result items)
     */
    public function isRecordSubscribed($driver) {
        $user = $this->container->get(\VuFind\Auth\Manager::class)->isLoggedIn();
        if (!$user)
            return false;

        if ($this->cachedSubscriptions === null) {
            $table = $this->container->get(\VuFind\Db\Table\PluginManager::class)
                ->get('subscription');

            $this->cachedSubscriptions = $table->getAll($user->id, 'asc');
        }

        foreach ($this->cachedSubscriptions as $subscription) {
            if ($subscription->journal_control_number_or_bundle_name == $driver->getUniqueId())
                return true;
        }

        return false;
    }

}
