<?php

namespace TueFind\Controller;

class RecordController extends \VuFind\Controller\RecordController {
    /**
     * Show redirect page if FallbackLoader was active
     *
     * @return mixed
     */
    public function homeAction()
    {
        $user = $this->getUser();
        $this->loadRecord();

        $view = parent::homeAction();
        $view->user = $user;
        return $view;
    }

    public function publishAction()
    {
        $user = $this->getUser();
        if (!$user)
            return $this->forceLogin();
        $this->loadRecord();

        $recordLanguages = $this->driver->tryMethod('getLanguages');
        $supportPublicationLanguages = false;
        if (in_array("German", $recordLanguages) || in_array("English", $recordLanguages)) {
            $supportPublicationLanguages = true;
        }

        return $this->createViewModel(['driver' => $this->driver, 'user' => $user, 'supportPublicationLanguages' => $supportPublicationLanguages]);
    }


        public function exportAction()
    {
        $driver = $this->loadRecord();
        $view = $this->createViewModel();
        $format = $this->params()->fromQuery('style');

        // Display export menu if missing/invalid option
        $export = $this->serviceLocator->get('VuFind\Export');
        if (empty($format) || !$export->recordSupportsFormat($driver, $format)) {
            if (!empty($format)) {
                $this->flashMessenger()
                    ->addMessage('export_invalid_format', 'error');
            }
            $view->setTemplate('record/export-menu');
            return $view;
        }

        // If this is an export format that redirects to an external site, perform
        // the redirect now (unless we're being called back from that service!):
        if ($export->needsRedirect($format)
            && !$this->params()->fromQuery('callback')
        )  {
            if ($export->useExportOutputAsParameter($format)) {
                $query_parameter = ($this->getViewRenderer()->plugin('record')($driver))->getExport($format);
                return $this->redirect()
                 ->toUrl($export->getRedirectUrl($format, $query_parameter));
            }
            else {
             // Build callback URL:
             $parts = explode('?', $this->getServerUrl(true));
             $callback = $parts[0] . '?callback=1&style=' . urlencode($format);
             return $this->redirect()
                 ->toUrl($export->getRedirectUrl($format, $callback));
            }
        }

        // Send appropriate HTTP headers for requested format:
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders($export->getHeaders($format));

        // Actually export the record
        $recordHelper = $this->getViewRenderer()->plugin('record');
        $response->setContent($recordHelper($driver)->getExport($format));
        return $response;
    }


}
