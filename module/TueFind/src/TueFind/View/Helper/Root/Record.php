<?php

namespace TueFind\View\Helper\Root;

class Record extends \VuFind\View\Helper\Root\Record {

    /**
     * This override is a backport to fix issue 2473 in VuFind 8.1.
     * The fix will be officially included in VuFind 9.0.
     *
     * see also: https://github.com/vufind-org/vufind/pull/2841
     */
    public function getLink($type, $lookfor)
    {
        $link = $this->renderTemplate(
            'link-' . $type . '.phtml',
            ['driver' => $this->driver, 'lookfor' => $lookfor]
        );

        $prepend = (strpos($link, '?') === false) ? '?' : '&amp;';

        $link .= $this->getView()->plugin('searchTabs')
            ->getCurrentHiddenFilterParams($this->driver->getSourceIdentifier(), false, $prepend);
        return $link;
    }
}
