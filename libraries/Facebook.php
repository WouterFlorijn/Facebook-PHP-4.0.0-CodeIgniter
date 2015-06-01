<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries/facebook/Facebook/FacebookSession.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookSDKException.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookRequestException.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookAuthorizationException.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookRequest.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookResponse.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookSignedRequestFromInputHelper.php');
require_once(APPPATH . 'libraries/facebook/Facebook/MyFacebookRedirectLoginHelper.php');
require_once(APPPATH . 'libraries/facebook/Facebook/FacebookJavaScriptLoginHelper.php');
require_once(APPPATH . 'libraries/facebook/Facebook/GraphObject.php');
require_once(APPPATH . 'libraries/facebook/Facebook/GraphSessionInfo.php');

require_once(APPPATH . 'libraries/facebook/Facebook/Entities/AccessToken.php');
require_once(APPPATH . 'libraries/facebook/Facebook/Entities/SignedRequest.php');

require_once(APPPATH . 'libraries/facebook/Facebook/HttpClients/FacebookHttpable.php');
require_once(APPPATH . 'libraries/facebook/Facebook/HttpClients/FacebookCurl.php');
require_once(APPPATH . 'libraries/facebook/Facebook/HttpClients/FacebookCurlHttpClient.php');

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSignedRequestFromInputHelper;
use Facebook\MyFacebookRedirectLoginHelper;
use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\GraphObject;

use Facebook\Entities;

use Facebook\HttpClients;

class Facebook
{
	var $ci;
	var $session;
	var $helper;

	public function __construct($config)
	{
		$this->ci =& get_instance();

		// Config.
		$this->ci->config->load('facebook');
		$default_config = array(
			'fb_api_id'     => $this->ci->config->item('fb_api_id'),
			'fb_app_secret' => $this->ci->config->item('fb_app_secret'),
			'redirect_url'  => site_url()
			);
		$default_config = array_filter($default_config);
		$config = array_merge($default_config, $config);

		FacebookSession::setDefaultApplication($config['fb_api_id'], $config['fb_app_secret']);

		$this->session = false;
		$this->helper = new MyFacebookRedirectLoginHelper($config['redirect_url']);
	}

	public function get_login_url($permissions)
	{
		return $this->helper->getLoginUrl($permissions);
	}

	public function get_logout_url($redirect_url)
	{
		if ($this->_check_session())
		{
			return $this->helper->getLogoutUrl($this->session, $redirect_url);
		}
		return false;
	}

	private function _try_js_session()
	{
		// Check JavaScript session.
		$js_helper = new FacebookJavaScriptLoginHelper();
		try
		{
			$this->session = $js_helper->getSession();
		}
		catch (Exception $ex)
		{
			$this->session = false;
		}
	}

	private function _try_existing_session()
	{
		if (sess('fb_token'))
		{
			$this->session = new FacebookSession(sess('fb_token'));

			try
			{
				$this->session->validate();
			}
			catch (Exception $e)
			{
				$this->session = false;
			}
		}
	}

	private function _try_redirect_session()
	{
		try
		{
			$this->session = $this->helper->getSessionFromRedirect();
		}
		catch (Exception $ex)
		{
			$this->session = false;
		}
	}

	public function initialize_session()
	{
		// Try to get a session in various ways.
		if (!$this->_check_session())
			$this->_try_js_session();
		if (!$this->_check_session())
			$this->_try_existing_session();
		if (!$this->_check_session())
			$this->_try_redirect_session();

		// Store the session token if possible and return whether successful.
		if ($this->_check_session())
		{
			$this->ci->session->set_userdata('fb_token', $this->session->getToken());
			return true;
		}
		else
			return false;
	}

	private function _check_session()
	{
		if (!$this->session)
		{
			$this->session = false;
			return false;
		}
		try
		{
			if (!$this->session->validate())
			{
				$this->session = false;
				return false;
			}
		}
		catch (Exception $ex)
		{
			$this->session = false;
			return false;
		}
		return true;
	}

	public function api($data)
	{
		if ($this->_check_session())
		{
			try
			{
				$request  = new FacebookRequest($this->session, 'GET', $data);
				$response = $request->execute();
				$result   = $response->getGraphObject()->asArray();

				return $result;
			}
			catch(FacebookRequestException $e)
			{
				return false;
			}
		}
		return false;
	}
}
