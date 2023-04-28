<?php

namespace TueFind\View\Helper;

class SetupThemeResources extends \VuFindTheme\View\Helper\SetupThemeResources {

    // We need to override this function to solve some Google Scholar-related problems
    protected function addMetaTags()
    {
        // Set up encoding:
        $headMeta = $this->getView()->plugin('headMeta');

        // TueFind: Disable auto escaping
        $headMeta->setAutoEscape(false);

        $headMeta()->prependHttpEquiv(
            'Content-Type',
            'text/html; charset=' . $this->container->getEncoding()
        );

        // Set up generator:
        $generator = $this->container->getGenerator();
        if (!empty($generator)) {
            $headMeta()->appendName('Generator', $generator);
        }
    }
}
