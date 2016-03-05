<?php
namespace insights\api\tracker;

use GuzzleHttp\Client AS HTTPClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class TrackerApiClient extends Component
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
		'add-site' => [
			'url' => 'post/site',
			'type' => 'post'
		],
		'delete-site' => [
			'url' => 'sites/delete-site',
			'type' => 'put'
		],
		'get-site-page-forms' => [
			'url' => 'get/forms',
			'type' => 'get'
		],
		'get-site-page-form-info' => [
			'url' => 'get/form-details',
			'type' => 'get'
		],
		'send-edit-form-result' => [
			'url' => 'patch/form-details',
			'type' => 'patch'
		],
		'change-site-forms-status' => [
			'url' => 'put/form-info',
			'type' => 'put'
		],
		'forms-status-on-site' => [
			'url' => 'patch/forms-status-on-site',
			'type' => 'patch'
		],
        'forms-status-from-list' => [
            'url' => 'patch/forms-status-from-list',
            'type' => 'patch'
        ],
        'get-site-forms' => [
            'url' => 'get/forms',
            'type' => 'get'
        ],
        'get-site-leads' => [
            'url' => 'get/leads',
            'type' => 'get'
        ],
        'get-site-visitors' => [
            'url' => 'get/visitors',
            'type' => 'get'
        ],
        'get-lead-profile' => [
            'url' => 'get/lead-profile',
            'type' => 'get'
        ],
        'get-lead-forms' => [
            'url' => 'get/lead-forms',
            'type' => 'get'
        ],
        'get-lead-visits' => [
            'url' => 'get/lead-visits',
            'type' => 'get'
        ],
        'get-site-tracker' => [
            'url' => 'get/tracker',
            'type' => 'get'
        ],
        'get-form-pages' => [
            'url' => 'get/form-pages',
            'type' => 'get'
        ],
        'change-tracker-status' => [
            'url' => 'patch/tracker-status',
            'type' => 'patch'
        ],
		'count-sites-leads' => [
			'url' => 'get/quantity-leads-on-sites',
			'type' => 'get'
		],
	];

	public function __construct() {
		$this->HTTPClient = new HTTPClient;
	}

	protected function sendRequest($name, $query_params=[], $body_params=[], $headers=[])
	{
		$headers = ArrayHelper::merge($headers, [
			'api-key' => $this->apiKey
		]);

		$request_params = $this->getRequestParams($name);
		$request_type = $request_params['type'];
		$request_url = $this->apiUrl.$request_params['url'];

		$options = [
			'headers'=>$headers
		];

		$options['query'] = $query_params;
		$options['json'] = $body_params;
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

	public function addSite($site_id, $url)
	{
		return $this->sendRequest('add-site', [], ['site_id'=>$site_id, 'url'=>$url]);
	}

	public function getSitePageForms($site_id, $url)
	{
		return $this->sendRequest('get-site-page-forms', ['site_id'=>$site_id, 'url'=>$url]);
	}

	public function getSitePageFormInfo($form_id)
	{
		return $this->sendRequest('get-site-page-form-info', ['form_id'=>$form_id]);
	}

	public function changeSiteFormsStatus($form_ids, $status)
	{
		return $this->sendRequest('change-site-forms-status', ['form_ids'=>$form_ids, 'status'=>$status]);
	}

    /**
     * @param array $form
     * @param array $fields
     * @return bool
     */
    public function sendEditFormResult(array $form = [], array $fields = [])
    {
        return $this->sendRequest('send-edit-form-result', [], [
            'form'   => $form,
            'fields' => $fields,
        ]);
    }

    /**
     * @param $site_id
     * @param $status
     * @return bool
     */
	public function changeFormsStatusOnSite($site_id, $status)
	{
		return $this->sendRequest('forms-status-on-site', ['site_id' => $site_id], [
            'status' => $status
        ]);
	}

    /**
     * @param array $form_ids
     * @param $status
     * @return bool
     */
    public function changeFormsStatusFromList(array $form_ids, $status)
    {
        return $this->sendRequest('forms-status-from-list', [], [
            'form_ids' => $form_ids,
            'status'   => $status
        ]);
    }

	public function deleteSite($site_id)
	{
		return $this->sendRequest('delete-site', ['site_id'=>$site_id]);
	}

    /**
     * @param $site_id
     * @param int $page
     * @param string $url
     * @return mixed
     */
    public function getSiteForms($site_id, $page = 1, $url = '')
    {
        $params = [
            'site_id'=> $site_id,
            'page'   => $page
        ];

        if (!empty($url)) {
            $params['url'] = $url;
        }

        return $this->sendRequest('get-site-forms', $params);
    }

    /**
     * @param $site_id
     * @param $order_by
     * @param $page
     * @param $like
     * @return mixed
     */
    public function getSiteLeads($site_id, $order_by = '', $page = 1, $like = '')
    {
        $params = [
            'site_id'  => $site_id,
            'order_by' => $order_by,
            'page'     => $page
        ];

        if (!empty($like)) {
            $params['like'] = $like;
        }

        return $this->sendRequest('get-site-leads', $params);
    }

    /**
     * @param $site_id
     * @param string $order_by
     * @param int $page
     * @return mixed
     */
    public function getSiteVisitors($site_id, $page = 1)
    {
        return $this->sendRequest('get-site-visitors', [
            'site_id' => $site_id,
            'page'    => $page
        ]);
    }

    /**
     * @param $lead_id
     * @return mixed
     */
    public function getLeadProfile($lead_id)
    {
        return $this->sendRequest('get-lead-profile', [
            'lead_id' => $lead_id,
        ]);
    }

    /**
     * @param $site_id
     * @param $lead_id
     * @param $page
     * @return mixed
     */
    public function getLeadForms($lead_id, $page = 1)
    {
        return $this->sendRequest('get-lead-forms', [
            'lead_id' => $lead_id,
            'page'    => $page
        ]);
    }

    /**
     * @param $site_id
     * @param $lead_id
     * @param $page
     * @return mixed
     */
    public function getLeadVisits($lead_id, $page = 1)
    {
        return $this->sendRequest('get-lead-visits', [
            'lead_id' => $lead_id,
            'page'    => $page
        ]);
    }

    /**
     * @param $site_id
     * @return mixed
     */
    public function getSiteTracker($site_id)
    {
        return $this->sendRequest('get-site-tracker', [
            'site_id' => $site_id,
        ]);
    }

    /**
     * @param $form_id
     * @param int $page
     * @return bool
     */
    public function getFormPages($form_id, $page = 1)
    {
        return $this->sendRequest('get-form-pages', [
            'form_id' => $form_id,
            'page'    => $page
        ]);
    }

    /**
     * @param $tracker_id
     * @param $status
     * @return mixed
     */
    public function changeTrackerStatus($site_id, $status)
    {
        return $this->sendRequest('change-tracker-status', ['site_id' => $site_id], [
            'status'  => $status,
        ]);
    }

	public function countSitesLeads($site_ids)
	{
		return $this->sendRequest('count-sites-leads', ['site_ids'=>$site_ids]);
	}

}