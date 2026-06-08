<?php

namespace IxTheo\Controller;

class AlphabrowseController extends \VuFind\Controller\AlphabrowseController
{
    // Use custom trait with result filter
    use Feature\AlphaBrowseTrait;
}
