<?php
namespace Images\Infrastructure;

class ImageRemoveAdapter
{

    /** @var  ClientInterface */
    protected $client;

    private $messages = [];

    /**
     * ImageRemoveAdapter constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }


    /**
     * @param $url
     * @return bool
     */
    public function removeRemoteFile($url)
    {

        $response = $this->client->removeImage($url);

        if(!$response) {
            $this->messages += ['Can\'t process file ' . $url];
        }

        $response = json_decode($response, true);
        if(empty($response['success']) || $response['success'] !== true) {
            if(!isset($response['message']) && is_string($response['message'])) {
                $this->messages += ['Can\'t process file ' . $url . ': ' . $response['message']];
            }
            $this->messages += ['Can\'t process file ' . $url];
        }

        return (bool)$response['success'];

    }


}