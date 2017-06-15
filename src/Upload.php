<?php

namespace UploadHelper;

use UploadHelper\Filter\ImageFilter;
use Zend\Filter\File\Rename;
use Zend\File\Transfer\Adapter\Http as FileTransfer;

class Upload
{
    private $publicPath;
    private $nonPublicPath;

    /**
     * Upload Helper constructor.
     *
     * @param $publicPath       Path to uload folder eg. /var/www/web-site/public/uploads/
     * @param $nonPublicPath    Path to uload folder eg. /var/www/web-site/data/uploads/
     */
    public function __construct($publicPath, $nonPublicPath)
    {
        $this->publicPath    = $publicPath;
        $this->nonPublicPath = $nonPublicPath;
    }

    /**
     * Return file path from file name, if folder doesn't not exist create it.
     *
     * @param string $file File name eg. "image.jpg"
     * @param bool $public If we need to return path at public folder or non-public
     * @return string           Path to the file
     * @throws \Exception
     */
    public function getPath($file, $public = true)
    {
        $baseDir   = $public ? $this->publicPath : $this->nonPublicPath;
        $uploadDir = $baseDir . $file[0] . '/' . $file[1] . '/' . $file[2] . '/';

        if(!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)){
            throw new \Exception("Can not create DIR $uploadDir");
        }

        return $uploadDir . $file;
    }

    /**
     * Return  web URL path - eg. /public-upload-folder/image.jpg
     *
     * @param String $file File name
     * @return string
     */
    public function getWebPath($file)
    {
        return str_replace(getcwd() . '/public', '', $this->publicPath) . "$file[0]/$file[1]/$file[2]/$file";
    }

    /**
     * Generate random file name
     *
     * @param $file Need it just to take extension
     * @return string
     */
    public function getRandomFileName($file)
    {
        return md5(rand()) . '.' . pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * Simple upload file withouth validation
     *
     * @param array $file     from $_FILE
     * @param string $key     When we have multi upload form
     * @param string $name    If we send NULL then a file will be created with new name
     * @param boolean $public Should we perform upload in public folder or non-public
     * @return string         File name
     * @throws \Exception
     */
    public function uploadFile($file, $key, $name = null, $public = true)
    {
        $overwrite = isset($name);
        $name      = is_null($name) ? $this->getRandomFileName($file['name']) : $name;
        $path      = $this->getPath($name, $public);
        $adapter   = new FileTransfer();
        $rename    = new Rename(['target' => $path, 'overwrite' => $overwrite]);

        $adapter->setFilters([$rename]);
        if(!($satus = $adapter->receive($key))){
            throw new \Exception(json_encode($adapter->getMessages()));
        }

        return $name;
    }

    /**
     * Delete file from File System.
     *
     * @param $file string file name eg. "/path/to/image.jpg"
     * @throws \Exception
     */
    public function deleteFile($file = null)
    {
        if($file){
            $name = basename($file);
            $path = $this->getPath($name);

            if(file_exists($path)){
                unlink($path);
            }
        }
    }

    /**
     * Check if file is valid image.
     *
     * @param $data         Entire $_FILE array
     * @param string $key   Key in array - eg. $_FILES['my-image']
     * @return mixed        File object ready to be uploaded
     * @throws \Exception   If file is not an image
     */
    public function filterImage($data, $key)
    {
        $filter = (new ImageFilter)->getInputFilter($key);

        if(!$filter->setData($data)->isValid()){
            throw new \Exception(json_encode($filter->getMessages()));
        }

        return $filter->getValue($key);
    }

    /**
     * Validate and upload img
     */
    public function uploadImage($data, $key)
    {
        if(!$data[$key]['name']){
            return '';
        }

        $image = $this->filterImage($data, $key);
        $name  = $this->uploadFile($image, $key);
        $path  = $this->getWebPath($name);

        return $path;
    }

}