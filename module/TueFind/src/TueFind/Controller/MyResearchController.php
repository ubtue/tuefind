<?php

namespace TueFind\Controller;

class MyResearchController extends \VuFind\Controller\MyResearchController
{
    protected function getUserAuthoritiesAndRecords($user, $onlyGranted=false, $exceptionIfEmpty=false): array
    {
        $table = $this->getDbService(\TueFind\Db\Service\UserAuthorityServiceInterface::class);

        $accessState = $onlyGranted ? 'granted' : null;
        $userAuthorities = $table->getByUserId($user->getId(), $accessState);

        if ($exceptionIfEmpty && count($userAuthorities) == 0) {
            throw new \Exception('No authority linked to this user!');
        }

        $authorityRecords = [];
        foreach ($userAuthorities as $userAuthority) {
            $authorityRecords[$userAuthority->getAuthorityControlNumber()] = $this->getRecordLoader()
                ->load($userAuthority->getAuthorityControlNumber(), 'SolrAuth');
        }

        return ['userAuthorities' => $userAuthorities, 'authorityRecords' => $authorityRecords];
    }

    /**
     * This is a separate function so we can override it easier in extending classes.
     * It returns an array with the name of the params in the form
     * (should be named similar to the corresponding columns in the user table.)
     */
    protected function getProfileParams()
    {
        return [
            'firstname' => ['default' => '', 'getter' => 'getFirstname', 'setter' => 'setFirstname'],
            'lastname' => ['default' => '', 'getter' => 'getLastname', 'setter' => 'setLastname'],
            'tuefind_institution' => ['default' => '', 'getter' => 'getInstitution', 'setter' => 'setInstitution'],
            'tuefind_country' => ['default' => '', 'getter' => 'getCountry', 'setter' => 'setCountry'],
        ];
    }

    /**
     * VuFind usually does not allow to edit your own profile directly.
     * They just provide additional buttons to change certain single properties,
     * like e.g. the email address (and trigger verification workflow).
     *
     * However, we want the user to be able to edit multiple fields here
     * and also to add some additional non-vufind-fields.
     */
    public function profileAction()
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        $profileParams = $this->getProfileParams();
        if ($this->getRequest()->getPost("submit")) {
            // email may no longer be updated here, the separate action (+button) should be used
            // so that the verify_email functionality actually has an effect.
            $request = $this->getRequest();

            foreach ($profileParams as $param => $paramSettings) {
                $user->{$paramSettings['setter']}($request->getPost()->get($param, $paramSettings['default']));
            }
            $user->save();
            $this->getAuthManager()->updateSession($user);
        }

        $view = parent::profileAction();
        $post = $this->getRequest()->getPost();
        foreach ($profileParams as $param => $paramSettings) {
            if (!$post->$param) {
                $post->$param = $user->{$paramSettings['getter']}();
            }
        }
        $view->request = $post;

        $view->passwordPolicy = $this->getAuthManager()
            ->getPasswordPolicy();

