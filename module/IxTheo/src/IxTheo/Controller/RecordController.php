<?php

namespace IxTheo\Controller;

class RecordController extends \TueFind\Controller\RecordController
{
    function processSubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $results = $this->loadRecord()->subscribe($post, $user);

        if ($results == null)
            return $this->createViewModel();

        $this->flashMessenger()->addMessage('Success', 'success');
        return $this->redirectToRecord();
    }

    function processUnsubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $this->loadRecord()->unsubscribe($post, $user);

        $this->flashMessenger()->addMessage('Success', 'success');
        return $this->redirectToRecord();
    }

    function subscribeAction()
    {
        // Process form submission:
        if ($this->params()->fromPost('action') == 'subscribe') {
            return $this->processSubscribe();
        } else if ($this->params()->fromPost('action') == 'unsubscribe') {
            return $this->processUnsubscribe();
        }

        // Retrieve user object and force login if necessary:
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $driver = $this->loadRecord();
        $service = $driver->getDbService(\IxTheo\Db\Service\SubscriptionServiceInterface::class);
        $recordId = $driver->getUniqueId();

        $infoText = $this->forward()->dispatch('Content', [
            'action' => 'content',
            'page' => 'SubscriptionInfoText'
        ]);

        $subscribed = boolval($service->findExisting($user, $recordId));
        $bundles = [];
        foreach($driver->getBundleIds() as $bundle) {
            if (boolval($service->findExisting($user, $bundle))) {
                $bundles[] = $bundle;
            }
        }

        return $this->createViewModel(['subscribed' => $subscribed,
                                       'bundles' => $bundles,
                                       'infoText' => $infoText]);
    }

    function processPDASubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $data = [];
        $results = $this->loadRecord()->pdaSubscribe($post, $user, $data);
        if ($results == null) {
            return $this->createViewModel();
        }
        $id = $this->loadRecord()->getRecordID();
        $notifier = $this->PDASubscriptions();
        $notifier->sendPDANotificationEmail($post, $user, $data, $id);
        $notifier->sendPDAUserNotificationEmail($post, $user, $data, $id);
        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }

    function processPDAUnsubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $this->loadRecord()->pdaUnsubscribe($post, $user);
        $id = $this->loadRecord()->getRecordID();
        $notifier = $this->PDASubscriptions();
        $notifier->sendPDAUnsubscribeEmail($user, $id);
        $notifier->sendPDAUserUnsubscribeEmail($user, $id);
        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }

    function pdasubscribeAction()
    {
        // Process form submission:
        if ($this->params()->fromPost('action') == 'pdasubscribe') {
            return $this->processPDASubscribe();
        } else if ($this->params()->fromPost('action') == 'pdaunsubscribe') {
            return $this->processPDAUnsubscribe();
        }

        // Retrieve user object and force login if necessary:
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $driver = $this->loadRecord();
        $table = $driver->getDbService(\IxTheo\Db\Service\PDASubscriptionServiceInterface::class);
        $recordId = $driver->getUniqueId();
        $userId = $user->id;

        $infoText = $this->forward()->dispatch('Content', [
            'action' => 'content',
            'page' => 'PDASubscriptionInfoText'
        ]);
        $bookDescription = $driver->getAuthorsAsString() . ": " .
                           $driver->getTitle() .  ($driver->getYear() != "" ? "(" . $driver->getYear() . ")" : "") .
                           ", ISBN: " . $driver->getISBNs()[0];
        return $this->createViewModel(["pdasubscription" => !($table->findExisting($userId, $recordId)), "infoText" => $infoText,
                                       "bookDescription" => $bookDescription]);
    }
}
