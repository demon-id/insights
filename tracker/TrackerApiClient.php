<?php
namespace insights\api\tracker;

use GuzzleHttp\Client AS HTTPClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class TrackerApiClient extends Component {

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
			'url' => 'get/form-info',
			'type' => 'get'
		],
		'send-edit-form-result' => [
			'url' => 'patch/form-settings',
			'type' => 'put'
		],
		'change-site-forms-status' => [
			'url' => 'put/form-info',
			'type' => 'put'
		],
		'change-all-site-forms-status' => [
			'url' => 'patch/tracking-forms-on-site',
			'type' => 'put'
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

		try {
			$response = $this->HTTPClient->$request_type($request_url, $options);

			$answer = $response->json();

			return $answer;

		} catch(\GuzzleHttp\Exception\BadResponseException $e) {

			Log::add('Message: '.$e->getMessage().' Response: '.$response->getBody(), 'api-http-errors', \Yii::getAlias('@runtime').'/logs');

			return false;
		}
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

	public function sendEditFormResult($data)
	{
		return $this->sendRequest('send-edit-form-result', [], ['data'=>$data]);
	}

	public function changeSiteFormsStatus($form_ids, $status)
	{
		return $this->sendRequest('change-site-forms-status', ['form_ids'=>$form_ids, 'status'=>$status]);
	}

	public function changeAllSiteFormsStatus($site_id, $status)
	{
		return $this->sendRequest('change-all-site-forms-status', ['site_id'=>$site_id], ['status'=>$status]);
	}

	public function deleteSite($site_id)
	{
		return $this->sendRequest('delete-site', ['site_id'=>$site_id]);
	}

    /**
     * @param $site_id
     * @param int $page
     * @param string $like
     * @return mixed
     */
    public function getSiteForms($site_id, $page = 1, $like = '')
    {
        $params = [
            'site_id'=> $site_id,
            'page'   => $page
        ];

        if (!empty($like)) {
            $params['like'] = $like;
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