<?php

class Image extends Singleton
{

    public function save($file)
    {
        $this->mkdirs();
        $fileName = $file['name'];
        $fileType = $file['type'];
        $fileError = $file['error'];
        $fileContent = file_get_contents($file['tmp_name']);
        if (!file_exists('uploads/' . $fileName)) {
            file_put_contents('uploads/' . $fileName, $fileContent);
        }
        return $fileName;
    }

    public function load($name, $width, $height)
    {
        $path = 'uploads/' . $name;
        if (!file_exists($path)) {
            return false;
        }

        $resizedPath = 'uploads/' . $width . 'x' . $height . '_' . $name . '.jpg';
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
        if (!file_exists('images/')) {
            mkdir('images', 0755, true);
        }
    }
}
