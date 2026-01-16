<?php

namespace VuFindCollapseExpand\Controller;

/**
 * This adds grouping handling to VuFinds search author controller
 *
 * Class SearchController
 * @package VuFindCollapseExpand\Controller
 * @author  Robert Lange <lange@ub.uni-leipzig.de>
 */
class AuthorController extends \VuFind\Controller\AuthorController
{
    /**
     * Sets the configuration for displaying author results
     *
     * @return mixed
     */
    public function resultsAction()
    {
        $collapseExpandConfig = $this->serviceLocator->get(\VuFindCollapseExpand\Config\CollapseExpand::class);

        $view = \VuFind\Controller\AuthorController::resultsAction();
        $view->collapseExpandConfig = $collapseExpandConfig->isActive();
        return $view;
    }
}