<?php
/**
 * @author webworker01
 * @package webworker01/komodophp
 *
 * Interact via komodo-cli to a local full node
 *
 */

namespace webworker01\Komodo;

class Cli
{
    protected $config;
    protected $guzzle;

    public function __construct($config)
    {
        $this->config = $config;
        $this->guzzle = new \GuzzleHttp\Client();
    }

    public function run($method, $params=[])
    {
        $host = $this->config['kmd']['host'];
        $port = $this->config['kmd']['port'];
        $user = $this->config['kmd']['username'];
        $pass = $this->config['kmd']['password'];

        $result = $this->guzzle->request(
            'POST',
            $host.':'.$port,
            [
                'debug' => $this->config['debug'],
                'auth' => [$user, $pass],
                'json' => [
                    'method' => $method,
                    'params' => $params
                ],
            ]
        );

        if ($result->getStatusCode() == '200') {
            return json_decode($result->getBody())->result;
        } else {
            return false;
        }
    }
}
