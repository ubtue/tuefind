<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\View\Renderer\PhpRenderer;
use TueFind\Db\Service\UserServiceInterface;
use TueFind\Service\DSpace8;
use VuFind\Db\Entity\UserEntityInterface;

class Admin extends \VuFind\AjaxHandler\AbstractBase
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $viewRenderer;

    protected $userService;

    protected DSpace8 $dspace8;

    public function __construct(
        PhpRenderer $viewRenderer,
        protected ?UserEntityInterface $user,
        UserServiceInterface $userService,
        DSpace8 $dspace8
    ) {
        $this->viewRenderer = $viewRenderer;
        $this->userService = $userService;
        $this->dspace8 = $dspace8;
    }

    public function handleRequest(Params $params)
    {
        // check if user is logged in
        if (!$this->user) {
            return $this->formatResponse([
                'status' => 'ERROR',
                'data' => $this->translate('You must be logged in first'),
            ], self::STATUS_HTTP_NEED_AUTH);
        }

        if ($params->fromQuery('action') === 'searchUsers') {
            try {
                $searchEmails = $params->fromQuery('q');

                $subSystem = $this->viewRenderer->getHelperPluginManager()->get(\TueFind\View\Helper\TueFind\TueFind::class)->getTueFindSubsystem();
                if ($subSystem == 'krimdok') {
                    $subSystem = null;
                }

                $users = $this->userService->getLikeEmail($searchEmails, $subSystem);

                $htmlContent = $this->viewRenderer->render('adminfrontend/ajax/admin/searchusers', [
                    'users' => $users,
                    'query' => $searchEmails,
                ]);

                return $this->formatResponse($htmlContent ?: 'No content generated.');
            } catch (\Exception $e) {
                return $this->formatResponse(
                    "<span class='btn btn-danger m-1' style='pointer-events: none;'>Template render error: " . htmlspecialchars($e->getMessage()) . '</span>'
                );
            }
        }

        if ($params->fromQuery('action') === 'changeUserRights') {
            try {
                $userId = (int)$params->fromPost('userId', $params->fromQuery('userId'));
                $rights = $params->fromPost('rights', $params->fromQuery('rights'));

                $user = $this->userService->getUserById($userId);

                if (!$user) {
                    return $this->formatResponse([
                        'status'  => 'ERROR',
                        'message' => 'User not found',
                    ], self::STATUS_HTTP_ERROR);
                }

                $user->setTueFindRights($rights);

                $this->userService->persistEntity($user);

                return $this->formatResponse([
                        'status'  => 'success',
                        'message' => '<div class="alert alert-success m-0">Rights updated successfully!</div>',
                    ]);
            } catch (\Exception $e) {
                return $this->formatResponse([
                   'status'  => 'ERROR',
                   'message' => "<span class='btn btn-danger m-1' style='pointer-events: none;'>Error: " . htmlspecialchars($e->getMessage()) . '</span>',
                ]);
            }
        }

        if ($params->fromQuery('action') === 'checkDspace') {
            try {
                
                $collection_name = $params->fromPost('collection_name', $params->fromQuery('collection_name'));

                $client = $this->dspace8;
                $client->login();
                
                $collection = $client->getCollectionByName($collection_name);
                $status = false;
                if(!empty($collection)) {
                     $status = true;
                }
                return $this->formatResponse([
                    'status'  => $status
                ]);
            } catch (\Exception $e) {
                return $this->formatResponse(
                    "<span class='btn btn-danger m-1' style='pointer-events: none;'>Template render error: " . htmlspecialchars($e->getMessage()) . '</span>'
                );
            }
        }

        return [];
    }
}
