<?php

namespace Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Exception;

class GoogleDriveService {
    private $client;
    private $service;
    private $parentId;

    private const MODULE_FOLDERS = [
        'repository'    => 'Repository',
        'announcements' => 'Announcements',
        'memorandums'   => 'Memorandums'
    ];

    public function __construct() {
        $this->parentId = GOOGLE_DRIVE_PARENT_ID;
        
        $userModel = new \User();
        $refreshToken = $userModel->getMasterRefreshToken();

        if (empty($refreshToken)) {
            return;
        }

        try {
            $this->client = new Client();
            $this->client->setClientId(GOOGLE_CLIENT_ID);
            $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
            $this->client->setAccessType('offline');
            $this->client->addScope(Drive::DRIVE_FILE);
            
            // Set the refresh token
            $this->client->refreshToken($refreshToken);
            
            $this->service = new Drive($this->client);
        } catch (Exception $e) {
            error_log("Google Drive OAuth Init Error: " . $e->getMessage());
        }
    }

    /**
     * Check if the service is fully authenticated and ready for use.
     */
    public function isReady(): bool {
        return ($this->service !== null);
    }

    /**
     * Upload a file and sort it into a module-specific subfolder.
     */
    public function uploadAndSort(string $filePath, string $originalName, string $category, string $module = 'repository'): array {
        if (!$this->service) {
            throw new Exception("Google Drive Service not initialized.");
        }

        // 1. Resolve Module and Target Folder
        $moduleFolderName = self::MODULE_FOLDERS[$module] ?? 'Repository';
        $moduleFolderId = $this->ensureFolderExists($moduleFolderName, $this->parentId);
        
        $targetFolderId = ($module === 'repository') 
            ? $this->ensureFolderExists($category, $moduleFolderId)
            : $moduleFolderId;

        // 2. Upload the file
        $fileMetadata = new DriveFile([
            'name' => $originalName,
            'parents' => [$targetFolderId]
        ]);

        $content = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath);

        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink'
        ]);

        return [
            'id'   => $file->id,
            'link' => $file->webViewLink
        ];
    }

    /**
     * Scan a specific module folder and its subfolders.
     */
    public function syncFolders(string $module = 'repository'): array {
        if (!$this->service) return [];

        $allFiles = [];
        $moduleFolderName = self::MODULE_FOLDERS[$module] ?? 'Repository';
        $moduleFolderId = $this->ensureFolderExists($moduleFolderName, $this->parentId);

        if ($module === 'repository') {
            $categories = ['Department', 'Faculty', 'Student'];
            foreach ($categories as $cat) {
                $catFolderId = $this->ensureFolderExists($cat, $moduleFolderId);
                $subFiles = $this->listFiles($catFolderId);
                foreach ($subFiles as $f) {
                    $allFiles[] = array_merge($f, ['category' => $cat]);
                }
            }
        } else {
            // For Announcements and Memorandums, scan the module folder directly
            $files = $this->listFiles($moduleFolderId);
            foreach ($files as $f) {
                // Determine a sensible category/type label
                $catLabel = ($module === 'announcements') ? 'Announcement' : 'Memorandum';
                $allFiles[] = array_merge($f, ['category' => $catLabel]);
            }
        }

        return $allFiles;
    }

    private function listFiles(string $folderId): array {
        $query = "'$folderId' in parents and mimeType != 'application/vnd.google-apps.folder' and trashed = false";
        $results = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name, webViewLink, mimeType, size)'
        ]);

        $files = [];
        foreach ($results->getFiles() as $f) {
            $files[] = [
                'drive_file_id' => $f->id,
                'title'         => $f->name,
                'drive_link'    => $f->webViewLink,
                'file_type'     => $f->mimeType,
                'file_size'     => $f->size ?? 0
            ];
        }
        return $files;
    }

    private function findFolderId(string $folderName): ?string {
        $query = "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and '$this->parentId' in parents and trashed = false";
        $results = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id)'
        ]);
        $files = $results->getFiles();
        return count($files) > 0 ? $files[0]->id : null;
    }

    /**
     * Delete a file from Google Drive.
     */
    public function deleteFile(string $driveFileId): bool {
        if (!$this->service) return false;
        try {
            $this->service->files->delete($driveFileId);
            return true;
        } catch (Exception $e) {
            error_log("Google Drive Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure a subfolder exists within a specific parent folder.
     */
    private function ensureFolderExists(string $folderName, ?string $targetParentId = null): string {
        $parentId = $targetParentId ?: $this->parentId;
        
        $query = "name = '$folderName' and mimeType = 'application/vnd.google-apps.folder' and '$parentId' in parents and trashed = false";
        $results = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id)'
        ]);
        $files = $results->getFiles();

        if (count($files) > 0) {
            return $files[0]->id;
        }

        // Create the folder if it doesn't exist
        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId]
        ]);

        $folder = $this->service->files->create($folderMetadata, [
            'fields' => 'id'
        ]);

        return $folder->id;
    }
}
