<?php

namespace App\Service;

use App\Entity\ObservationPhoto;
use App\Entity\ObservationReport;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ObservationUploadService
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * Move the uploaded evidence images into the public folder, attach them to the report and assign positions.
     *
     * @param UploadedFile[] $files
     */
    public function attachPhotos(ObservationReport $report, array $files, int $startPosition = 0): void
    {
        $position = $startPosition;
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = sprintf('%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);

            $target = $this->projectDir.'/public/uploads/observations/'.$filename;
            $directory = \dirname($target);
            if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Could not create upload directory "%s".', $directory));
            }

            $file->move($directory, $filename);

            $photo = new ObservationPhoto();
            $photo->setFilename($filename)
                ->setPosition($position++)
                ->setCaption($file->getClientOriginalName());

            $report->addPhoto($photo);
        }
    }
}