<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Exceptions\ApieException;
use Psr\Http\Message\UploadedFileInterface;

final class FileUploadException extends ApieException
{
    private const ERRORS = [
        UPLOAD_ERR_OK => 'File upload error: no error?',
        UPLOAD_ERR_INI_SIZE => 'File too large',
        UPLOAD_ERR_FORM_SIZE => 'File too large',
        UPLOAD_ERR_PARTIAL => 'File upload is incomplete',
        UPLOAD_ERR_NO_FILE => 'No file upload found',
        UPLOAD_ERR_NO_TMP_DIR => 'Internal server file handling 6',
        UPLOAD_ERR_CANT_WRITE => 'Internal server file handling 7',
        UPLOAD_ERR_EXTENSION => 'Internal server file handling 8',
    ];

    public function __construct(UploadedFileInterface $file)
    {
        parent::__construct(self::ERRORS[$file->getError()] ?? 'Unknown file upload error');
    }
}
