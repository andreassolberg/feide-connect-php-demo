<?php

session_start();

class FeideConnect {

	protected $ep_authorization = 'https://auth.uwap.uninettlabs.no/oauth/authorization';
	protected $ep_token = 'https://auth.uwap.uninettlabs.no/oauth/token';
	protected $ep_userinfo = 'https://api.uwap.uninettlabs.no/userinfo';

	protected $config = null;

	protected $state = null;
	protected $token;

	function __construct($config) {
		$this->config = $config;

		if (!empty($_SESSION['state'])) $this->state = $_SESSION['state'];
		if (!empty($_SESSION['token'])) $this->token = $_SESSION['token'];
	}



	protected function setState($state) {
		$this->state = $state;
		$_SESSION['state'] = $state;
	}

	protected function verifyState($state) {
		// echo "Comparing state " . $state . " with stored state " . $this->state;
		if ($this->state !== $state) throw new Exception('Invalid state.');
	}

	protected function setToken($token) {
		$this->token = $token;
		$_SESSION['token'] = $token;
	}

	

	public function reset() {
		$this->setToken(null);
		$this->setState(null);
	}




	public function redirect($url, $q = null) {
		
		$fullURL = $url;
		if ($q !== null) {
			$qs = http_build_query($q);
			$fullURL .= '?' . $qs;			
		}
		header('Location: ' . $fullURL);
		exit;
	}



	protected function post($url, $q) {

		if (empty($this->config['client_id'])) throw new Exception('Configuration [client_id] is REQUIRED but not set');
		if (empty($this->config['client_secret'])) throw new Exception('Configuration [client_id] is REQUIRED but not set');

		// echo 'about to post' . "\n"; print_r($q); print_r($url); exit;
		$qs = http_build_query($q);
		$opts = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 
					"Content-type: application/x-www-form-urlencoded\r\n" . 
					"Authorization: Basic " . base64_encode($this->config['client_id'] . ':' . $this->config['client_secret']),
				'content' => $qs
			)
		);
		$context  = stream_context_create($opts);
		$result = file_get_contents($url, false, $context);
		$data = json_decode($result, true);
		if ($data === null) {
			echo 'Could not parse JSON output from Token endpoint. ' . 
				'Debug response from OAUth provider: '; print_r($result); exit;
		}
		return $data;
	}

	protected function uuid() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	public function authorize() {

		$state = $this->uuid();
		$this->setState($state);
		$q = array(
			'response_type' => 'code',
			'client_id' => $this->config['client_id'],
			'redirect_uri' => $this->config['redirect_uri'],
			'state' => $state,
		);
		$this->redirect($this->ep_authorization, $q);

	}


	public function callback() {

		if (empty($_REQUEST['code'])) return null;
		if (empty($_REQUEST['state'])) throw new Exception('Missing state parameter in the response from the OAuth Provider');

		$this->verifyState($_REQUEST['state']);

		$response = $this->resolveCode($_REQUEST['code']);
		$this->setToken($response);

		$this->redirect($this->config['redirect_uri']);
	}

	public function resolveCode($code) {

		$q = array(
			'client_id' => $this->config['client_id'],
			'redirect_uri' => $this->config['redirect_uri'],
			'grant_type' => 'authorization_code',
			'code' => $code, 
		);
		$response = $this->post($this->ep_token, $q);

		if (empty($response['access_token'])) {
			echo "response was was <pre>"; print_r($response);
			throw new Exception('Response from token endpoint did not contain an access token');
		}
		return $response;

	}

	public function getToken($get = false) {
		$token = $this->token;

		if ($token === null && $get) {
			return $this->authorize();
		}
		return $token;
	}

	public function getUserInfo() {

		return $this->protectedRequest($this->ep_userinfo);


	}
	
	public function get($url) {

		return $this->protectedRequest($url);

	}


	protected function protectedRequest($url) {

		if ($this->token === null) throw new Exception('Cannot get data without a token');

		$opts = array(
			'http' => array(
				'method'  => 'GET',
				'header'  => "Authorization: Bearer " . $this->token['access_token'],
			),
		);
		$context  = stream_context_create($opts);
		$result = file_get_contents($url, false, $context);
		$data = json_decode($result, true);
		if ($data === null) {
			echo 'Could not parse JSON output from API [' . $url . ']. ';
			echo 'Debug response from API: '; print_r($result); 
			exit;
		}
		return $data;

	}
	

}