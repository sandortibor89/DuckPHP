<?php
namespace helper;

class Mail {

	private $mail_data;

	public function __construct() {
        $mail_data = [];
    }

	public function to(string ...$args): self {
		if (is_string(reset($args)) && is_string(end($args)) && $this -> is_email(reset($args))) {
			if (count($args) === 1) {
				$mail_data['to'] = reset($args);
			} elseif (count($args) === 2) {
				$mail_data['to'] = [reset($args) => end($args)];
			}
		} elseif (is_array(reset($args))) {
			//tömb esetén
		}
		return $this;
	}

	public function from(string $email): self {
		return $this;
	}

	public function cc(string $email): self {
		return $this;
	}

	public function bcc(string $email): self {
		return $this;
	}

	private function is_email(string $email = ""): bool {
		return !filter_var($email, FILTER_VALIDATE_EMAIL) === false;
	}

}
?>
