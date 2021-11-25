<?php
function isCaptchaValid($response) {
	if($_SERVER['REMOTE_ADDR'] === '::1' || LOCAL || (DEBUG && $_SERVER['REMOTE_ADDR'] === '193.52.24.26')) return true;
	if (!preg_match('/^[a-zA-Z0-9-_]+$/', $response)) return false;
	
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array('secret' => '6Leh3xATAAAAAAnA-UYDR_ucxq_KtU07NjZyvqZj', 'response' => $response);
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		),
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);

	$res = json_decode($result, true);
	return ($res['success'] == 'true');
}
?>
