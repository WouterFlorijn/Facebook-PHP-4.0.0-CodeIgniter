# Facebook-PHP-4.0.0-CodeIgniter
This repository contains the Facebook PHP SDK v4.0.0 and a wrapper class that can be easily loaded and used in CodeIgniter.

<h2>Installation</h2>

<h3>Download</h3>

Download the files and move them to your libraries folder (or optionally a subfolder).

<h3>Config</h3>

Create a `facebook.php` config file in your config folder. This file should contain the following information:

```
$config['fb_api_id']     = 'YOUR API ID';
$config['fb_app_secret'] = 'YOUR APP SECRET';
```

<h2>Usage</h2>

The library is very useful for Facebook login and registration.

First thing we need to to is load it like any other library:

```
$this->load->library('facebook', array('redirect_url' => 'REDIRECT URL'));
```

If the library is placed in a subfolder, include the folder name as well.
Set the redirect url to wherever you want to process the data (Ex: `site_url('facebook/completed')`).

Now you can access the library using `$this->facebook->some_method()`.

<h3>Example registration</h3>

This example shows how to allow users to create accounts using Facebook.

```
class Login extends CI_Controller
{
  // Log in with Facebook.
	public function facebook()
	{
		$this->load->library('facebook/facebook', array('redirect_url' => site_url('login/facebook_done')));

		$has_session = $this->facebook->initialize_session();

		if ($has_session)
			$this->facebook_done(true);
		else
			redirect($this->facebook->get_login_url(array('public_profile', 'email')));
	}

	// Complete Facebook log in.
	public function facebook_done($has_session = false)
	{
		$this->load->library('facebook/facebook', array('redirect_url' => site_url('login/facebook_done')));

		if (!$has_session)
			$has_session = $this->facebook->initialize_session();

		if ($has_session)
		{
			$user = $this->facebook->api('/me');
			// Now enter user data into your database etc.
			// BE SURE TO STORE THE FACEBOOK ID OF THE USER.
		}
		else
		{
		  // Show an error.
		}

		redirect('somewhere');
	}
```

A log in feature can be established in a similar way, by verifying that a user exists in the database (by comparing the Facebook ID).
