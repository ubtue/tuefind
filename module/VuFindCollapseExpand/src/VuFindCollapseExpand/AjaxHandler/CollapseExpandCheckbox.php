<?php

namespace VuFindCollapseExpand\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class CollapseExpandCheckbox
 * This Ajax handler is used to store the state of the checkbox for
 * Collapse and Expand
 *
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */
class CollapseExpandCheckbox extends \VuFind\AjaxHandler\AbstractBase implements
    \VuFindCollapseExpand\Config\CollapseExpandConfigAwareInterface
{
    use \VuFindCollapseExpand\Config\CollapseExpandConfigAwareTrait;

    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {
        $status = $params->fromPost('status');
        $status = $status == 'true';
        $this->collapseExpandConfig->store(['collapse.enabled' => $status]);
        return $this->formatResponse([], 200);
    }
}
