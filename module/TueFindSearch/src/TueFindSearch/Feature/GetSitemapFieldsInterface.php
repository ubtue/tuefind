<?php

namespace TueFindSearch\Feature;

use VuFindSearch\ParamBag;
use VuFindSearch\Query\AbstractQuery;

/**
 * Similar to VuFind getIds Feature, but with sitemap-specific result fields
 */
interface GetSitemapFieldsInterface
{
    public function getSitemapFields(
        AbstractQuery $query,
        $offset,
        $limit,
        ParamBag $params = null
    );
}
