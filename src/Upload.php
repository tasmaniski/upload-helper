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
     * Upload constructor.
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
     * @param string $file File name eg. "image.jpg"
     * @param bool $public are we need to return path at public folder or non-public
     * @return type
     * @throws Exception
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
     * Return  web URL path - eg. http://web-site.com/public-upload-folder/image.jpg
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
     * Simple upload file withouth validators
     *
     * @param array $file     from $_FILE
     * @param string $name    If we send NULL then a file will be created with new name
     * @param boolean $public Should we perform upload in public folder or non-public
     * @param string $key     if you have multi upload form, you must pass key
     * @return string
     * @throws Exception
     */
    public function uploadFile($file, $public = true, $name = null, $key = null)
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
     * Delete file from FS
     *
     * @param $file string file name eg. "image.jpg"
     */
    public function deleteFile($file)
    {
        $imagePath = getcwd() . '/public' . $file;

        if(file_exists($imagePath)){
            unlink($imagePath);
        }
    }

    /**
     * Use image filter and return filtered value
     */
    public function filterImage($data, $key = 'image')
    {
        $filter = (new ImageFilter)->getInputFilter();

        if(!$filter->setData($data)->isValid()){
            throw new \Exception(json_encode($filter->getMessages()));
        }

        return $filter->getValue($key);
    }

}