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
			'url' => 'sites/add-site',
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
			'url' => 'get/forminfo',
			'type' => 'get'
		],
		'send-edit-form-result' => [
			'url' => 'patch/form-settings',
			'type' => 'put'
		],
		'change-site-forms-status' => [
			'url' => 'put/forminfo',
			'type' => 'put'
		],
		'change-all-site-forms-status' => [
			'url' => 'patch/tracking-forms-on-site',
			'type' => 'put'
		],
	];

	public function __construct() {
		$this->HTTPClient = new HTTPClient;
	}

	protected function sendRequest($name, $params=[], $headers=[])
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

		$options['query'] = $params;
		$options['json'] = $params;

		$response = $this->HTTPClient->$request_type($request_url, $options);

		$answer = $response->json();

		return $answer;
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
		return $this->sendRequest('add-site', ['site_id'=>$site_id, 'url'=>$url]);
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
		return $this->sendRequest('send-edit-form-result', ['data'=>$data]);
	}

	public function changeSiteFormsStatus($form_ids, $status)
	{
		return $this->sendRequest('change-site-forms-status', ['form_ids'=>$form_ids, 'status'=>$status]);
	}

	public function changeAllSiteFormsStatus($site_id, $status)
	{
		return $this->sendRequest('change-all-site-forms-status', ['site_id'=>$site_id, 'status'=>$status]);
	}

	public function deleteSite($site_id)
	{
		return $this->sendRequest('delete-site', ['site_id'=>$site_id]);
	}
}