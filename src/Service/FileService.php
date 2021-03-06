<?php

namespace App\Service;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service class for file manager
 * @author Tristan
 * @version 1
 */
class FileService
{
    private string $targetDirectory;
    private SluggerInterface $slugger;
    private Filesystem $fileSystem;
    


    public function __construct(Filesystem $fileSystem, string $targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->fileSystem = $fileSystem;
    }

    /**
     * upload file from
     * @param UploadedFile $file uploaded file
     * @param String $folder folder destination path
     * @param String $newFileName fole new name
     * @return string file path
     */
    public function upload(UploadedFile $file, String $folder, String $newFileName = null) : string
    {
        if (empty($newFileName)) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename.'.'.$file->guessExtension();
        } else {
            $fileName = $newFileName.'.'.$file->guessExtension();
        }
        $filePath = $this->getTargetDirectory() . $folder;

        try {
            $file->move($filePath, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $folder . $fileName;
    }

    /**
     * remove a file
     * @param String $filePath file path
     * @return bool result
     */
    public function remove(String $filePath) : bool
    {
        error_log($filePath);
        if ($filePath != "") {
            try {
                $this->fileSystem->remove($this->getTargetDirectory() . $filePath);
            } catch (IOException $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getTargetDirectory() : string
    {
        return $this->targetDirectory;
    }
}
