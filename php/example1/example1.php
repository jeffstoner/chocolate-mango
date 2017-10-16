<?php

require_once __DIR__ . '/vendor/autoload.php';


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class Example1 {
    private $orgId = null;
    private $user = null;
    private $pass = null;

    public function __construct ($user = null, $pass = null)
    {
        if (isset($user)) {
            $this->user = $user;
        }
        if (isset($pass)) {
            $this->pass = $pass;
        }
    }

    public function getMyAccount()
    {
        $content = null;

        if (!isset($this->user)) {
            throw new Exception('Username is not set.');
        }
        if (!isset($this->pass)) {
            throw new Exception('Password is not set.');
        }

        $uri = 'https://api-na.dimensiondata.com/caas/2.5/user/myUser';
        $content = $this->makeTheCall($uri);

        // Actually do something with the result
        if (isset($content) && isset($content['organization']['id'])) {
            $this->orgId = $content['organization']['id'];
        } else {
            echo "Could not determine Organization ID.\n";
        }
    }

    public function getDatacenter()
    {
        $content = null;

        if ($this->orgId == null || $this->orgId == '') {
            // We didn't set our orgId...so set it
            $this->getMyAccount();
        }

        $uri = 'https://api-na.dimensiondata.com/caas/2.5/' . $this->orgId . '/infrastructure/datacenter';
        $content = $this->makeTheCall($uri);

        if (isset($content)) {
            // process the results
            if (isset($content['datacenter'])) {
                // strip off the paging envelope
                $datacenters = $content['datacenter'];
                $numDatacenters = count($datacenters);
                echo "There are {$numDatacenters} in this MCP\n";
                foreach ($datacenters as $dc) {
                    echo $dc['displayName'] . ' : ' . $dc['id'] . ' (' . $dc['type'] . ")\n";
                }
            }
        } else {
            echo "API call was successful but got an empty document.\n";
        }
    }

    private function makeTheCall($uri = null)
    {
        $content = null;

        if (!isset($this->user)) {
            throw new Exception('Username is not set.');
        }
        if (!isset($this->pass)) {
            throw new Exception('Password is not set.');
        }
        if (!isset($uri)) {
            throw new Exception('URI is not set.');
        }

        $headers = [
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [$this->user, $this->pass]
        ];
        $client = new Client();
        $safe = false;

        try {
            $response = $client->request('GET', $uri, $headers);
            $safe = true;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            // the network failed, but don't crash the app
            echo "Failed to connect to API endpoint.\n";
        } finally {

            if ($safe) {
                if ($response->getStatusCode() == 200) {
                    $body = $response->getBody();
                    if (strlen($body) > 1) {
                        $content = json_decode($body, true);
                    } else {
                        echo "Returned content is too short.\n";
                    }
                } else {
                    echo "API call failed.\n";
                }
            } else {
                echo "Could not establish API call.\n";
            }
        }

        return $content;
    }
}

$grab = new Example1('myUserName', 'myPassword');

$grab->getDatacenter();

