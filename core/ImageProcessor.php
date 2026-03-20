<?php

declare(strict_types=1);

namespace Core;

/**
 * Image Processor — compress, resize, and convert uploads to WebP.
 *
 * Requirements: PHP GD extension (standard on most shared hosts).
 * Falls back gracefully if GD is unavailable (copies the original).
 *
 * Usage:
 *   $webpPath = ImageProcessor::process($sourcePath, $destDir, $maxW, $quality);
 */
class ImageProcessor
{
    public const DEFAULT_QUALITY  = 82;   // WebP quality (0-100)
    public const DEFAULT_MAX_W    = 1200; // Max width in pixels
    public const THUMB_MAX_W      = 400;  // Thumbnail max width
    public const THUMB_MAX_H      = 400;  // Thumbnail max height

    /**
     * Process (compress + optionally resize) an uploaded image.
     *
     * @param string $sourcePath   Absolute path to the temp/source file
     * @param string $destDir      Absolute path to destination directory
     * @param string $filename     Filename WITHOUT extension (we add .webp)
     * @param int    $maxW         Max width — 0 = no resize
     * @param int    $quality      WebP quality (0–100)
     * @return string              Destination absolute path of the processed image
     */
    public static function process(
        string $sourcePath,
        string $destDir,
        string $filename,
        int $maxW    = self::DEFAULT_MAX_W,
        int $quality = self::DEFAULT_QUALITY
    ): string {
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        // Determine if GD WebP is available
        $canWebP = function_exists('imagewebp') && function_exists('imagecreatefromjpeg');

        if (!$canWebP || !self::gdSupportsSource($sourcePath)) {
            // Fallback: just move the file with the original extension
            $ext  = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            $dest = $destDir . '/' . $filename . '.' . $ext;
            copy($sourcePath, $dest);
            return $dest;
        }

        $dest = $destDir . '/' . $filename . '.webp';

        try {
            $im = self::createFromAny($sourcePath);
            if ($im === null) {
                copy($sourcePath, $dest);
                return $dest;
            }

            // Resize if needed
            if ($maxW > 0) {
                $im = self::resizeIfNeeded($im, $maxW);
            }

            // Auto-rotate EXIF (JPEG only)
            $im = self::autoRotate($im, $sourcePath);

            // Convert to WebP
            imagewebp($im, $dest, $quality);
            imagedestroy($im);

        } catch (\Throwable $e) {
            // Absolute last resort — copy original
            copy($sourcePath, $dest);
        }

        return $dest;
    }

    /**
     * Generate a square thumbnail at THUMB_MAX_W×THUMB_MAX_H.
     *
     * @return string  Absolute path to the thumbnail
     */
    public static function thumbnail(
        string $sourcePath,
        string $destDir,
        string $filename,
        int $size    = self::THUMB_MAX_W,
        int $quality = self::DEFAULT_QUALITY
    ): string {
        if (!function_exists('imagewebp')) {
            return self::process($sourcePath, $destDir, $filename . '_thumb', $size, $quality);
        }

        $dest = $destDir . '/' . $filename . '_thumb.webp';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);

        try {
            $im = self::createFromAny($sourcePath);
            if (!$im) { copy($sourcePath, $dest); return $dest; }

            $w = imagesx($im);
            $h = imagesy($im);

            // Square crop from center
            $sq   = min($w, $h);
            $offX = (int)(($w - $sq) / 2);
            $offY = (int)(($h - $sq) / 2);

            $thumb = imagecreatetruecolor($size, $size);
            imagecopyresampled($thumb, $im, 0, 0, $offX, $offY, $size, $size, $sq, $sq);
            imagedestroy($im);

            imagewebp($thumb, $dest, $quality);
            imagedestroy($thumb);
        } catch (\Throwable) {
            copy($sourcePath, $dest);
        }

        return $dest;
    }

    /**
     * Process an uploaded $_FILES entry and return the relative public path.
     * Called by controllers after move_uploaded_file or from tmp_name directly.
     *
     * @param array{tmp_name:string, name:string, size:int, error:int, type:string} $file  $_FILES entry
     * @param string $publicDir  e.g. BASE_PATH . '/public/assets/img/products/123/'
     * @param string $publicBase e.g. '/assets/img/products/123/'
     * @return string|null  Relative public URL path, or null on failure
     */
    public static function handleUpload(array $file, string $publicDir, string $publicBase): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime    = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed, true)) return null;

        $name    = bin2hex(random_bytes(8));
        $absPath = self::process($file['tmp_name'], $publicDir, $name);
        $relPath = rtrim($publicBase, '/') . '/' . basename($absPath);

        return $relPath;
    }

    // ── Internal ──────────────────────────────────────────────────

    private static function gdSupportsSource(string $path): bool
    {
        $mime = mime_content_type($path);
        return match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg'),
            'image/png'  => function_exists('imagecreatefrompng'),
            'image/webp' => function_exists('imagecreatefromwebp'),
            'image/gif'  => function_exists('imagecreatefromgif'),
            default      => false,
        };
    }

    private static function createFromAny(string $path): ?\GdImage
    {
        $mime = mime_content_type($path);
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path) ?: null,
            'image/png'  => self::pngWithAlpha($path),
            'image/webp' => imagecreatefromwebp($path)  ?: null,
            'image/gif'  => imagecreatefromgif($path)   ?: null,
            default      => null,
        };
    }

    private static function pngWithAlpha(string $path): ?\GdImage
    {
        $im = imagecreatefrompng($path);
        if (!$im) return null;
        imagealphablending($im, true);
        imagesavealpha($im, true);
        // Flatten alpha onto white background for WebP output
        $w   = imagesx($im);
        $h   = imagesy($im);
        $bg  = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $im, 0, 0, 0, 0, $w, $h);
        imagedestroy($im);
        return $bg;
    }

    /** @return \GdImage */
    private static function resizeIfNeeded(\GdImage $im, int $maxW): \GdImage
    {
        $w = imagesx($im);
        $h = imagesy($im);
        if ($w <= $maxW) return $im;

        $newH = (int)round($h * $maxW / $w);
        $new  = imagecreatetruecolor($maxW, $newH);
        imagecopyresampled($new, $im, 0, 0, 0, 0, $maxW, $newH, $w, $h);
        imagedestroy($im);
        return $new;
    }

    /** @return \GdImage */
    private static function autoRotate(\GdImage $im, string $path): \GdImage
    {
        if (!function_exists('exif_read_data')) return $im;
        $mime = mime_content_type($path);
        if ($mime !== 'image/jpeg') return $im;

        try {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? 1;
            return match ((int)$orientation) {
                3 => imagerotate($im, 180, 0)  ?: $im,
                6 => imagerotate($im, -90, 0) ?: $im,
                8 => imagerotate($im, 90, 0)  ?: $im,
                default => $im,
            };
        } catch (\Throwable) {
            return $im;
        }
    }
}
