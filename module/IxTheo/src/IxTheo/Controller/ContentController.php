<?php

namespace IxTheo\Controller;

class ContentController extends \VuFind\Controller\ContentController
{

    public function contentAction()
    {
        $result = parent::contentAction();

        // This was not possible using apache settings, so unfortunately we need to hardcode it here.
        // Some servers use a different default referrer policy, but we need to reset it on certain pages
        // else e.g. there will be problems using OpenStreetMap.
        $page = $this->params()->fromRoute('page');
        if (strtolower($page) == 'networking') {
            // See Issue #3611
            // https://wiki.openstreetmap.org/wiki/Referer
            $this->getResponse()->getHeaders()->addHeaderLine('Referrer-Policy', 'origin-when-cross-origin');
        }

        return $result;
    }
}
