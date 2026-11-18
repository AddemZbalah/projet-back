<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\CommerceImage;
use App\Entity\Offre;
use App\Entity\OffreImage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Enregistre les photos des commerces et des offres.
 * Chaque image est ré-encodée en JPEG (1600 px max) : poids maîtrisé et métadonnées EXIF
 * (dont la position GPS des photos prises au téléphone) supprimées.
 */
class ImageUploader
{
    public const MAX_IMAGES = 8;
    private const MAX_SIDE = 1600;
    private const QUALITY = 82;

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    /**
     * @param UploadedFile[] $files
     */
    public function addToCommerce(Commerce $commerce, array $files): int
    {
        $position = $commerce->getImages()->count();
        $added = 0;

        foreach ($files as $file) {
            if ($position >= self::MAX_IMAGES) {
                break;
            }
            $commerce->addImage((new CommerceImage())
                ->setFilename($this->store($file, CommerceImage::UPLOAD_DIR))
                ->setPosition($position++));
            ++$added;
        }

        return $added;
    }

    /**
     * @param UploadedFile[] $files
     */
    public function addToOffre(Offre $offre, array $files): int
    {
        $position = $offre->getImages()->count();
        $added = 0;

        foreach ($files as $file) {
            if ($position >= self::MAX_IMAGES) {
                break;
            }
            $offre->addImage((new OffreImage())
                ->setFilename($this->store($file, OffreImage::UPLOAD_DIR))
                ->setPosition($position++));
            ++$added;
        }

        return $added;
    }

    /** « Republier » : copie les photos d'une ancienne offre (nouveaux fichiers, pour que chaque offre gère les siens). */
    public function copyOffreImages(Offre $source, Offre $target): void
    {
        foreach ($source->getImages() as $image) {
            $from = $this->path(OffreImage::UPLOAD_DIR, $image->getFilename());
            if (!is_file($from)) {
                continue;
            }
            $filename = bin2hex(random_bytes(16)).'.jpg';
            copy($from, $this->path(OffreImage::UPLOAD_DIR, $filename));
            $target->addImage((new OffreImage())->setFilename($filename)->setPosition($image->getPosition()));
        }
    }

    public function remove(CommerceImage|OffreImage $image): void
    {
        $path = $this->path($image::UPLOAD_DIR, $image->getFilename());
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function store(UploadedFile $file, string $dir): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getPathname()));
        if ($source === false) {
            throw new \RuntimeException('Image illisible.');
        }

        $source = $this->applyExifOrientation($source, $file->getPathname());
        [$w, $h] = [imagesx($source), imagesy($source)];
        $ratio = min(1, self::MAX_SIDE / max($w, $h));
        [$nw, $nh] = [(int) round($w * $ratio), (int) round($h * $ratio)];

        $target = imagecreatetruecolor($nw, $nh);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255)); // fond blanc pour les PNG transparents
        imagecopyresampled($target, $source, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $filename = bin2hex(random_bytes(16)).'.jpg';
        imagejpeg($target, $this->path($dir, $filename), self::QUALITY);

        return $filename;
    }

    /** Les photos de téléphone sont souvent stockées « couchées » avec une indication EXIF de rotation. */
    private function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        $orientation = function_exists('exif_read_data') ? (@exif_read_data($path)['Orientation'] ?? 1) : 1;

        return match ((int) $orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private function path(string $dir, string $filename): string
    {
        $directory = $this->publicDir.'/'.$dir;
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory.'/'.$filename;
    }
}
