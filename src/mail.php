<?php
namespace RusaDrako\mail;



/**
 * Отправка почтовых сообщений.
 * @version 1.1.0
 * @created 2018-09-06
 * @author Петухов Леонид <rusadrako@yandex.ru>
 */
class mail {

	/** Адресат получателя */
	protected $_to              = [];
	/** Адресат получателя (копия) */
	protected $_to_cc           = [];
	/** Адресат получателя (скрытая копия) */
	protected $_to_bcc          = [];
	/** Адресат отправителя */
	protected $_from            = '';
	/** Заголовок письма */
	protected $_subject         = '';
	/** Текст письма */
	protected $_message         = '';
	/** Файлы изображений, в тексте письма */
	protected $_message_img     = [];
	/** Прикреплённые файлы */
	protected $_file            = [];
	/** Тип сообщения html/text */
	protected $_message_type    = false;

	/** Основной разделитель */
	protected $_boundary        = '';

	/** Сформированный заголовок письма */
	protected $_header          = '';
	/** Сформированное тело письма */
	protected $_body            = '';

	/** Метка тестового режима */
	protected $_test            = false;

	/** Кодировка письма */
	protected $_charset         = 'utf-8';

	/** Системные сообщения */
	var $error_message        = [];
	/** Объект модели */
	protected static $_object   = null;





	/** */
	public function __construct() {
		# Вычищаем письмо - выставляем настройки по-умолчанию
		$this->clean();
		# Ставим кодировку по-умолчанию
		$this->charset('utf-8');
	}





	/** */
    public function __destruct() {}





	/** Вызов объекта
	* @return object Объект модели
	*/
	public static function call(...$args) {
		if (null === self::$_object) {
			self::$_object = new static(...$args);
		}
		return self::$_object;
	}





	/** Чистит строку */
	protected function _clean_line($value) {
		# Удаляем переносы
		return str_replace([chr(10), chr(13)], [' ', ' '], $value);
	}





	/* Проверяет 8-битная ли кодировка */
	protected function _is_8bit($value) {
		return preg_match('~[\x80-\xff]~', $value);
	}





	/** Проверяет 8-битная ли кодировка */
	protected function _rework_charset($value) {
		# Если указана кодировка отличная от utf-8
		if ($this->_charset != 'utf-8') {
			# Производим перекодировку заголовка и тела письма
			$value = iconv('utf-8', $this->_charset, $value);
		}
		return $value;
	}





	/** Перерабатывает адрес отправителя/получателя */
	protected function _rework_email($array) {
		$_from = [];
		foreach ($array as $k => $v) {
			if ($k != $v) {
				$_k = $this->_rework_charset($k);
				if ($this->_is_8bit($k)) {
					$_from[] = '"' . $this->_rework_8bit($_k) . '" <' . $v . '>';
				} else {
					$_from[] = '"' . $_k . '" <' . $v . '>';
				}
			} else {
				$_from[] = $v;
			}
		}
		return \implode(', ', $_from);
	}





	/** Перерабатывает строку в 8-битный вид */
	protected function _rework_8bit($value) {
		$value = '=?' . $this->_charset . '?B?' . base64_encode($value) . '?=';
		return $value;
	}





	/** Чистит текущий объект */
	public function clean() {
		# Адресат получателя
		$this->_to            = [];
		$this->_to_cc         = [];
		$this->_to_bcc        = [];
		# Адресат отправителя
		$this->_from          = [
			'test'       => 'test@test.ru'
		];
		# Заголовок письма
		$this->_subject       = '';
		# Текст письма
		$this->_message       = '';
		# Файлы изображений, в тексте письма
		$this->_message_img   = [];
		# Прикреплённые файлы
		$this->_file          = [];
		# Сформированный заголовок письма
		$this->_header        = '';
		# Сформированное тело письма
		$this->_body          = '';
		# Тип сообщения html
		$this->type_html('html');
		# Вернуть текущий объект
		return $this;
	}





	/** Тестовый режим */
	public function test($bool) {
		$this->_test = $bool;
		return $this;
	}





	/** Прописываем кодировку письма */
	public function charset($encoding) {
		$this->_charset = $encoding;
		return $this;
	}





	/** Прописываем text-тип письма */
	public function type_text() {
		$this->_message_type = 'text/plain';
		return $this;
	}





