<?php
require_once 'CacapiClient.php';
date_default_timezone_set('Europe/Helsinki');

$client = new CacapiClient('joona.somerkivi@gmail.com', 'sESaHe5EPu9YNusEHU8e8a4a2');

try
{
    var_dump($client->listServers());
    var_dump($client->listTemplates());
    var_dump($client->listTasks());
} catch (CacapiClientException $e)
{
    echo get_class($e) , ': ' , $e->getMessage(), "\n";
}
