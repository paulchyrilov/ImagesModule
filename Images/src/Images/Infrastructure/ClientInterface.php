<?php
namespace Images\Infrastructure;

interface ClientInterface
{

    /**
     * @param string $filePath
     * @param string $origFileName
     * @param null|string $fileType
     * @param array $params
     * @return mixed
     */
    public function sendImage($filePath, $origFileName, $fileType = null, $params = []);

    /**
     * @param $url
     * @return mixed
     */
    public function removeImage($url);

}