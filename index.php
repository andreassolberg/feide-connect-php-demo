<?php

require_once('./lib/FeideConnect.php');
require_once('./lib/misc.php');
require_once('./etc/config.php');



class App {

	protected $eksamenAPI = 'https://eksamen.gk.uwap.uninettlabs.no';

	protected $feide;

	function __construct($config) {
		$this->config = $config;

		// Use the Feide Connect OAuth implementation found in lib/FeideConnect.php
		$this->feide = new FeideConnect($this->config);


		if (empty($this->config['client_id'])) {
			return $this->notConfigured();
		}


		// Implement a reset function that 
		if (isset($_REQUEST['reset']) && $_REQUEST['reset'] === '1') {
			return $this->reset();
		}

		$login = (isset($_REQUEST['login']) && $_REQUEST['login'] === '1');

		$this->feide->callback();
		$token = $this->feide->getToken($login);

		if ($token === null) {
			return $this->ready();
		} 

		$this->show($token);

	}

	protected function reset() {
		$this->feide->reset();
		$this->feide->redirect($this->config['redirect_uri']);
	}

	protected function notConfigured() {

		$data = array(
			'redirect_uri' => $this->getCurrentURL(),
		);
		$this->loadTemplate('notConfigured', $data);

	}

	protected function ready() {

		$data = array(
			'config' => $this->config,
		);
		$this->loadTemplate('ready', $data);


	}


	protected function show($token) {

		$userinfo = $this->feide->getUserInfo();
		$eksamensdata = $this->feide->get($this->eksamenAPI . '/eksamen/hentResultater');
		$data = array(
			'redirect_uri' => $this->getCurrentURL(),
			'token' => $token,
			'config' => $this->config,
			'userinfo' => $userinfo,
			'eksamensdata' => $eksamensdata,
		);
		$this->loadTemplate('main', $data);


	}



	public static function getCurrentURL () {
	    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	    if ($_SERVER["SERVER_PORT"] != "80") {
	        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	    }  else  {
	        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	    }

	    $pageURL = preg_replace('|/[^/]*$|', '/', $pageURL);
	    return $pageURL;
	}


	protected function loadTemplate($page, $data = null) {

		require_once(dirname(__FILE__) . '/templates/' . $page . '.html');

	}

}



$app = new App($CONFIG);


