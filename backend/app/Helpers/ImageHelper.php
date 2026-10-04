<?php

namespace App\Helpers;
use File;

class ImageHelper
{

    private function __construct()
    {
    }

    public static function getInstance()
    {
        return new self();
    }

    /**
     * @param string $folder
     * @param $image
     * @return string
     */
    public function getImageUrl(string $folder, $image)
    {
        if ($image)
            return get_baseUrl() . '/images/' . $folder . '/' . $image;
        return get_baseUrl() . '/images/1.png';
    }

    /**
     * @param string $folder
     * @param $file
     * @return string
     */
    public function getFileUrl(string $folder, $file)
    {
        if ($file) {
            return get_baseUrl() . '/files/' . $folder . '/' . $file;
        }
        return get_baseUrl() . '/images/1.png'; // Default image URL if no file is specified
    }



    /**
     * @param $folder
     * @param $file
     * @return string
     */
    public function saveImage($folder, $file)
    {
        $image = $file;
        $input['image'] = mt_rand() . time() . '.' . $image->getClientOriginalExtension();
        $dist = public_path('/images/' . $folder . '/');
        $image->move($dist, $input['image']);
        return $input['image'];
    }


    /**
     * @param $folder
     * @param $file
     * @return string
     */
    public function saveFile($folder, $file)
    {
        $fileData = $file;
        $fileName = mt_rand() . time() . '.' . $fileData->getClientOriginalExtension();
        $dist = public_path('/files/' . $folder . '/');
        $fileData->move($dist, $fileName);
        return $fileName;
    }

    /**
     * @param $folder
     * @param $file
     * @return int
     */
    public function deleteFile($folder, $file)
    {
        $file = public_path('/images/' . $folder . '/' . $file);
        if (file_exists($file)) {
            File::delete($file);
        }
        return 1;
    }

}
