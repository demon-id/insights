<?php
namespace insights\api\tracker;

use GuzzleHttp\Client AS HTTPClient;
use GuzzleHttp\Post\PostFile;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use common\components\Log;

class SocialApiClient extends Component
{
    /**
     * @const prefix CURLOPT_
     */
    const CONNECTTIMEOUT = 5;
    const TIMEOUT = 5;

    public $apiUrl;

    public $apiKey;

    protected $HTTPClient;

    protected $methodParams = [
        'get-social-links' => [
            'url' => 'get/social-links',
            'type' => 'get'
        ],
    ];

    public function __construct() {
        $this->HTTPClient = new HTTPClient;
    }

    protected function sendRequest($name, $query_params=[], $headers=[])
    {
        $headers = ArrayHelper::merge($headers, [
            'api-key' => $this->apiKey
        ]);

        $request_params = $this->getRequestParams($name);
        $query_str = implode('/', $query_params);
        $request_type = $request_params['type'];
        $request_url = $this->apiUrl . $request_params['url'] . '/' . $query_str;

        $options = [
            'headers'=>$headers
        ];

        $options['query'] = $query_params;
        $options['exceptions'] = false;
        $options['connect_timeout'] = self::CONNECTTIMEOUT;
        $options['timeout'] = self::TIMEOUT;

        $response = null;
        try {
            $response = $this->HTTPClient->$request_type($request_url, $options);

            $answer = $response->json();

            return $answer;

        } catch(\GuzzleHttp\Exception\BadResponseException $e) {

            $this->logExceptions($request_url, $response, $e);

            return false;
        } catch(\GuzzleHttp\Exception\ParseException $e) {

            $this->logExceptions($request_url, $response, $e);

            return false;
        } catch(\GuzzleHttp\Exception\ConnectException $e) {

            $this->logExceptions($request_url, $response, $e);

            return false;
        }

    }

    protected function logExceptions($request_url, $response, $e)
    {
        Log::add(
            'Url:'.$request_url."\n".
            'Message: '.$e->getMessage()."\n".
            'Response: '.(($response) ? $response->getBody() : '')."\n",
            'api-http-errors',
            \Yii::getAlias('@runtime').'/logs'
        );
    }

    protected function getRequestParams($name)
    {
        if (empty($this->methodParams[$name])) {
            throw new \BadMethodCallException('Api method is undefined.');
        }

        return $this->methodParams[$name];
    }

    /**
     * @param $form_id
     * @param int $page
     * @return bool
     */
    public function getSocialLinks($email)
    {
        return $this->sendRequest('get-social-links', [$email]);
    }
}