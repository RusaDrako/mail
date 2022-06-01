<?php
namespace RusaDrako\mail;



/**
 * Расширение функционала для mail
 */
class mail_addon extends mail {



	/** Отправляет простое письмо по списку */
	public function send_mail_list($mail_array, $mail_from = '', $subject = '', $message = '') {
		$this
				->from($mail_from)
				->subject($subject)
				->message($message)
				;

		if (!is_array($mail_array)) {
			$mail_array = [$mail_array];
		}

		if (count($mail_array) > 0) {
			foreach($mail_array as $v) {
				$this
						->send($м)
						;
			}
		}
	}





	/** Отправляет простое письмо */
	public function send_mail($to, $from, $subject, $message) {
		$this
				->from($from)
				->subject($subject)
				->message($message, false);
		return $this->send($to);
	}





/**/
}
