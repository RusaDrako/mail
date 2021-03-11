<?php
require_once('../src/autoload.php');

echo '<pre>';
print_r($_POST);
echo '</pre>';

$mail = isset($_POST['mail']) ? $_POST['mail'] : '';
$test = isset($_POST['test']) ? $_POST['test'] : true;
$subject = isset($_POST['subject']) ? $_POST['subject'] : 'Заголовок тестового сообщения';
$message = isset($_POST['message']) ? $_POST['message'] : 'Тестовое сообщение';


if (isset($_POST['action']) && $_POST['action'] == 'send') {
	$test = $_POST['test'];
	$mail = $_POST['mail'];
	$subject = $_POST['subject'];
	$message = $_POST['message'];
	# Отправка сообщения
	RD_Mail::call()
		->test($test)
		->from('test1@test.ru', 'Тестовый пользователь')
		->subject($subject)
		->message($message)
		->attach_file(__DIR__ . '/file/rd.png', 'test.png')
		->send($mail)
		;
}

?>



<form method="POST">
	<label><input type="checkbox" name="test" <?php if ($test) {echo 'checked';} ?>/> Тестовый режим</label>
	<br>
	<br>
	Почтовый ящик
	<br>
	<input type="text" name="mail" value="<?php echo $mail; ?>"/>
	<br>
	<br>
	Заголовок сообщения
	<br>
	<input type="text" name="subject" value="<?php echo $subject; ?>"/>
	<br>
	<br>
	Текст сообщения
	<br>
	<textarea name="message"><?php echo $message; ?></textarea>
	<br>
	<br>
	<input type="hidden" name="action" value="send"/>
	<button type="submit">Отправить сообщение</button>
</form>