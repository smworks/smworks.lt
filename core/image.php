<?php

class Image extends Singleton
{
    const ALLOWED_IMAGE_TYPES = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif'
    );

    public function save($file)
    {
        $this->mkdirs();
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            return 'Invalid upload';
        }

        $imageType = exif_imagetype($file['tmp_name']);
        if (!isset(self::ALLOWED_IMAGE_TYPES[$imageType])) {
            http_response_code(400);
            return 'Unsupported image type';
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_IMAGE_TYPES[$imageType];
        $targetPath = 'uploads/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            http_response_code(500);
            return 'Failed to store image';
        }

        return json_encode(array('location' => $targetPath), JSON_UNESCAPED_SLASHES);
    }

    public function load($name, $width, $height)
    {
        $safeName = basename($name);
        $path = 'uploads/' . $safeName;
        if (!file_exists($path)) {
            return false;
        }

        $resizedPath = 'uploads/' . $width . 'x' . $height . '_' . $safeName . '.jpg';
        if (!file_exists($resizedPath)) {
            $this->resize($path, $resizedPath, $width, $height);
        }

        return $resizedPath;
    }

    private function resize($path, $resizedPath, $width, $height)
    {
        $imageType = exif_imagetype($path);

        $ext = image_type_to_extension($imageType);
        $img = null;

        switch ($ext) {
            case '.jpg':
            case '.jpeg':
                $img = imagecreatefromjpeg($path);
                break;
            case '.png':
                $img = imagecreatefrompng($path);
                break;
            case '.gif':
                $img = imagecreatefromgif($path);
                break;
            default:
                return;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        $originalRatio = $w / $h;
        $resizedRatio = $width / $height;

        $offsetX = 0;
        $offsetY = 0;

        if ($resizedRatio > $originalRatio) { // Original image is more vertical
            $newHeight = $height;
            $newWidth = $height * $originalRatio;
            $offsetX = ($width - $newHeight) * 0.5;
        } else { // Original image is more horizontal or equal
            $newWidth = $width;
            $newHeight = $width * (1.0 / $originalRatio);
            $offsetY = ($height - $newHeight) * 0.5;
        }

        $resized = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $img, $offsetX, $offsetY, 0, 0,
            $newWidth, $newHeight, $w, $h);

        imageJpeg($resized, $resizedPath);
    }


    private function mkdirs()
    {
        if (!file_exists('uploads/')) {
            mkdir('uploads', 0755, true);
        }
    }
}
