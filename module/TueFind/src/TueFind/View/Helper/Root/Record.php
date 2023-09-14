<?php

namespace TueFind\View\Helper\Root;

class Record extends \VuFind\View\Helper\Root\Record {

    /**
    * This file originally contained a backport to fix issue 2473 in VuFind 8.1.
    * The fix has been officially included in VuFind 9.0, so the code has been removed.
    * We still keep this class since an existing IxTheo class is extending from it,
    * just in case we need it again in the future.
    *
    * see also: https://github.com/vufind-org/vufind/pull/2841
    */
}
