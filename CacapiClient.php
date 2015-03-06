<?php
class CacapiClient
{
    const USER_AGENT = 'CacapiClient/cURL';
    private $login;
    private $apiKey;
    private $apiUrl = 'https://panel.cloudatcost.com/api/v1/';

    const RESPONSE_OK = 200;

    public function __construct($login, $apiKey, $apiUrl = 'https://panel.cloudatcost.com/api/v1/')
    {
        $this->login = $login;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    public function listServers()
    {
        return $this->callFunction('listservers');
    }

    public function listTemplates()
    {
        return $this->callFunction('listtemplates');
    }

    public function listTasks()
    {
        return $this->callFunction('listtasks');
    }

    public function powerOpt()
    {
        return $this->callFunction('poweropp');
    }

    private function callFunction($functionName)
    {
        $getParameters = http_build_query(array(
            'login' => $this->login,
            'key' => $this->apiKey
        ));
        #var_dump($getParameters);
        $url = "$this->apiUrl$functionName/?login=$this->login&key=$this->apiKey";
        var_dump($url);
        $curlHandle = curl_init($url);

        $this->setCurlOptions(
            $curlHandle,
                array(
                #CURLOPT_POST => count($postParameters),
                #CURLOPT_POSTFIELDS => $postParameters,
                #CURLOPT_COOKIE => $this->getCookies($additionalCookies),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => 0,
                #CURLOPT_USERAGENT => self::USER_AGENT
            )
        );

        $response = json_decode(curl_exec($curlHandle));
        $httpResponseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);
        if (self::RESPONSE_OK !== $httpResponseCode)
        {
            throw new CacapiClientRequestException((int)$httpResponseCode, $response);
        }
        return $response;
    }

    private function setCurlOptions(&$curlHandle, array $options)
    {
        foreach ($options as $curlOpt => $value)
        {
            curl_setopt($curlHandle, $curlOpt, $value);
        }
    }
}

class CacapiClientException extends \Exception {}
class CacapiClientRequestException extends \CacapiClientException
{
    const RESPONSE_INVALID_URL = 400; //Invalid api URL
    const RESPONSE_INVALID_KEY = 403; //Invalid or missing api key
    const RESPONSE_REQUEST_FAILED = 412; //Request failed
    const RESPONSE_INTERNAL_ERROR = 500; //Internal server error
    const RESPONSE_RATE_LIMIT = 503; //Rate limit hit

    /**
     * @param int $httpResponseCode
     * @param obj $response
     */
    public function __construct($httpResponseCode, $response)
    {
        if (is_object($response))
        {
            $message = sprintf(
                'time: %s, status: %s, error: %d, description: %s, HTTP: %d, definition: %s',
                date('Y-m-d H:i:s', $response->time),
                $response->status,
                $response->error,
                $response->error_description,
                $httpResponseCode,
                $this->httpResponseCodeToMessage($httpResponseCode)
            );
        }
        else
        {
            $message = $this->httpResponseCodeToMessage($httpResponseCode);
        }
        parent::__construct($message);
    }

    private function httpResponseCodeToMessage($responseCode)
    {
        $errorMessageMapping = array(
            self::RESPONSE_INVALID_URL => 'Invalid URL provided',
            self::RESPONSE_INVALID_KEY => 'Invalid API-key provided',
            self::RESPONSE_REQUEST_FAILED => 'Request failed',
            self::RESPONSE_INTERNAL_ERROR => 'There were internal error at CloudAtCost end',
            self::RESPONSE_RATE_LIMIT => 'Rate limit reached',
        );
        if ( ! isset($errorMessageMapping[$responseCode]))
        {
            return "Unknown HTTP-response-code '$responseCode'";
        }
        return $errorMessageMapping[$responseCode];
    }
}
