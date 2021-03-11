# RD_Mail

Отправка сообщения по mail

## Отправка сообщения

```php
RD_Mail::call()
	->from($mail_from, $mail_from_name)
	->subject($subject)
	->message($message)
	->send($mail_to)
	;
```

- **$mail_from** - Почтовый ящик отправителя
- **$mail_from_name** - Имя отправителя
- **$subject** - Заголовок письма
- **$message** - Тело письма
- **$mail_to** - Почтовый ящик получателя


## Прикрепление файла

```php
	->attach_file($file_name_full, $file_name)
```

- **$file_name_full** - Путь к прикрепляемуему файлу
- **$file_name** - Имя файла в письме

## Добавление картинки в тело письма

```php
	->insert_img($id, $file_name_full, $file_name)
```

- **$id** - ID картинки для добавления в тело письма
- **$file_name_full** - Путь к прикрепляемуему файлу
- **$file_name** - Имя файла в письме


```html
	<img src="cid:#тэг#">
```

- **#тэг#** - ID картинки для добавления в тело письма

## Тестовый режим

В тестовом режиме сформированное письмо не отправляется, а выводится на экран

```php
	->test($test)
```
