<?php
namespace Images\Infrastructure;

use Zend\File\Transfer\Adapter\Http as FileTransferAdapter;

class ImageTransferAdapter extends FileTransferAdapter
{

    private $system = 'default';

    /** @var  ClientInterface */
    protected $client;

    /**
     * @param ClientInterface $client
     * @param array $options
     */
    public function __construct(ClientInterface $client, $options = [])
    {
        if(isset($options['system']) && is_string($options['system'])) {
            $this->system = $options['system'];
        }
        $this->client = $client;
        parent::__construct($options);
    }

    public function receive($files = null, $tag = null)
    {
        if (!$this->isValid($files)) {
            return false;
        }

        $check = $this->getFiles($files);
        foreach ($check as $file => $content) {
            if(!is_file($content['tmp_name'])) {
                $this->messages += ['Can\'t  receive file ' . $content['name']];
                continue;
            }
            $response = $this->client->sendImage(
                $content['tmp_name'],
                $content['name'],
                $content['type'],
                [
                    'system' => $this->system,
                    'tag' => $tag
                ]
            );

            if(!$response) {
                $this->messages += ['Can\'t process file ' . $content['name']];
            }

            $response = json_decode($response, true);
            if(empty($response['success']) || $response['success'] !== true) {
                if(!isset($response['message']) && is_string($response['message'])) {
                    $this->messages += ['Can\'t process file ' . $content['name'] . ': ' . $response['message']];
                }
                $this->messages += ['Can\'t process file ' . $content['name']];
                continue;
            }

            if(empty($response['result'])) {
                $this->messages += ['Can\'t process file ' . $content['name'] . '. Result is empty'];
                continue;
            }

            unlink($content['tmp_name']);
            $this->files[$file]['url'] = $response['result'];
            $this->files[$file]['received'] = true;
        }

        return true;
    }

    public function getFileName($file = null, $path = true)
    {
        return $this->getFileUrl($file);
    }


    /**
     * Use this method to receive external file URL;
     *
     * @param null $file
     * @return array|string
     */
    public function getFileUrl($file = null)
    {
        $files     = $this->getFiles($file, true, true);
        $result    = array();
        foreach ($files as $file) {
            if (empty($this->files[$file]['name'])) {
                continue;
            }

            $result[$file] = $this->files[$file]['url'];
        }

        if (count($result) == 1) {
            return current($result);
        }

        return $result;
    }


}