        return $view;
    }

    public function publicationsAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $config = $this->getConfig('tuefind');
        $dspaceServer = $config->Publication->dspace_url_base;
        $dspaceVersion = $config->Publication->dspace_version;

        $authorityUsers = $this->getDbService(\TueFind\Db\Service\UserAuthorityServiceInterface::class)->getByUserId($user);
        $authorityUsersArray = [];
        foreach($authorityUsers as $authorityUser) {
            $authorityUserLoader = $this->serviceLocator->get(\VuFind\Record\Loader::class)->load($authorityUser->getAuthorityControlNumber(), 'SolrAuth');
            $authorityUsersArray[] = [
                'id'=>$authorityUser->getAuthorityControlNumber(),
                'access_state'=>$authorityUser->getAccessState(),
                'title'=>$authorityUserLoader->getTitle()
            ];
        }
        $publications = [];
        $dbPublications = $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->getByUserId($user->getId());
        foreach ($dbPublications as $dbPublication) {
            $existingRecord = $this->getRecordLoader()->load($dbPublication->getControlNumber(), 'Solr', /*tolerate_missing=*/true);
            $publication = ['db' => $dbPublication, 'record' => $existingRecord];
            $publications[] = $publication;
        }

        $viewParams = $this->getUserAuthoritiesAndRecords($user, /* $onlyGranted = */ true);
        $viewParams['publications'] = $publications;
        $viewParams['dspaceServer'] = $dspaceServer;
        $viewParams['dspaceVersion'] = $dspaceVersion;
        $viewParams['authorityUsers'] = $authorityUsersArray;
        return $this->createViewModel($viewParams);
    }

    public function selfarchivingAction() {

        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        return $this->forward()->dispatch('Content', [
            'action' => 'content',
            'page' => 'SelfArchivingGuide'
        ]);

    }

    public function publishAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        // This is related to DSpace in the first place, not to our own server.
        // The value will be shown as MB in certain display texts, as well as compared
        // with the uploaded file's size. Be careful increasing this value, since
        // our own php.ini must also allow the value.
        $uploadMaxFileSizeMB = 50;
        $showForm = true;
        $config = $this->getConfig('tuefind');
        $dspaceServer = $config->Publication->dspace_url_base;
        $dspaceVersion = $config->Publication->dspace_version;

        // 1) Get metadata to show form
        $existingRecordId = $this->params()->fromRoute('record_id', null);
        if (empty($existingRecordId)) {
            throw new \Exception('record_id is empty!');
        }
        $existingRecord = $this->getRecordLoader()->load($existingRecordId);

        $dbPublications = $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->getByControlNumber($existingRecordId);
        if (!empty($dbPublications->external_document_id)) {

            $publicationURL = ($dspaceVersion == 6) ? $dspaceServer."/xmlui/handle/".$dbPublications->external_document_id : $dspaceServer."/handle/".$item->handle;

            $this->flashMessenger()->addMessage(['msg' => $this->translate('publication_already_exists').": <a href='".$publicationURL."' target='_blank'>".$this->translate('click_here_to_go_to_file')."</a>", 'html' => true], 'error');
            $uploadError = true;
            $showForm = false;
        }

        $termFileData = $this->getLatestTermFile();
        $action = $this->params()->fromPost('action');

        // 2) Process upload action (if form was submitted)
        if ($action == 'publish') {
            // Check uploaded file (+ do some preparations)
            $uploadError = false;
            $uploadedFile = $this->params()->fromFiles('file');
            $PDFMediaTypesArray = ['application/pdf', 'application/x-pdf', 'application/x-bzpdf', 'application-gzpdf'];
            if (!in_array($uploadedFile['type'], $PDFMediaTypesArray)) {
                $this->flashMessenger()->addMessage($this->translate('only_PDF_files_allowed'), 'error');
                $uploadError = true;
            }
            if ($uploadedFile['size'] > $uploadMaxFileSizeMB * 1024 * 1024) {
                $this->flashMessenger()->addMessage($this->translate('file_is_too_big'), 'error');
                $uploadError = true;
            }
            if (!preg_match('/^[-a-z0-9_\s]+\.pdf$/i', $uploadedFile['name'])) {
                $this->flashMessenger()->addMessage($this->translate('publication_PDF_title_validation'), 'error');
                $uploadError = true;
            }

            if (!$uploadError) {
                $tmpdir = sys_get_temp_dir() . '/' . uniqid('publication_');
                if (!is_dir($tmpdir))
                    mkdir($tmpdir);
                $tmpfile = $tmpdir . '/' . $uploadedFile['name'];

                if (is_file($tmpfile)) {
                    unlink($tmpfile);
                }
                if (!move_uploaded_file($uploadedFile['tmp_name'], $tmpfile)) {
                    throw new \Exception('Uploaded file could not be moved to tmp directory!');
                }

                if($dspaceVersion == 6) {
                    $dspace = $this->serviceLocator->get(\TueFind\Service\DSpace6::class);
                    $dspace->login();
                    $collectionName = $config->Publication->collection_name;
                    $collection = $dspace->getCollectionByName($collectionName);
                    $dspaceMetadata = $this->serviceLocator->get(\VuFind\MetadataVocabulary\PluginManager::class)->get('DSpace6')->getMappedData($existingRecord);
                    $item = $dspace->addItem($collection->uuid, $dspaceMetadata);
                    $bitstream = $dspace->addBitstream($item->uuid, basename($tmpfile), $tmpfile);
                    // Store information in database
                    $dbPublications = $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->addPublication($user->getId(), $existingRecordId, $item->handle, $item->uuid, $termFileData['termDate']);
                    $publicationURL = $dspaceServer."/xmlui/handle/".$item->handle;
                }else{
                    $dspace = $this->serviceLocator->get(\TueFind\Service\DSpace7::class);
                    $dspace->login();
                    $configCollectionName = $config->Publication->collection_name;
                    $configCollectionName = 'UOJ 12'; //test collection name from DEMO
                    $collection = $dspace->getCollectionByName($configCollectionName);
                    $dspaceMetadata = $this->serviceLocator->get(\VuFind\MetadataVocabulary\PluginManager::class)->get('DSpace7')->getMappedData($existingRecord);
                    $item = $dspace->addWorkspaceItem($tmpfile,$collection->uuid);
                    //$workflowItem = $dspace->addWorkflowItem($item->id); // not work
                    $updateData = $dspace->updateWorkspaceItem($item->id,$dspaceMetadata);
                    // Store information in database
                    $dbPublications = $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->addPublication($user->getId(), $existingRecordId, $item->id, $item->sections->upload->files[0]->uuid, $termFileData['termDate']);
                    $publicationURL = $dspaceServer."/workspaceitems/".$item->id."/view";
                }
                $this->flashMessenger()->addMessage(['msg' => $this->translate('publication_successfully_created').": <a href='".$publicationURL."' target='_blank'>".$this->translate('click_here_to_go_to_file')."</a>", 'html' => true], 'success');
                $showForm = false;
            }
        }

        // 3) Generate view
        $view = $this->createViewModel($this->getUserAuthoritiesAndRecords($user, /* $onlyGranted = */ true, /* $exceptionIfEmpty = */ true));
        $dublinCore = $this->serviceLocator->get(\VuFind\MetadataVocabulary\PluginManager::class)->get('DublinCore')->getMappedData($existingRecord);
        $userAuthorities = [];
        foreach ($view->userAuthorities as $userAuthority) {
            $selected = false;
            $authorityRecord = $view->authorityRecords[$userAuthority['authority_id']];
            $GNDNumber = $authorityRecord->getGNDNumber();
            $authorityTitle = htmlspecialchars($authorityRecord->getTitle());
            foreach ($dublinCore['DC.creator'] as $creator) {
                if ($authorityTitle == $creator) {
                    $selected = true;
                }
            }
            $userAuthorities[] = [
                'authority_id' => $userAuthority['authority_id'],
                'authority_title' => $authorityTitle,
                'authority_GNDNumber' => $GNDNumber,
                'select_title' => $authorityTitle . ' (GND: ' .  $GNDNumber . ')',
                'selected' => $selected
            ];
        }

        $view->showForm = $showForm;
        $view->userAuthorities = $userAuthorities;
        $view->existingRecord = $existingRecord;
        $view->dublinCore = $dublinCore;
        $view->termFile = $termFileData;
        $view->recordLanguages = $existingRecord->getLanguages();
        $view->uploadMaxFilesizeMB = $uploadMaxFileSizeMB;
        return $view;
    }

    public function rssFeedSettingsAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $dbTablePluginManager = $this->serviceLocator->get(\VuFind\Db\Service\PluginManager::class);
        $rssSubscriptionsTable = $dbTablePluginManager->get(\TueFind\Db\Service\RssSubscriptionServiceInterface::class);
        $rssFeedsTable = $dbTablePluginManager->get(\TueFind\Db\Service\RssFeedServiceInterface::class);
        $action = $this->getRequest()->getPost('action', '');
        $feedId = $this->getRequest()->getPost('id', '');
        if ($action == 'add') {
            $rssSubscriptionsTable->addSubscription($user->getId(), $feedId);
        } elseif ($action == 'remove') {
            $rssSubscriptionsTable->removeSubscription($user->getId(), $feedId);
        } elseif ($action == 'subscribe_email') {
            $user->setRssFeedSendEmails(true);
        } elseif ($action == 'unsubscribe_email') {
            $user->setRssFeedSendEmails(false);
        }

        return $this->createViewModel(['rssFeeds' => $rssFeedsTable->getFeedsSortedByName(),
                                       'rssSubscriptions' => $rssSubscriptionsTable->getSubscriptionsForUserSortedByName($user->getId()),
                                       'user' => $user]);
    }

    public function rssFeedPreviewAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $rssTable = $this->serviceLocator->get(\VuFind\Db\Table\PluginManager::class)->get('rss_item');
        $rssItems = $rssTable->getItemsForUserSortedByPubDate($user->getId());
        return $this->createViewModel(['user' => $user,
                                       'rssItems' => $rssItems,
                                       'page' => $this->params()->fromQuery('page') ?? 1]);
    }

    /**
     * This method can be used to access a user's personal RSS feed without a login,
     * for use in e.g. a RSS reader. Instead of using the user_id, we rather use the uuid
     * for privacy reasons:
     * - The user_id might be shown to other users in hyperlinks (e.g. if tags are enabled)
     * - The user_id might be guessed more easily by a brute force attack
     */
    public function rssFeedRawAction()
    {
        $userUuid = $this->params()->fromRoute('user_uuid');
        $user = $this->serviceLocator->get(\VuFind\Db\Table\PluginManager::class)->get('user')->getByUuid($userUuid);
        $instance = $this->serviceLocator->get('ViewHelperManager')->get('tuefind')->getTueFindInstance();
        $cmd = '/usr/local/bin/rss_subset_aggregator --mode=rss_xml ' . escapeshellarg($user->getId()) . ' ' . escapeshellarg($instance);

        // We need to explicitly pass through VUFIND_HOME, or database.conf cannot be found
        putenv('VUFIND_HOME=' . getenv('VUFIND_HOME'));
        exec($cmd, $rssFeedContentArray, $return_var);
        $rssFeedContentString = implode('', $rssFeedContentArray);

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-type', 'text/xml');
        $response->setContent($rssFeedContentString);
        return $response;
    }

    private function getLatestTermFile(): array
    {
        $termsDir =  $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/publication_terms/';
        $files = scandir($termsDir);
        $latestTermData = [];
        foreach ($files as $file) {
            if (preg_match('/(\d{4})(\d{2})(\d{2})/', $file, $matches)) {
                $formatedDate = $matches[1] . "-" . $matches[2] . "-" . $matches[3];
                $timeStamp = strtotime($formatedDate);
                $latestTermData[] = [
                    "milliseconds"=>$timeStamp,
                    "termDate"=>$formatedDate,
                    "fileName"=>$file
                ];
            }
        }
        if (empty($latestTermData)) {
            throw new \Exception('Latest term file not found in: ' . $termsDir);
        }
        usort($latestTermData, function($a, $b){
            return ($b['milliseconds'] - $a['milliseconds']);
        });
        return $latestTermData[0];
    }
}
