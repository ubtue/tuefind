<?php

namespace IxTheo\AjaxHandler;

use IxTheo\Db\Service\PDASubscriptionService;
use Laminas\Mvc\Controller\Plugin\Params;
use VuFind\Db\Entity\UserEntityInterface;
use VuFind\I18n\Translator\TranslatorAwareInterface;

class DeletePDASubscription extends \VuFind\AjaxHandler\AbstractBase implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected PDASubscriptionService $PDASubscriptionService;

    protected ?UserEntityInterface $user;

    public function __construct(PDASubscriptionService $PDASubscriptionService, ?UserEntityInterface $user)
    {
        $this->PDASubscriptionService = $PDASubscriptionService;
        $this->user = $user;
    }

    public function handleRequest(Params $params)
    {
        if (!$this->user) {
            return $this->formatResponse(
                $this->translate('You must be logged in first'),
                self::STATUS_HTTP_NEED_AUTH
            );
        }

        $delete = $params->fromPost('delete');
        $source = $params->fromPost('source');
        if (empty($delete) || empty($source)) {
            return $this->formatResponse(
                $this->translate('bulk_error_missing'),
                self::STATUS_HTTP_BAD_REQUEST
            );
        }

        $this->PDASubscriptionService->unsubscribe($this->user, $delete);

        return $this->formatResponse('');
    }
}
