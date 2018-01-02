<?php
	/**
	* Главный файл (переключатель)
	* Site: http://bezramok-tlt.ru
	* Регистрация пользователя письмом
	*/

	//Запускаем сессию
	session_start();

	//Устанавливаем кодировку и вывод всех ошибок
	header('Content-Type: text/html; charset=UTF8');
	error_reporting(E_ALL);

	//Включаем буферизацию содержимого
	ob_start();

	//Определяем переменную для переключателя
	$mode = isset($_GET['mode'])  ? $_GET['mode'] : false;
	$user = isset($_SESSION['user']) ? $_SESSION['user'] : false;
	$err = array();


	//Устанавливаем ключ защиты
	define('BEZ_KEY', true);
	 
	//Подключаем конфигурационный файл
	include './config.php';
	 
	//Подключаем скрипт с функциями
	include './func/funct.php';

	//подключаем MySQL
	include './bd/bd.php';

	switch($mode)
	{
		//Подключаем обработчик с формой регистрации
		case 'reg':
			include './scripts/reg/reg.php';
			include './scripts/reg/reg_form.html';
		break;
		
		//Подключаем обработчик с формой авторизации
		case 'auth':
			include './scripts/auth/auth.php';
			include './scripts/auth/auth_form.html';
			include './scripts/auth/show.php';
		break;

		//Восстановление пароля
		case 'reminder';
			include './scripts/reminder/reminder.php';
			//Выводим нужную форму для пользователя
			//Проверяем что не проходит по ссылке после востановления
			if(isset($_GET['key']))
			{
				//Проверяем есть ли такой ключ
				$sql = 'SELECT COUNT(*) AS `total`
						FROM `'. BEZ_DBPREFIX .'reg`
						WHERE `active_hex` = :active_hex';

				//Подготавливаем PDO выражение для SQL запроса
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':active_hex', $_GET['key'], PDO::PARAM_STR);
				if($stmt->execute())
				{
					$row = $stmt->fetch(PDO::FETCH_ASSOC);

					//Проверяем что вернул запрос
					if($row['total'] == 0)
						include './scripts/reminder/form_reminder.html';
					else
					{
						//Отправляем на главную страницу
						//header('Location: '. BEZ_HOST);
						//exit;
						include './scripts/reminder/new_pass_form.html';
					}
				}
			}
			else
			{
				if(!isset($_GET['newpass']))
					include './scripts/reminder/form_reminder.html';
			//	include './scripts/reminder/new_pass_form.html';
			}
		break;
    
	}
    
	//Получаем данные с буфера
	$content = ob_get_contents();
	ob_end_clean();

	//Подключаем наш шаблон
	include './html/index.html';
?>			