<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    /**
     * Extensions autorisees par type de repertoire
     */
    private const ALLOWED_EXTENSIONS = [
        'cgv' => ['pdf'],
        'contrats' => ['pdf', 'doc', 'docx'],
        'default' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'],
    ];

    /**
     * Types MIME autorises par extension
     */
    private const ALLOWED_MIME_TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
    ];

    /**
     * Taille maximale en octets (10 Mo)
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

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
     * @throws FileException Si l'upload echoue ou si le fichier n'est pas autorise
     */
    public function upload(UploadedFile $file, string $directory): array
    {
        // Validation de la taille
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new FileException('Le fichier est trop volumineux (max 10 Mo).');
        }

        // Validation de l'extension
        $extension = strtolower($file->guessExtension() ?? '');
        $allowedExtensions = self::ALLOWED_EXTENSIONS[$directory] ?? self::ALLOWED_EXTENSIONS['default'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new FileException(
                sprintf('Extension non autorisee. Extensions acceptees : %s.', implode(', ', $allowedExtensions))
            );
        }

        // Validation du type MIME
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = self::ALLOWED_MIME_TYPES[$extension] ?? [];

        if (!empty($allowedMimeTypes) && !in_array($mimeType, $allowedMimeTypes, true)) {
            throw new FileException('Le type de fichier ne correspond pas a l\'extension.');
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        $targetDirectory = $this->getTargetDirectory($directory);

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $file->move($targetDirectory, $newFilename);

        return [
            'filename' => $newFilename,
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $mimeType,
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
