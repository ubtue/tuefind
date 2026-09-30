<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\View\Renderer\PhpRenderer;
use VuFind\Config\PluginManager as ConfigManager;
use VuFind\Db\Entity\UserEntityInterface;

use function in_array;

class CmsDocsEntries extends \VuFind\AjaxHandler\AbstractBase
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     * @var PhpRenderer
     */
    protected $viewRenderer;

    protected $searchResultsManager;

    protected $configManager;

    protected $allowedBase;

    public function __construct(
        \VuFind\Search\Results\PluginManager $searchResultsManager,
        PhpRenderer $viewRenderer,
        ConfigManager $configManager,
        protected ?UserEntityInterface $user = null
    ) {
        $this->searchResultsManager = $searchResultsManager;
        $this->viewRenderer = $viewRenderer;
        $this->configManager = $configManager;
        $this->allowedBase = $this->configManager->get('tuefind')->CMS->repository_path;
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
                'data' => $this->translate('You must be logged in first'),
            ], self::STATUS_HTTP_NEED_AUTH);
        }

        $resultReturn = [];

        $action = $params->fromQuery('action');
        if ($action === 'createFolder') {
            $baseDir = $params->fromQuery('parentPath');
            $folderName = trim($params->fromQuery('folderName') ?? '');

            // 1. check if folder name is empty
            if (empty($folderName)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Folder name is required',
                ]);
            }

            // 2. Strict validation (spaces and special characters are rejected by a single regex)
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $folderName) || str_contains($folderName, ' ')) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid folder name. Use only letters, numbers, dashes, underscores, and dots (no spaces).',
                ]);
            }

            // 3. Protection from Path Traversal (prohibition of "." and "..")
            if ($folderName === '.' || $folderName === '..') {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid folder name.',
                ]);
            }

            // 4. Correct path concatenation (directory separator)
            $baseDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR;
            $targetPath = $baseDir . $folderName;

            // 5. Check access and existence
            if (!is_writable($baseDir)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Base directory is not writable',
                ]);
            }

            if (file_exists($targetPath)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Folder already exists',
                ]);
            }

            if (!mkdir($targetPath, 0o755, true)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Failed to create folder',
                ]);
            }

            return $this->formatResponse([
                'status' => 'success',
                'message' => 'Folder created successfully',
                'folderPath' => $targetPath,
            ]);
        }

        if ($action === 'deleteImage') {
            $file = $this->allowedBase . $params->fromQuery('full-path');

            $resultReturn = [
                'status' => 'success',
                'data' => 'File deleted successfully',
            ];

            if (file_exists($file)) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    return $this->formatResponse([
                        'status' => 'ERROR',
                        'data' => 'Invalid file type',
                    ]);
                }

                unlink($file);
            } else {
                $resultReturn = [
                    'status' => 'ERROR',
                    'data' => 'File not found: ' . $file,
                ];
            }
            return $this->formatResponse($resultReturn);
        }

        if ($action === 'deleteFile') {
            $fullPath = $this->allowedBase . $params->fromQuery('full-path');

            if (file_exists($fullPath)) {
                // MIME + extenxion check
                $allowedMime = [
                    'application/pdf',
                    'text/plain',
                ];

                $allowedExt = ['pdf', 'txt'];

                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

                if (!in_array(mime_content_type($fullPath), $allowedMime) || !in_array($ext, $allowedExt)) {
                    return $this->formatResponse([
                        'status' => 'ERROR',
                        'message' => 'Invalid file type',
                    ]);
                }

                unlink($fullPath);

                $resultReturn = [
                    'status' => 'success',
                    'data' => 'File deleted successfully',
                ];
            } else {
                $resultReturn = [
                    'status' => 'ERROR',
                    'data' => 'File not found: ' . $fullPath,
                ];
                return $this->formatResponse($resultReturn);
            }
        }

        if ($action === 'uploadFiles') {
            if (empty($_FILES['file'])) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'No file uploaded',
                ]);
            }

            $file = $_FILES['file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                    UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk (check permissions or disk space)',
                    UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
                ];

                $errorCode = $file['error'];
                $message = $errorMessages[$errorCode] ?? 'Unknown upload error: ' . $errorCode;

                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => $message,
                    'code' => $errorCode,
                ]);
            }

            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
                'text/plain',
            ];

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'];

            // get the original file name without extension
            $originalName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME); // name without extension
            // get the file extension in lowercase
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            $mimeType = mime_content_type($file['tmp_name']);

            if (!in_array($mimeType, $allowedMimeTypes) || !in_array($ext, $allowedExtensions)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid file type',
                ]);
            }

            $allowedBase = $this->allowedBase;

            $theme = $params->fromQuery('theme');

            $theme = trim($theme, '/');
            if (empty($theme) || str_contains($theme, '..')) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid theme path',
                ]);
            }

            $targetDir = $allowedBase . '/' . $theme;

            $realTargetDir = realpath($targetDir);
            $realBaseDir = realpath($allowedBase);

            if ($realTargetDir === false || !str_starts_with($realTargetDir, $realBaseDir)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Invalid or non-existent path: ' . $targetDir,
                ]);
            }

            if (!is_writable($realTargetDir)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Folder is not writable: ' . $theme,
                ]);
            }

            // replace spaces with underscores
            $cleanName = str_replace(' ', '_', $originalName);

            // form the new file name
            $newName = $cleanName . '.' . $ext;

            $targetPath = $realTargetDir . '/' . $newName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $this->formatResponse([
                    'status' => 'ERROR',
                    'message' => 'Failed to save file',
                ]);
            }

            return $this->formatResponse([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'filePath' => $targetPath,
            ]);
        }

        if ($action === 'getThemeURLs') {
            $allowedBase = $this->allowedBase;

            $formattedPaths = [];
            $block = 'AJAXCMSDocsBlock';
            if (!empty($params->fromQuery('block'))) {
                $block = $params->fromQuery('block');
            }
            $modetype = '';
            if (!empty($params->fromQuery('modetype'))) {
                $modetype = $params->fromQuery('modetype');
            }

            if (is_dir($allowedBase)) {
                $folders = [];

                if (!empty($allowedBase) && is_dir($allowedBase)) {
                    $dirPath = rtrim($allowedBase, '/') . '/';

                    $dirContent = scandir($dirPath);

                    foreach ($dirContent as $item) {
                        if (str_starts_with($item, '.') || str_starts_with($item, '_')) {
                            continue;
                        }

                        $itemFullPath = $dirPath . $item;

                        if (is_dir($itemFullPath)) {
                            $folders[] = [
                                'name' => $item,
                                'fullPath' => $itemFullPath,
                            ];
                        }
                    }
                }

                $viewParams = [
                    'path' => '',
                    'fullPath' => $allowedBase,
                    'block' => $block,
                    'modetype' => $modetype,
                    'folders' => $folders,
                    'files' => [],
                    'serverPath' => $allowedBase,
                ];

                $htmlContent = $this->viewRenderer->render('adminfrontend/ajax/allcmsfiles', $viewParams);

                return $this->formatResponse($htmlContent);
            } else {
                $formattedPaths[] = "<span class='btn btn-danger m-1' style='pointer-events: none;'>No themes directory found</span>";
            }

            return $this->formatResponse($formattedPaths);
        }

        if ($action === 'getThemeContent') {
            $serverPath = $params->fromQuery('server-path');
            $path = $params->fromQuery('path');
            $fullPath = $params->fromQuery('full-path');
            $block = $params->fromQuery('block');
            $modetype = $params->fromQuery('modetype');

            if (null === $this->viewRenderer) {
                return $this->formatResponse('Error: Renderer is null inside handleRequest', 500);
            }

            $folders = [];
            $files = [];

            $allowedExtensions = ['pdf', 'txt', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'doc', 'docx', 'xls', 'xlsx'];

            if (!empty($fullPath) && is_dir($fullPath)) {
                $dirPath = rtrim($fullPath, '/') . '/';

                $dirContent = scandir($dirPath);

                foreach ($dirContent as $item) {
                    if (str_starts_with($item, '.') || str_starts_with($item, '_')) {
                        continue;
                    }

                    $itemFullPath = $dirPath . $item;

                    if (is_dir($itemFullPath)) {
                        $folders[] = [
                            'name' => $item,
                            'fullPath' => $itemFullPath,
                        ];
                    } else {
                        $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));

                        $cleanFullPath = str_replace('//', '/', $itemFullPath);

                        if (in_array($extension, $allowedExtensions)) {
                            $files[] = [
                                'name' => $item,
                                'extension' => $extension,
                                'size' => filesize($itemFullPath),
                                'fullPath' => $cleanFullPath,
                                'serverPath' => $serverPath,
                                'relativePath' => str_replace($serverPath, '', $cleanFullPath),
                            ];
                        }
                    }
                }
            }

            $viewParams = [
                'path' => $path,
                'fullPath' => $fullPath,
                'block' => $block,
                'modetype' => $modetype,
                'folders' => $folders,
                'files' => $files,
                // TODO: The serverPath is known by configuration
                //       it does not make sense to put it to html and send it via AJAX to to the server again
                'serverPath' => $serverPath,
            ];

            $htmlContent = $this->viewRenderer->render('adminfrontend/ajax/allcmsfiles', $viewParams);

            return $this->formatResponse($htmlContent);
        }

        // deprecated, use /cms/assets/... endpoint with relative path instead
        if ($action === 'getImageContent') {
            $fullPath = $params->fromQuery('full-path');
            // Base security: check that the file really lies inside the allowed synchronization folder
            $fullPath = $this->allowedBase . $fullPath;

            if (empty($fullPath) || !str_starts_with(realpath($fullPath), $this->allowedBase) || !file_exists($fullPath)) {
                return $this->formatResponse('File not found or access denied', 404);
            }

            // Check MIME-type (example: image/png, image/jpeg)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fullPath);
            finfo_close($finfo);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));

            readfile($fullPath);
            exit;
        }

        // deprecated, use /cms/assets/... endpoint with relative path instead
        if ($action === 'getFileContent') {
            $fullPath = $params->fromQuery('full-path');

            if (empty($fullPath) || !file_exists($fullPath)) {
                return $this->formatResponse('File not found or access denied', 404);
            }

            $realPath = realpath($fullPath);

            if (!$this->allowedBase || !$realPath || !str_starts_with($realPath, $this->allowedBase . DIRECTORY_SEPARATOR)) {
                return $this->formatResponse('File not found or access denied', 403);
            }

            $allowedExtensions = ['pdf', 'txt'];
            $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                return $this->formatResponse('Invalid file type', 400);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $realPath);
            finfo_close($finfo);

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($realPath));

            header('Content-Disposition: inline; filename="' . basename($realPath) . '"');

            readfile($realPath);
            exit;
        }

        return $this->formatResponse($resultReturn);
    }
}
