<?php

namespace Facebook;

require_once('FacebookRedirectLoginHelper.php');

class MyFacebookRedirectLoginHelper extends \Facebook\FacebookRedirectLoginHelper
{
	protected function storeState($state)
	{
		$ci =& get_instance();
		$ci->session->set_userdata('fb_state', $state);
	}

	protected function loadState()
	{
		$this->state = sess('fb_state');
		return $this->state;
	}
}