	/** Прописываем html-тип письма */
	public function type_html() {
		$this->_message_type = 'text/html';
		return $this;
	}





	/** Добавляет адресат получателя */
	protected function _to($value) {
		$this->_to = [];
		# Если передан массив
		if (is_array($value)) {
			foreach ($value as $k => $v) {
				if (is_numeric($k)) {
					$this->_to[$v] = $v;
				} else {
					$this->_to[$k] = $v;
				}
			}
		# Иначе
		} else {
			# Добавляем элемент
			$this->_to[$value] = $value;
		}
		return $this;
	}





	/** Добавляет адресат получателя (копия)
	 * @param string $mail email
	 * @param string $name Имя отправителя
	 */
	public function to_cc($mail, $name = null) {
		if ($name) {
			$this->_to_cc[$name] = $mail;
		} else {
			$this->_to_cc[$mail] = $mail;
		}
		return $this;
	}





	/** Добавляет адресат получателя (скрытая копия)
	 * @param string $mail email
	 * @param string $name Имя отправителя
	 */
	public function to_bcc($mail, $name = null) {
		if ($name) {
			$this->_to_bcc[$name] = $mail;
		} else {
			$this->_to_bcc[$mail] = $mail;
		}
		return $this;
	}





	/** Добавляет адресат отправителя
	 * @param string $mail email
	 * @param string $name Имя отправителя
	 */
	public function from($mail, $name = null) {
		if ($name) {
			$this->_from = [$name => $mail];
		} else {
			$this->_from = [$mail => $mail];
		}
		return $this;
	}





	/** Добавляет заголовок письма
	 * @param string $subject Заголовок сообщения
	 */
	public function subject($subject) {
		$this->_subject = $subject;
		return $this;
	}





	/** Добавляет текста письма
	 * @param string $message Текс сообщения
	 */
	public function message($message) {
		$this->_message = $message;
		return $this;
	}





	/** Добавляет файл изображений в тексте письма
	 * @param string $id ID картинки для добавления в тело письма
	 * @param string $file_name_full Полный путь подгружкемого файла
	 * @param string $file_name Новое имя подгружаемого файла
	 * Пример добавления картинки в тело письма
	 * <img src="cid:#тэг#">
	 */
	public function insert_img($id, $file_name_full, $file_name = false) {
		# Если файл существует
		if (file_exists($file_name_full)) {
			# Если имя файла задано
			if ($file_name) {
				# Присваеваем имя
				$_file_name = $file_name;
			# Если нет
			} else {
				# Формируем имя
				$_file_name = basename($file_name_full);
			}
			# Открываем соединение с файлом
			$file         = fopen($file_name_full,"rb");
			# Получаем mime-тип файла
			$mime_type    = mime_content_type($file_name_full);
			# Получаем содержимое файла
			$img_stream   = base64_encode(fread($file, filesize($file_name_full)));
			# Закрываем соединение с файлом
			fclose($file);
			# Добавление содержимого в письмо
			$this->insert_img_stream($id, $img_stream, $mime_type, $_file_name);
		}
		return $this;
	}





	/** Добавляет поток файла изображений в тексте письма
	 * @param string $id ID картинки для добавления в тело письма
	 * @param string $img_stream Поток файла
	 * @param string $mime_type mime-тип файла
	 * @param string $file_name Новое имя подгружаемого файла
	 * Пример добавления картинки в тело письма
	 * <img src="cid:#тэг#">
	 */
	public function insert_img_stream($id, $img_stream, $mime_type, $file_name) {
		# Добавляем информацию в массив
		$this->_message_img[$id] = [
			'name'   => $file_name,
			'type'   => $mime_type,
			'body'   => $img_stream,
		];
		return $this;
	}





	/** Добавляет прикреплённый к письму файл
	 * @param string $file_name_full Полный путь подгружкемого файла
	 * @param string $file_name Новое имя подгружаемого файла
	 */
	public function attach_file($file_name_full, $file_name) {
		# Если файл существует
		if (file_exists($file_name_full)) {
			# Если имя файла задано
			if ($file_name) {
				# Присваеваем имя
				$_file_name = $file_name;
			# Если нет
			} else {
				# Формируем имя
				$_file_name = basename($file_name_full);
			}
			# Открываем соединение с файлом
			$file          = fopen($file_name_full,"rb");
			# Получаем mime-тип файла
			$mime_type     = mime_content_type($file_name_full);
			# Получаем содержимое файла
			$file_stream   = base64_encode(fread($file, filesize($file_name_full)));
			# Закрываем соединение с файлом
			fclose($file);
			# Добавление файла к письму
			$this->attach_file_stream($file_stream, $_file_name, $mime_type);
		}
		return $this;
	}





