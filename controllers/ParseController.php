<?php

namespace app\controllers;

use GuzzleHttp\Exception\GuzzleException;
use PhpParser\Node\Expr\Empty_;
use yii;
use DiDom\Document;
use yii\web\controller;


class ParseController extends Controller
{
    protected function getStrHTMLByUrlCurl($url): array
    {
        $curl = curl_init($url);
        // True to return the transfer as a string of the return value of curl_exec() instead of outputting it directly:
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // False to stop CURL from verifying the peer's certificate (сертификат узла сети):
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        // The contents of the "user-agent": header to be used in HTTP request
        curl_setopt($curl, CURLOPT_USERAGENT, 'Agent');
        // False to remove header in the output:
        curl_setopt($curl, CURLOPT_HEADER, false);
        // True to follow any "Location": header that the sever sends as part of the HTTP header (if server makes redirections):
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // curl_exec() function can start the CURL session:
        $documentStrHTML = curl_exec($curl);
        $errorMsg = null;
        if ($error = curl_error($curl))
            $errorMsg = $error;

        // Close our session after execution:
        curl_close($curl);

        if (isset($errorMsg))
            return ['is_ok' => false, 'data' => $errorMsg];

        return ['is_ok' => true, 'data' => $documentStrHTML ];
    }

    protected function getStrHTMLByUrlGuzzle($url): array
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            return ['is_ok' => true, 'data' => $response->getBody()->getContents()];
        } catch (GuzzleException $exception) {
            return ['is_ok' => false , 'data' => $exception->getCode() . ' | ' . $exception->getMessage()];
        }
    }

    protected function print_array($array)
    {
        echo '<pre>' . print_r($array, true) . '</pre>';
    }
}