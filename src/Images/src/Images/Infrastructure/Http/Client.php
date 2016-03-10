<?php
namespace Images\Infrastructure\Http;

use Images\Infrastructure\ClientInterface;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl as CurlAdapter;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\Stdlib\Parameters;

class Client implements ClientInterface
{

    /**
     * @var \Zend\Http\Client
     */
    private $transport;

    /** @var array  */
    private $query = [];

    private $domain = null;


    /**
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->transport = new HttpClient();
        $adapter = new CurlAdapter();
        $adapter->setOptions([
            'maxredirects' => 50,
            'timeout'      => $config['connectionTimeout'],
            'curloptions' => [
                CURLOPT_TIMEOUT => $config['receiveTimeout'],
                CURLOPT_HEADER => isset($config['headers']) ? $config['headers'] : []
            ],
        ]);
        $this->transport->setAdapter($adapter);

        if(empty($config['host'])) {
            throw new ServiceNotCreatedException('Host is not provided for images http client.');
        }

        $this->domain = $config['host'];

        if(isset($config['query'])) {
            $this->query = $config['query'];
        }
    }


    /**
     * @param array $params
     * @return bool|string
     */
    public function sendImage($filePath, $origFileName, $fileType = null, $params = [])
    {

        $this->transport->setUri($this->buildUrl('/index/receive'));
        $this->transport->getRequest()->setMethod('POST')
            ->setPost(new Parameters($params))
            ->setFiles(new Parameters([
                [
                    'formname' => 'image',
                    'data' => fread(fopen($filePath, 'r'), filesize($filePath)),
                    'ctype' => $fileType,
                    'filename' => $origFileName,
                ]
            ]));

        $response = $this->transport->send();

        if ($response->getStatusCode() != 200) {
            return false;
        }

        return $response->getBody();
    }

    public function removeImage($url)
    {
        $this->transport->setUri($this->buildUrl('/index/remove'));
        $this->transport->getRequest()->setMethod('GET');
        $query = $this->transport->getRequest()->getQuery();
        $query['url'] = $url;
        $this->transport->getRequest()->setQuery($query);

        $response = $this->transport->send();

        if ($response->getStatusCode() != 200) {
            return false;
        }

        return $response->getBody();
    }

    /**
     * @param string $path
     * @return string
     */
    private function buildUrl($path)
    {
        $parts = parse_url($this->domain);

        if(!empty($this->query)) {
            $parts['query'] = http_build_query($this->query);
        }

        $url = (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') .
            (isset($parts['host']) ? $parts['host'] : '') .
            (isset($parts['port']) ? ':' . $parts['port'] : '') .
            (isset($parts['user']) ? $parts['user'] : '') .
            (isset($parts['pass']) ? $parts['pass'] : '') .
            $path .
            (isset($parts['query']) ? '?' . $parts['query'] : '') .
            (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

        return $url;
    }

}