	/** Добавляет прикреплённый к письму файл
	 * @param string $file_stream Поток файла
	 * @param string $file_name Новое имя подгружаемого файла
	 * @param string $mime_type mime-тип файла
	 */
	public function attach_file_stream($file_stream, $file_name, $mime_type = false) {
		# Если тип файла не указан
		if (!$mime_type) {
			# Ставим тип: двоичный файл без указания формата
			$mime_type = "application/octet-stream";
		}
		$this->_file[] = [
			'name'   => $file_name,
			'type'   => $mime_type,
			'body'   => $file_stream,
		];
		return $this;
	}





	/** Формируем структуру почтового сообщения */
	 function _create_mail() {
		# Генерируем разделитель письма
		$this->_boundary = '=_NextPart_' . md5(uniqid(time()));
		# Получаем заголовок
		$subject = $this->_clean_line($this->_subject);
		$subject = $this->_rework_charset($subject);
		$subject = $this->_rework_8bit($subject);
		# Получаем тело письма
		$message = $this->_rework_charset($this->_message);

		$_to_сс_full = $this->_rework_email($this->_to_cc);
		$_to_bсс_full = $this->_rework_email($this->_to_bcc);
		$_from_full = $this->_rework_email($this->_from);
		$_from = \implode(', ', $this->_from);

		# https://openrate.us/posts/1110236/
		# Стандартные служебные заголовки электоронной почты
		# Cc: — Carbon Copy — заголовок является расширением поля «To:», он указывает дополнительных получателей письма. Некоторые почтовые программы рассматривают «To:» и «Cc:» по-разному, генерируя ответ на сообщение.
		# Content-Transfer-Encoding: — MIME-заголовок, не имеет отношения к доставке почты, отвечает за то, как программа-получатель интерпретирует содержимое сообщения.
		# MIME — стандартному метод помещения в письмо нетекстовой информации (см. в Википедии).
		# Content-Type: — MIME-заголовок, сообщающий почтовой программе о типе данных, хранящихся в сообщении.
		# Date: — дата создания сообщения. Не стоит принимать на веру из-за возможности подделки или ошибки во времени у отправителя. Формат 'Mon, 07 May 2012 12:09:16 -0700'
		# Errors-To: — адрес для отсылки автоматических сообщений об ошибках. Большинство отправителей обычно хотят получать сообщения об ошибках на исходящий адрес, который используется почтовыми серверами по умолчанию.
		# From (без двоеточия) — конвертный заголовок «From» формируется на базе информации, полученной от команды MAIL FROM. Например, если отправляющая машина говорит MAIL FROM: 123@123.com, получающая машина сгенерирует строчку следующего вида: «From 123@123.com»
		# ! Конвертный заголовок создается не отправителем сообщения, а компьютером, через который прошло это сообщение.
		# From: (с двоеточием) информация об адресе отправителя, указанная самим отправителем.
		# Message-Id: — более или менее уникальный идентификатор, присваиваемый каждому сообщению, чаще всего первым почтовым сервером, который встретится у него на пути. Обычно он имеет форму «blablabla@domen.ru», где «blablabla» может быть абсолютно чем угодно, а вторая часть — имя машины, присвоившей идентификатор. Иногда, но редко, «blablabla» включает в себя имя отправителя.
		# Если структура идентификатора нарушена (пустая строка, нет знака @) или вторая часть идентификатора не является реальным интернет-сайтом, значит письмо — вероятная подделка.
		# Также Message-id: или Message-ID:.
		# In-Reply-To: — заголовок Usenet, который иногда появляется и в письмах. «In-Reply-To:» указывает идентификатор некоего сообщения, на которое данное сообщение является ответом. Этот заголовок нетипичен для писем, если только письмо действительно не является ответом на сообщение в Usenet. Спаммеры иногда им пользуются, возможно, чтобы обойти фильтрующие программы.
		# Mime-Version: или MIME-Version: — MIME-заголовок, обозначающий версию MIME-протокола, который использовался отправителем.
		# Organization: — свободный заголовок, обычно содержащий название организации, через которую отправитель сообщения получает доступ к сети.
		# Priority: — свободный заголовок, устанавливающий приоритет сообщения. Большинство программ его игнорируют. Часто используется спаммерами в форме «Priority: urgent» с целью привлечения внимания к сообщению.
		# Received: — содержит информацию о прохождении письма через почтовый сервер. Анализируя заголовок «Received:», мы видим, кто его отправил и какой путь оно проделало, попав в наш ящик.
		# Reply-To: — указывает адрес, на который следует посылать ответы. Несмотря на то, что этот заголовок имеет множество способов цивилизованного применения, он также используется спаммерами для отведения гневных ответов получателей спама от себя.
		# Return-Path: — адрес возврата в случае неудачи, когда невозможно доставить письмо по адресу назначения. Обычно совпадает с MAIL FROM. Но может и отличаться.
		# Subject: — тема сообщения.
		# To: — адрес получателя (или адреса). При этом поле «To:» может не содержать адреса получателя, так как прохождение письма базируется на конвертном заголовке «To»,  а не на заголовке сообщения «To:».
		#
		# X-заголовки
		# Это отдельный набор заголовков, начинающихся с заглавной X с последующим дефисом. Существует договоренность, согласно который X-заголовки являются нестандартными и добавляются только для дополнительной информации. Поэтому нестандартный информативный заголовок должен иметь имя, начинающееся на «X-«. Эта договоренность, однако, часто нарушается.
		# X-Confirm-Reading-To: — заголовок запрашивает автоматическое подтверждение того, что письмо было получено или прочитано. Предполагается соответствующая реакция почтовой программы, но обычно он игнорируется.
		# X-Errors-To: — заголовок указывает адрес, на который следует отсылать сообщения об ошибках. Он реже соблюдается почтовыми серверами.
		# X-Mailer: или X-mailer: — свободное поле, в котором почтовая программа, с помощью которой было создано данное сообщение, идентифицирует себя (в рекламных или подобных целях). Поскольку спам часто рассылается специальными почтовыми программами, это поле может служить ориентиром для фильтров.
		# X-Priority: — еще одно поле для приоритета сообщения.
		# X-Sender: — почтовый аналог Usenet-заголовка «Sender:». Предполагалось, что он будет доставлять более надежную информацию об отправителе, чем поле «From:», однако в действительности его так же легко подделать.
		# X-UIDL: — уникальный идентификатор, используемый в POP-протоколе при получении сообщений с сервера. Обычно он добавляется между почтовым сервером получателя и собственно почтовой программой получателя. Если письмо пришло на почтовый сервер уже с заголовком «X-UIDL:», это скорее всего спам — очевидной выгоды в использовании заголовка нет, но спаммеры иногда его добавляют.
		#
		# Еще служебные заголовки
		# List-Unsubscribe: — читайте здесь.
		# X-Mras: служебный заголовок Mail.Ru, фиксирующий наличие или отсутствие спама в письме на основе разработанной в Mail.Ru системы фильтрации спама — MRAS (Mail.Ru Anti-Spam).
		# List-id: — служебный заголовок для сбора статистики по отдельным письмам в Почтовом офисе Яндекса.
		# X-Mailru-Msgtype: — аналогичный «List-id:» заголовок для Postmaster@Mail.Ru.
		# X-PMFLAGS: и X-Distribution: — специфические заголовки программы Pegasus Mail.
		# Sender: — нетипичен для писем (обычно используется «X-Sender:»), иногда появляется в копиях Usenet-сообщений. Предполагает идентификацию отправителя, в случае с Usenet-сообщениями является более надежным, чем строчка «From:».
		# Comments: — заголовок не является стандартным, может содержать любую информацию. Чаще всего используется в виде «Comments: Authenticated sender is <rth@bieberdorf.edu>».
		# References: — редко используется в почтовых сообщениях, за исключением копий Usenet-сообщений. Он используется в Usenet для прослеживания «дерева ответов», к которому принадлежит сообщение. Если он появился в письме, то это письмо является копией Usenet-сообщения или почтовый ответ на Usenet-сообщения.
		# Newsgroups: — используется в письмах, связанных с Usenet: либо в копии отправленного в Usenet сообщения, или в ответе на эти сообщения. В первом случае он указывает конференцию, в которые сообщение было послано, а во втором — конференции, в которые было послано сообщение, на которое данное письмо является ответом.
		# Apparently-To: — сообщения с большим количеством получателей иногда имеют длинный список заголовков вида «Apparently-To: 123@domen.ru» (по одной строчке на получателя). Эти заголовки нетипичны для нормальных сообщений, они обычно являются признаком массовой рассылки.
		# Bcc: — Blind Carbon Copy, слепая копия. Если вы видите этот заголовок в полученном сообщении, значит, «что-то пошло не так». Этот заголовок используется так же, как и «Cc:», но не должен появляться в списке заголовков.
		# Префикс Resent- может быть добавлен при пересылке письма. Например, «Resent-From:» или «Resent-To:». Такие поля содержат информацию, добавленную тем, кто переслал сообщение:
		# Поле «From:» содержит адрес первоначального отправителя.
		# «Resent-From:» — адрес переславшего.
		$headers[] = 'Content-Transfer-Encoding: base64';
		$headers[] = 'Message-ID: ' . time() . '.' . md5(time().microtime()) . '.' . $_from;			# Message-Id: — более или менее уникальный идентификатор, присваиваемый каждому сообщению, чаще всего первым почтовым сервером, который встретится у него на пути. Обычно он имеет форму «blablabla@domen.ru», где «blablabla» может быть абсолютно чем угодно, а вторая часть — имя машины, присвоившей идентификатор. Иногда, но редко, «blablabla» включает в себя имя отправителя.
//		$headers[] = 'To: ' . $this->_rework_email($this->_to);	# To: — адрес получателя (или адреса). При этом поле «To:» может не содержать адреса получателя, так как прохождение письма базируется на конвертном заголовке «To», а не на заголовке сообщения «To:».
//		$headers[] = 'To: ';	# To: — адрес получателя (или адреса). При этом поле «To:» может не содержать адреса получателя, так как прохождение письма базируется на конвертном заголовке «To», а не на заголовке сообщения «To:».
		# копия сообщения на этот адрес
		if ($_to_сс_full) {
			$headers[] = "CC: {$_to_сс_full}";
		}
		# скрытая копия сообщения на этот
		if ($_to_bсс_full) {
			$headers[] = "BCC: {$_to_bсс_full}";
		}
		$headers[] = 'From: ' . $_from_full;				# From: (с двоеточием) информация об адресе отправителя, указанная самим отправителем.
		$headers[] = 'Subject: ' . $subject;				# Subject: — тема сообщения.
		$headers[] = 'Date: ' . date('Y-m-d H:i:s');		# Date: — дата создания сообщения. Не стоит принимать на веру из-за возможности подделки или ошибки во времени у отправителя.
		$headers[] = 'Reply-To: '. $_from;					# Reply-To: — указывает адрес, на который следует посылать ответы. Несмотря на то, что этот заголовок имеет множество способов цивилизованного применения, он также используется спаммерами для отведения гневных ответов получателей спама от себя.
		$headers[] = 'Errors-To: '. $_from;					# Errors-To: — адрес для отсылки автоматических сообщений об ошибках. Большинство отправителей обычно хотят получать сообщения об ошибках на исходящий адрес, который используется почтовыми серверами по умолчанию.

		$headers[] = 'Return-Path: '. $_from;				# Return-Path: — адрес возврата в случае неудачи, когда невозможно доставить письмо по адресу назначения. Обычно совпадает с MAIL FROM. Но может и отличаться.
		$headers[] = 'Mime-Version: 1.0';					# Mime-Version: или MIME-Version: — MIME-заголовок, обозначающий версию MIME-протокола, который использовался отправителем.
		$headers[] = 'X-Mailer: PHP/' . phpversion();		# X-Mailer: или X-mailer: — свободное поле, в котором почтовая программа, с помощью которой было создано данное сообщение, идентифицирует себя (в рекламных или подобных целях). Поскольку спам часто рассылается специальными почтовыми программами, это поле может служить ориентиром для фильтров.
		$headers[] = 'Sensitivity: Normal';					# Для заголовка Sensitivity (пометка) доступны следующие значения: Normal — обычное; Personal — личное; Private — частное; Company-Confidential — ДСП

		# Важность
		# Для X-Priority, X-MSMail-Priority и Importance (важность) доступны следующие значения или их числовые коды: Low — низкая (5); Normal — обычная (3); High — высокая (1)
		$headers[] = 'X-Priority: 1 (Higuest)';				# Priority: — свободный заголовок, устанавливающий приоритет сообщения. Большинство программ его игнорируют. Часто используется спаммерами в форме «Priority: urgent» с целью привлечения внимания к сообщению.
		$headers[] = 'X-MSMail-Priority: High';
		$headers[] = 'Importance: High';
		$headers[] = 'Content-Type: multipart/related; boundary="' . $this->_boundary . '"';		# Content-Type: — MIME-заголовок, сообщающий почтовой программе о типе данных, хранящихся в сообщении.

		# Формируем тело письма (основной текст)
		$body[] = '--' . $this->_boundary;
		$body[] = 'Content-type: ' . $this->_message_type . '; charset="' . $this->_charset . '"';
		$body[] = 'Content-Transfer-Encoding: 8bit';		# Content-Transfer-Encoding: — MIME-заголовок, не имеет отношения к доставке почты, отвечает за то, как программа-получатель интерпретирует содержимое сообщения.
		$body[] = '';
		$body[] = $message;
		$body[] = '';

		# Если есть файлы-картинки, которые требуется вставить в тело письма
		if (!empty($this->_message_img)) {
			# Проходим по массиву файлов
			foreach ($this->_message_img as $k => $v) {
				$body[] = '--' . $this->_boundary;
				$body[] = 'Content-Type: ' . $v['type'] . '; name="' . $v['name'] . '"';
				$body[] = 'Content-Transfer-Encoding: base64';
				$body[] = 'Content-ID: ' . $k;
				$body[] = '';
				$body[] = $v['body'];
				$body[] = '';
			}
		}

		# Если есть файлы, которые требуется прикрепить к письму
		if (!empty($this->_file)) {
			foreach ($this->_file as $k => $v) {
				$body[] = '--' . $this->_boundary;
				$body[] = 'Content-Type: ' . $v['type'] . '; name="' . $v['name'] . '"';
				$body[] = 'Content-Transfer-Encoding: base64';
				$body[] = 'Content-Disposition: attachment; filename="' . $v['name'] . '"';
				$body[] = '';
				$body[] = $v['body'];
				$body[] = '';
			}
		}

		$body[] = '--' . $this->_boundary . '--';

		$glue = "\r\n";
		$this->_header   = implode($glue, $headers);
		$this->_body     = implode($glue, $body);
	}





