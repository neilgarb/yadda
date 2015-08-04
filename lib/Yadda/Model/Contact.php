<?php

class Yadda_Model_Contact {
	
	/**
	 * Sends a contact email.
	 * 
	 * @param string $name
	 * @param string $email
	 * @param string $comments
	 * @throws Yadda_Model_Exception
	 * @return void
	 */
	public static function send($name, $email, $comments) {
		// validate - name
		if (empty($name)) {
			throw new Yadda_Model_Exception('Please provide your name.');
		}
		
		// validate - email
		if (empty($email)) {
			throw new Yadda_Model_Exception('Please provide your email address.');
		} else {
			$validator = new Zend_Validate_EmailAddress();
			if (!$validator->isValid($email)) {
				throw new Yadda_Model_Exception('Please provide a valid email address.');
			}
		}
		
		// validate - comments
		if (empty($comments)) {
			throw new Yadda_Model_Exception('Please let us know what\'s on your mind.');
		}
		$comments = trim(preg_replace('#\r?\n#', "\n", $comments));
		
		// email the user
		try {
			$body =
				'Hi '.$name."\n\n".
				'Thanks for contacting yadda.'."\n\n".
				'We\'ll get back to you as soon as is humanly possible.'."\n\n".
				'You wrote:'."\n\n".
				'"""'."\n".
				$comments."\n".
				'"""'."\n\n".
				'Regards,'."\n\n".
				'--'."\n".
				'yadda.'."\n".
				'http://yadda.co.za/';
			$mail = new Zend_Mail('utf-8');
			$mail->setSubject('Thanks for contacting us');
			$mail->setFrom('no-reply@yadda.co.za', 'yadda.');
			$mail->addTo($email, $name);
			$mail->setBodyText($body);
			$mail->send();
		} catch (Zend_Mail_Exception $e) {
			throw new Yadda_Model_Exception(
				'An error occurred while trying to submit your comments. '.
				'Please make sure you\'ve filled in your details correctly.');
		}
		
		// email yadda.
		try {
			$mail = new Zend_Mail('utf-8');
			$mail->setSubject('Contact form');
			$mail->setFrom($email, $name);
			$mail->addTo('contact@yadda.co.za');
			$mail->setBodyText($comments);
			$mail->send();
		} catch (Zend_Mail_Exception $e) {
			throw new Yadda_Model_Exception(
				'Oops, this is embarrassing. We managed to send you a '.
				'confirmation email but something went wrong when we tried to '.
				'log your comments in our system. Please try one more time - '.
				'if it fails then mail us directly at contact@yadda.co.za.');
		}
	}
}