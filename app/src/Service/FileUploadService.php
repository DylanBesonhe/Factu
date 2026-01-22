<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $projectDir
    ) {
    }

    /**
     * Upload un fichier dans le repertoire specifie
     *
     * @param UploadedFile $file Le fichier a uploader
     * @param string $directory Le sous-repertoire de storage (ex: 'cgv', 'contrats')
     * @return array{filename: string, originalName: string, mimeType: string|null, size: int|null}
     * @throws FileException Si l'upload echoue
     */
    public function upload(UploadedFile $file, string $directory): array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->getTargetDirectory($directory);

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $file->move($targetDirectory, $newFilename);

        return [
            'filename' => $newFilename,
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Supprime un fichier
     *
     * @param string $filename Le nom du fichier
     * @param string $directory Le sous-repertoire de storage
     * @return bool True si le fichier a ete supprime, false sinon
     */
    public function delete(string $filename, string $directory): bool
    {
        $filePath = $this->getTargetDirectory($directory) . '/' . $filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Verifie si un fichier existe
     *
     * @param string $filename Le nom du fichier
     * @param string $directory Le sous-repertoire de storage
     * @return bool
     */
    public function exists(string $filename, string $directory): bool
    {
        $filePath = $this->getTargetDirectory($directory) . '/' . $filename;
        return file_exists($filePath);
    }

    /**
     * Retourne le chemin complet d'un fichier
     *
     * @param string $filename Le nom du fichier
     * @param string $directory Le sous-repertoire de storage
     * @return string
     */
    public function getFilePath(string $filename, string $directory): string
    {
        return $this->getTargetDirectory($directory) . '/' . $filename;
    }

    /**
     * Retourne le repertoire cible pour un sous-repertoire donne
     *
     * @param string $directory Le sous-repertoire de storage
     * @return string
     */
    private function getTargetDirectory(string $directory): string
    {
        return $this->projectDir . '/storage/' . $directory;
    }
}
