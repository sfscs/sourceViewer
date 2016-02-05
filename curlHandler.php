<?php
class CurlHandler {

	protected $options = array(
		CURLOPT_RETURNTRANSFER => 1,            // return the response as a string
		CURLOPT_SSL_VERIFYPEER => FALSE,        // cheap ssl hack
		CURLOPT_TIMEOUT => 30,
		CURLOPT_FOLLOWLOCATION => TRUE 			// follow redirect (like to https instead)
	);

	protected $headers;

	protected $response;

	protected $ch;

	function __construct()
	{
	}

	function send($uri, $verb = 'GET', $body = '')
	{
		// reset response
		$this -> response = '';

		// init the options
		$options = $this -> options;
		$options[CURLOPT_URL] = $uri;

		$verb = strtoupper($verb);
		// determine request type
		switch ($verb) {
			case "POST":
				// add post query to body
				if ($body)
					$options[CURLOPT_POSTFIELDS] = $body;
				// fancy way to do a post
				$options[CURLOPT_CUSTOMREQUEST] = $verb;
			case "GET":
				break;
			default:
				exit;
		}

		// close the curl handle if its still open
		if (is_resource($this -> ch))
			curl_close($this -> ch);

		// init the curl handle
		$this -> ch = curl_init();

		// set the options
		curl_setopt_array($this -> ch, $options);
		
		// send the request
		$response = curl_exec($this -> ch);

		if ($response)
		{
			$this -> response = $response;
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function __get($name)
	{
		switch ($name) {
			case "responseBody":
				return $this -> response;
			break;
		}
	}
}