<?php

namespace TueFind\Sitemap;

class Sitemap extends \VuFind\Sitemap\Sitemap
{
    /**
     * TueFind: We override this function to make "changefreq" optional
     *          and also add the new optional "lastmod" parameter.
     *          see also https://www.sitemaps.org/de/protocol.html
     */
    protected function getEntry($url)
    {
        if (is_array($url)) {
            $link = $url['url'];
            $languages = $url['languages'] ?? [];
            $frequency = $url['frequency'] ?? '';
            $lastmod = $url['lastmod'] ?? '';
        } else {
            $link = $url;
            $languages = [];
            $frequency = '';
            $lastmod = '';
        }
        $alternativeLinks = '';
        if ($languages) {
            $lngParam = strpos($link, '?') === false ? '?lng=' : '&lng=';
            $links = [];
            foreach ($languages as $sitemapLng => $vufindLng) {
                $lngLink = $vufindLng
                    ? $link . $lngParam . urlencode($vufindLng) : $link;
                $links[] = '<xhtml:link rel="alternate" hreflang="'
                    . htmlspecialchars($sitemapLng) . '">'
                    . htmlspecialchars($lngLink)
                    . '</xhtml:link>';
            }

            $alternativeLinks = '  ' . implode("\n  ", $links) . "\n";
            $this->xhtmlNamespaceNeeded = true;
        } else {
            $locs[] = '<loc>' . htmlspecialchars($link) . '</loc>';
        }
        $link = htmlspecialchars($link);
        $freq = htmlspecialchars($frequency ?: $this->frequency);

        $html = "<url>\n"
            . "  <loc>$link</loc>\n";
        if ($freq != '') {
            $html .= "  <changefreq>$freq</changefreq>\n";
        }
        if ($lastmod != '') {
            $html .= "  <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>\n";
        }
        $html .= $alternativeLinks
            . "</url>\n";
        return $html;
    }
}
