<?php
namespace Images\Infrastructure;

use Zend\File\Transfer\Adapter\Http as FileTransferAdapter;
use Zend\File\Transfer\Exception;

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

    /**
     * Returns found files based on internal file array and given files
     *
     * @param  string|array $files (Optional) Files to return
     * @param  bool $names (Optional) Returns only names on true, else complete info
     * @param  bool $noexception (Optional) Allows throwing an exception, otherwise returns an empty array
     * @return array Found files
     * @throws Exception\RuntimeException On false filename
     */
    protected function getFiles($files, $names = false, $noexception = false)
    {
        $check = array();

        if (is_string($files)) {
            $files = array($files);
        }

        if (is_array($files)) {
            foreach ($files as $find) {
                $found = array();
                foreach ($this->files as $file => $content) {
                    if (!isset($content['url'])) {
                        continue;
                    }

                    if (($content['url'] === $find) && isset($content['multifiles'])) {
                        foreach ($content['multifiles'] as $multifile) {
                            $found[] = $multifile;
                        }
                        break;
                    }

                    if ($file === $find) {
                        $found[] = $file;
                        break;
                    }

                    if ($content['url'] === $find) {
                        $found[] = $file;
                        break;
                    }
                }

                if (empty($found)) {
                    if ($noexception !== false) {
                        return array();
                    }

                    throw new Exception\RuntimeException(sprintf('The file transfer adapter can not find "%s"', $find));
                }

                foreach ($found as $checked) {
                    $check[$checked] = $this->files[$checked];
                }
            }
        }

        if ($files === null) {
            $check = $this->files;
            $keys  = array_keys($check);
            foreach ($keys as $key) {
                if (isset($check[$key]['multifiles'])) {
                    unset($check[$key]);
                }
            }
        }

        if ($names) {
            $check = array_keys($check);
        }

        if(empty($check)) {
            $check = parent::getFiles($files, $names, $noexception);
        }

        return  $check;
    }


}