<?php
require_once('inc/PHPMailer/class.phpmailer.php');
require_once('inc/PHPMailer/class.smtp.php');


if(!function_exists('mail_html')) {
	if(!function_exists('preg_upper')) {
		function preg_upper($matches) {
			return strtoupper($matches['c']);
		}
	}

	function mail_html($prenom, $nom, $email, $subject, $msg, $embed_image=null) {
		$mail = new PHPMailer();

		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';                          // Specify main and backup server
		$mail->Port = 465;                                    // Set the SMTP port
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'contact.ludotech@gmail.com';              // SMTP username
		$mail->Password = 'PinkLudonoel';                       // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'tls' also accepted

		$mail->From = 'contact.ludotech@gmail.com';
		$mail->FromName = 'LudoNoël';
		$mail->AddAddress($email, $prenom.' '.strtoupper($nom));  // Add a recipient

		$mail->IsHTML(true);                                  // Set email format to HTML
		$mail->CharSet = 'UTF-8';                             // UTF-8

		$mail->Subject = $subject;
		$mail->Body    = $msg;

		// Prepare AltBody
		// URLS to text
		$alt = preg_replace(
			"/<a\s+href=['\"](?P<url>https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9]\.[^\s]{2,})['\"].*?>(?P<text>.*?)<\/a>/",
			"\\2 (\\1)",
			$msg
		);
		$alt = preg_replace_callback("/<(strong|b)>(?P<c>.*?)<\/(strong|b)>/", 'preg_upper', $alt); // Make bold caps
		$alt = preg_replace("/<i>(.*?)<\/i>/", '*\\1*', $alt); // Stars around italic
		$mail->AltBody = strip_tags($alt); // drop the rest

		if($embed_image !== null)
			$mail->AddEmbeddedImage($embed_image, 'img');

		return $mail->Send(); // In case of error, readable at $mail->ErrorInfo
	}
}
?>
