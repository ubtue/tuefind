<?php

namespace VuFindCollapseExpand\Search\Solr;

use VuFindCollapseExpand\Config\CollapseExpandConfigAwareInterface;
use VuFindCollapseExpand\Config\CollapseExpandConfigAwareTrait;

class Params extends \VuFind\Search\Solr\Params implements CollapseExpandConfigAwareInterface
{
    use CollapseExpandConfigAwareTrait;
    use ParamsTrait;
}
