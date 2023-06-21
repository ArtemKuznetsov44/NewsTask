<?php


namespace app\controllers;

use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use yii;
use yii\web\Controller;
use function PHPUnit\Framework\throwException;


class NewsController extends Controller
{
    private function getStrHTMLByUrlCurl($url): string
    {
        try {
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

            // Close our session after execution:
            curl_close($curl);

            return $documentStrHTML;
        }
        catch (yii\base\ExitException $exception) {
            return $exception->statusCode . ' | '. $exception->getMessage();
        }
    }

    private function getStrHTMLByUrlGuzzle($url): string
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            return $response->getBody()->getContents();
        }
        catch(yii\base\ExitException $exception) {
            return $exception->statusCode . ' | ' . $exception->getMessage();
        }
    }


    private function print_array($array)
    {
        echo '<pre>' . print_r($array, true) . '</pre>';
    }

    /**
     * @throws InvalidSelectorException
     */
    public function actionIndex()
    {
//        // Getting DOM structure from string:
//        $documentDOM = new Document($this->getStrHTMLByUrl($url = 'https://www.rbc.ru'));

        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://www.rbc.ru/');
        $documentHTML = $response->getBody()->getContents();

        $documentDOM = new Document($documentHTML);

        $newsList = $documentDOM->find('div.js-news-feed-list a[data-yandex-name="from_news_feed"]');
        foreach ($newsList as $new) {
            echo "<hr/>";
            echo $new->find('span.news-feed__item__title')[0]->text();
            echo "<br/>";
            echo $new->attr('href');
            echo "<hr/>";
        }
        echo count($newsList);
    }
}
