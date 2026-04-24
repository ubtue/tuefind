<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;
use SebastianBergmann\Environment\Console;
use VuFind\Db\Entity\UserEntityInterface;

class CmsDocsEntries extends \VuFind\AjaxHandler\AbstractBase
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $searchResultsManager;

    public function __construct(\VuFind\Search\Results\PluginManager $searchResultsManager, protected ?UserEntityInterface $user) {
        $this->searchResultsManager = $searchResultsManager;
    }
    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {
        // check if user is logged in
        if (!$this->user) {
            return $this->formatResponse([
                'status' => 'ERROR',
                'data' => $this->translate('You must be logged in first')
            ], self::STATUS_HTTP_NEED_AUTH);
        }

        $resultReturn = [];

        if ($params->fromQuery('action') === 'listImages') {

            
            $baseDir = $_SERVER['REDIRECT_VUFIND_HOME'] . '/themes/krimdok2/images/';
            $baseUrl = '/themes/krimdok2/images/';

            $ajaxHTML = "";

            if (is_dir($baseDir)) {
                foreach (scandir($baseDir) as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $ajaxHTML .= '<div class="col-3">
                        <div class="card h-100 smc-card">
                            <div class="card-header">
                                ' . $file . '
                            </div>
                            <div class="card-body">
                                <img src="' . $baseUrl . $file . '" class="card-img-top" alt="' . $file . '">
                            </div>
                            <div class="card-footer text-muted row gx-0">
                                <a href="#" class="text-center d-block text-default cms_preview col-6" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . $baseUrl . $file . '" class="text-center d-block text-danger col-6 delete-btn" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>';
                }
            }

            return $this->formatResponse(
               $ajaxHTML
            );

        }

        if ($params->fromQuery('action') === 'listFiles') {

            $documentRoot = $_SERVER['DOCUMENT_ROOT']; // now for testing';
            $subSystemFileFolderName = '/krimdok_docs/';
            $baseDir = $documentRoot . $subSystemFileFolderName;

            $files = [];

            if (is_dir($baseDir)) {
                foreach (scandir($baseDir) as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    $files[] = [
                        'name' => $file,
                        'url' => $baseDir . $file
                    ];
                }
            }

            return $this->formatResponse(
               $files
            );

        }

        if ($params->fromQuery('action') === 'deleteImage') {

            $baseDir = $_SERVER['REDIRECT_VUFIND_HOME'] . '/themes/krimdok2/images/';
            $file = basename($params->fromQuery('failPATH'));
            $failPath = $baseDir . $file;

            if (file_exists($failPath)) {

                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    return $this->formatResponse([
                        'status' => 'ERROR',
                        'data' => 'Invalid file type'
                    ]);
                }

                unlink($failPath);

                $resultReturn = [
                    'status' => 'success',
                    'data' => 'File deleted successfully'
                ];
            } else {
                $resultReturn = [
                    'status' => 'ERROR',
                    'data' => 'File not found: ' . $failPath
                ];
                return $this->formatResponse($resultReturn);
            }
        }

        if ($params->fromQuery('action') === 'deleteFile') {

            //$baseDir = $_SERVER['REDIRECT_VUFIND_HOME'] . '/themes/krimdok2/images/';
            $failPath = $params->fromQuery('failPATH');

            if (file_exists($failPath)) {
                
                // MIME + extenxion check
                $allowedMime = [
                    'application/pdf',
                    'text/plain'
                ];

                $allowedExt = ['pdf', 'txt'];

                $ext = strtolower(pathinfo($failPath, PATHINFO_EXTENSION)); 

                if (!in_array(mime_content_type($failPath), $allowedMime) || !in_array($ext, $allowedExt)) {
                    return $this->formatResponse([
                        'status' => 'ERROR',
                        'message' => 'Invalid file type'
                    ]);
                }
                
                unlink($failPath);
                
                $resultReturn = [
                    'status' => 'success',
                    'data' => 'File deleted successfully'
                ];
            } else {
                $resultReturn = [
                    'status' => 'ERROR',
                    'data' => 'File not found: ' . $failPath
                ];
                return $this->formatResponse($resultReturn);
            }
        }

        if ($params->fromQuery('action') === 'uploadImage') {

            if (empty($_FILES['file'])) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'No file uploaded'
                ]);
            }

            $file = $_FILES['file'];

            // check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Upload error'
                ]);
            }

            // check file type
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($file['type'], $allowed)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid file type'
                ]);
            }

            // folder where the images will be stored
            $baseDir = $_SERVER['REDIRECT_VUFIND_HOME'] . '/themes/krimdok2/images/';

            // nunique name
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = uniqid('img_', true) . '.' . $ext;

            $targetPath = $baseDir . $newName;

            if(!is_writable(dirname($targetPath))) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'folder not writable: ' . dirname($targetPath)
                ]);
            }

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Failed to save file'
                ]);
            }

            $resultReturn = [
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'filePath' => '/themes/krimdok2/images/' . $newName
             
            ];
        }
        if ($params->fromQuery('action') === 'uploadDocument') {

            if (empty($_FILES['file'])) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'No file uploaded'
                ]);
            }

            $file = $_FILES['file'];

            // check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Upload error'
                ]);
            }

            // MIME + extenxion check
            $allowedMime = [
                'application/pdf',
                'text/plain'
            ];

            $allowedExt = ['pdf', 'txt'];

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file['type'], $allowedMime) || !in_array($ext, $allowedExt)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid file type'
                ]);
            }

            //$baseDir = $_SERVER['REDIRECT_VUFIND_HOME'] . '/themes/krimdok2/docs/';
            $documentRoot = $_SERVER['DOCUMENT_ROOT'];      
            $subSystemFileFolderName = '/krimdok_docs/';
            $baseDir = $documentRoot . $subSystemFileFolderName;

            if (!is_dir($baseDir)) {
                //mkdir($baseDir, 0775, true); // create directory if it doesn't exist
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'dir not found: ' . $baseDir
                ]);
            }

            // Unique name to prevent overwriting existing files
            $newName = uniqid('doc_', true) . '.' . $ext;

            $targetPath = $baseDir . $newName;

            if (!is_writable(dirname($targetPath))) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Folder not writable'
                ]);
            }

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Failed to save file'
                ]);
            }

            return $this->formatResponse([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'filePath' => '/themes/krimdok2/docs/' . $newName
            ]);
        }
        return $this->formatResponse($resultReturn);
    }
}
