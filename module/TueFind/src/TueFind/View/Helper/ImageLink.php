<?php

namespace TueFind\View\Helper;

class ImageLink extends \VuFindTheme\View\Helper\ImageLink {
    public function __invoke($image)
    {
        // Normalize href to account for themes:
        $relPath = 'images/' . $image;
        $details = $this->themeInfo->findContainingTheme(
            $relPath,
            \VuFindTheme\ThemeInfo::RETURN_ALL_DETAILS
        );

        if (null === $details) {
            return null;
        }

        $urlHelper = $this->getView()->plugin('url');

        // TueFind: Workaround for current bug in VuFind 9.0, see:
        // https://github.com/vufind-org/vufind/pull/3345
        $parts = explode('/', $relPath);
        $encodedRelPath = implode('/', array_map('rawurlencode', $parts));
        $url = $urlHelper('home') . "themes/{$details['theme']}/" . $encodedRelPath;

        $url .= strstr($url, '?') ? '&_=' : '?_=';
        $url .= filemtime($details['path']);

        return $url;
    }
}
