<?php

namespace TueFind\Controller;

use VuFindSearch\ParamBag;

use function count;

class AuthorityRecordController extends \VuFind\Controller\AuthorityRecordController
{
    public function homeAction()
    {
        $result = parent::homeAction();
        $result->user = $this->getUser();
        return $result;
    }

    public function loadRecord(?ParamBag $params = null, bool $force = false)
    {
        $gndNumber = $this->params()->fromRoute('gnd', $this->params()->fromQuery('gnd'));
        if ($gndNumber != null) {
            $driver = $this->serviceLocator->get(\TueFind\Record\Loader::class)->loadAuthorityRecordByGNDNumber($gndNumber, 'SolrAuth');
        } else {
            $id = $this->params()->fromRoute('id', $this->params()->fromQuery('id'));
            if (empty($id)) {
                $id = $this->params()->fromRoute('authority_id', $this->params()->fromQuery('authority_id'));
            }
            $driver = $this->serviceLocator->get(\VuFind\Record\Loader::class)
                ->load($id, 'SolrAuth');
        }
        return $driver;
    }

    public function requestAccessAction()
    {
        $authorityId = $this->params()->fromRoute('authority_id');
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        if ($this->params()->fromPost('request') == 'yes') {
            $userAuthorityService = $this->getDbService(\TueFind\Db\Service\UserAuthorityServiceInterface::class);
            $userAuthorityService->addRequest($user, $authorityId);

            $authorityAccessHistoryService = $this->getDbService(\TueFind\Db\Service\UserAuthorityHistoryServiceInterface::class);
            $authorityAccessHistoryService->addRequest($user, $authorityId);

            // body
            $renderer = $this->getViewRenderer();
            $message = $renderer->render(
                'Email/authority-request-access.phtml',
                [
                    'userName' => $user->getUsername(),
                    'userEmail' => $user->getEmail(),
                    'authorityUrl' => $this->getServerUrl('solrauthrecord') . $authorityId,
                    'processRequestUrl' => $this->getServerUrl('adminfrontend-showuserauthorities'),
                ]
            );

            // receivers
            $userService = $this->getDbService(\TueFind\Db\Service\UserServiceInterface::class);
            $receivingUsers = $userService->getByRight('user_authorities');
            $receivers = [];
            foreach ($receivingUsers as $receivingUser) {
                $receivers[] = $receivingUser->getEmail();
            }

            $config = $this->getConfig();
            $mailer = $this->serviceLocator->get(\VuFind\Mailer\Mailer::class);
            $receiverCount = count($receivers);
            if ($receiverCount == 0) {
                $receivers = $config->Site->email;
            } else {
                $mailer->setMaxRecipients($receiverCount);
            }

            // send mail
            $mailer->send($receivers, $config->Site->email, 'A user has requested access to an authority dataset', $message);
        }

        return $this->createViewModel(['user' => $user]);
    }
}