	/** Отправляет письмо */
	public function send($to) {
		$this->_to($to);
		# Генерируем тело письма
		$this->_create_mail();
		$mail_list = $this->_rework_email($this->_to);
		$str_mail_list = str_replace(['<', '>'], ['&#139', '&#155'], $mail_list);
		# Значение результата по-умолчанию
		$result = false;
		# Тестовый режим
		if ($this->_test) {
			echo $this->error_message[] = $str_mail_list . " - Письмо ушло!";
			$this->_mail_info($str_mail_list);
			$result = true;
		} else {
			if (mail($mail_list, $this->_subject, $this->_body, $this->_header)) {
				$this->error_message[] = $str_mail_list . " - Письмо ушло!";
				$result = true;
			} else {
 				$this->error_message[] = $str_mail_list . " - Письмо не ушло!";
				$result = false;
			};
		}
		# Возвращаем результат
		return $result;
	}





	/** Отправка письма - вывод инфы */
	 function _mail_info($str_mail_list) {
		echo '<pre style="background: #ffb; color: #000;">';
		echo str_replace(['<', '>'], ['&#139', '&#155'], $str_mail_list);
		echo '<hr>';
		echo str_replace(['<', '>'], ['&#139', '&#155'], $this->_header);
		echo '<hr>';
		echo str_replace(['<', '>'], ['&#139', '&#155'], $this->_body);
		echo '<hr>';
		echo '</pre>';
	}





/**/
}
