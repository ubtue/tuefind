<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;

class CmsPageContentTransformer extends \VuFind\AjaxHandler\AbstractBase
{
    protected $viewHelper;

    public function __construct(\TueFind\View\Helper\TueFind\TueFind $viewHelper)
    {
        $this->viewHelper = $viewHelper;
    }

    /**
     * Expose CMS content transformation to be used e.g. in the WYSIWYG editor.
     * Use POST instead of GET due to potentially big parameter
     */
    public function handleRequest(Params $params)
    {
        $content = $params->fromPost('content', $params->fromQuery('content'));

        if (empty($content)) {
            return $this->formatResponse(
                'content parameter missing or empty',
                self::STATUS_HTTP_BAD_REQUEST
            );
        }

        $transformedContent = $this->viewHelper->transformCmsPageContent($content);
        $response = ['content' => $transformedContent];
        return $this->formatResponse($response);
    }
}
