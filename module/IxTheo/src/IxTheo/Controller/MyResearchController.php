<?php

namespace IxTheo\Controller;

use VuFind\Search\RecommendListener,
    VuFind\Exception\ListPermission as ListPermissionException;

class MyResearchController extends \TueFind\Controller\MyResearchController
{
    public function changeEmailAction() {
        $view = parent::changeEmailAction();

        $user = $this->getUser();

        // Update the TAD access flag:
        exec("/usr/local/bin/set_tad_access_flag.sh " . $user->id);

        return $view;
    }


    public function pdasubscriptionsAction() {

        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new \Exception('Lists disabled');
        }

        // Check for "delete item" request; parameter may be in GET or POST depending
        // on calling context.
        $deleteId = $this->params()->fromPost(
            'delete', $this->params()->fromQuery('delete')
        );
        if ($deleteId) {
            $deleteSource = $this->params()->fromPost(
                'source',
                $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
            );
            // If the user already confirmed the operation, perform the delete now;
            // otherwise prompt for confirmation:
            $confirm = $this->params()->fromPost(
                'confirm', $this->params()->fromQuery('confirm')
            );
            if ($confirm) {
                $success = $this->performDeletePDASubscription($deleteId, $deleteSource);
                if ($success !== true) {
                    return $success;
                }
            } else {
                return $this->confirmDeletePDASubscription($deleteId, $deleteSource);
            }
        }

        // If we got this far, we just need to display the subscriptions:
        try {
            $runner = $this->serviceLocator->get('VuFind\SearchRunner');

            // We want to merge together GET, POST and route parameters to
            // initialize our search object:
            $request = $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
                + ['id' => $this->params()->fromRoute('id')];

            // Set up listener for recommendations:
            $rManager = $this->serviceLocator->get('VuFind\RecommendPluginManager');
            $setupCallback = function ($runner, $params, $searchId) use ($rManager) {
                $listener = new RecommendListener($rManager, $searchId);
                $listener->setConfig(
                    $params->getOptions()->getRecommendationSettings()
                );
                $listener->attach($runner->getEventManager()->getSharedManager());
            };

            $results = $runner->run($request, 'PDASubscriptions', $setupCallback);
            return $this->createViewModel(
                ['params' => $results->getParams(), 'results' => $results]
            );
        } catch (ListPermissionException $e) {
            if (!$this->getUser()) {
                return $this->forceLogin();
            }
            throw $e;
        }
    }

    public function subscriptionsAction() {

        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new \Exception('Lists disabled');
        }

        // Check for "delete item" request; parameter may be in GET or POST depending
        // on calling context.
        $deleteId = $this->params()->fromPost(
            'delete', $this->params()->fromQuery('delete')
        );
        if ($deleteId) {
            $deleteSource = $this->params()->fromPost(
                'source',
                $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
            );
            // If the user already confirmed the operation, perform the delete now;
            // otherwise prompt for confirmation:
            $confirm = $this->params()->fromPost(
                'confirm', $this->params()->fromQuery('confirm')
            );
            if ($confirm) {
                $success = $this->performDeleteSubscription($deleteId, $deleteSource);
                if ($success !== true) {
                    return $success;
                }
            } else {
                return $this->confirmDeleteSubscription($deleteId, $deleteSource);
            }
        }

        // If we got this far, we just need to display the subscriptions:
        try {
            $runner = $this->serviceLocator->get('VuFind\SearchRunner');

            // We want to merge together GET, POST and route parameters to
            // initialize our search object:
            $request = $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
                + ['id' => $this->params()->fromRoute('id')];

            // Set up listener for recommendations:
            $rManager = $this->serviceLocator->get('VuFind\RecommendPluginManager');
            $setupCallback = function ($runner, $params, $searchId) use ($rManager) {
                $listener = new RecommendListener($rManager, $searchId);
                $listener->setConfig(
                    $params->getOptions()->getRecommendationSettings()
                );
                $listener->attach($runner->getEventManager()->getSharedManager());
            };

            $results = $runner->run($request, 'Subscriptions', $setupCallback);
            return $this->createViewModel(
                ['params' => $results->getParams(), 'results' => $results]
            );
        } catch (ListPermissionException $e) {
            if (!$this->getUser()) {
                return $this->forceLogin();
            }
            throw $e;
        }
    }

    public function performDeleteSubscription($id, $deleteSource) {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Load/check incoming parameters:
        if (empty($id)) {
            throw new \Exception('Cannot delete empty ID!');
        }

        $table = $this->getTable('Subscription');
        $table->unsubscribe($user->id, $id);
        return true;
    }

    public function performDeletePDASubscription($id, $deleteSource) {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Load/check incoming parameters:
        if (empty($id)) {
            throw new \Exception('Cannot delete empty ID!');
        }

        $table = $this->getTable('PDASubscription');
        $table->unsubscribe($user->id, $id);
        $notifier = $this->PDASubscriptions();
        $notifier->sendPDAUnsubscribeEmail($user, $id);
        $notifier->sendPDAUserUnsubscribeEmail($user, $id);
        return true;
    }

    protected function getProfileParams()
    {
        $params = [
            'ixtheo_title' => '', 'ixtheo_country' => '',
            'ixtheo_language' => '', 'ixtheo_appellation' => ''
        ];
        return array_merge(parent::getProfileParams(), $params);
    }

    public function profileAction()
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        $view = parent::profileAction();
        $view->request->ixtheo_language = $user->ixtheo_language ?: $this->layout()->userLang;
        return $view;
    }

    /**
     * Check whether given target would be an action in MyReasearch
     *
     * @return mixed
     */

    protected function isMyResearchTarget($target) {
        $targetBase = substr($target, 0, strrpos( $target, '/'));
        $myResearchHome = $this->getServerUrl('myresearch-home');
        $myResearchBase = substr($myResearchHome, 0, strrpos($myResearchHome, '/'));
        return $targetBase == $myResearchBase;
    }

    /**
     * Logout Action
     *
     * @return mixed
     */
    public function logoutAction() {
        $config = $this->getConfig();
        if (isset($config->Site->logOutRoute)) {
            $logoutTarget = $this->getServerUrl($config->Site->logOutRoute);
        } else {
            $logoutTarget = $this->getRequest()->getServer()->get('HTTP_REFERER');
            if (empty($logoutTarget)) {
                $logoutTarget = $this->getServerUrl('home');
            }

            // If there is an auth_method parameter in the query, we should strip
            // it out. Otherwise, the user may get stuck in an infinite loop of
            // logging out and getting logged back in when using environment-based
            // authentication methods like Shibboleth.
            $logoutTarget = preg_replace(
                '/([?&])auth_method=[^&]*&?/', '$1', $logoutTarget
            );
            $logoutTarget = rtrim($logoutTarget, '?');

            // Another special case: if logging out will send the user back to
            // the MyResearch home action, instead send them all the way to
            // VuFind home. Otherwise, they might get logged back in again,
            // which is confusing. Even in the best scenario, they'll just end
            // up on a login screen, which is not helpful.
            if ($logoutTarget == $this->getServerUrl('myresearch-home') || $this->isMyResearchTarget($logoutTarget)) {
                $logoutTarget = $this->getServerUrl('home');
            }
        }

        return $this->redirect()
            ->toUrl($this->getAuthManager()->logout($logoutTarget));
    }
}
