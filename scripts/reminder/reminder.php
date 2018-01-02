<?php
 /**
 * Обработчик формы восстановления пароля
 * Site: http://bezramok-tlt.ru
 * Авторизация пользователя
 */

 //Ключ защиты
 if(!defined('BEZ_KEY'))
 {
     header("HTTP/1.1 404 Not Found");
     exit(file_get_contents('./../404.html'));
 }
 
 //Выводим сообщение об отправки ссылки для восстановления пароля
 if(isset($_GET['send']) and $_GET['send'] == 'ok')
 	echo '<div class="alert alert-success"><h4 class="text-center">Ваш запрос на восстановление пароля отправлен на указаный вами email!</h4></div>';

  //Выводим сообщение об успешно смене пароля
 	if(isset($_GET['newpass']) and $_GET['newpass'] == 'ok')
 		echo '<div class="alert alert-success"><h4 class="text-center">Ваш пароль успешно изменен, проверьте свой email!</h4></div>';

 //Если нажата кнопка восстановить пароль утюжим переменные 
if(isset($_POST['reminder'])){
	
	//Если email существует, то проверяем есть ли он в нашей базе
	if(emailValid($_POST['email'])){
		//Запрос на выборку аккаунта для восстановления
		$sql = 'SELECT * FROM `'. BEZ_DBPREFIX .'reg`
						WHERE `status` = 1
						AND `login` = :email';

		//Подготавливаем PDO выражение для SQL запроса
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
		if($stmt->execute())
		{
			//Получаем ответ от MySQL
			$rows = $stmt->fetch(PDO::FETCH_ASSOC);
			
			//Проверяем что такой email есть
			if(!empty($rows))
			{
				//Шлем письмо для восстановления пароля
				$title = 'Вы запросили восстановление пароля на http://bezramok-tlt.ru';
				$message = 'Для смены пароля Вам нужно пройти по ссылке <a href="'. BEZ_HOST .'?mode=reminder&key='. $rows['active_hex'] .'">'. BEZ_HOST .'?mode=reminder&key='. $rows['active_hex'] .'</a>';
					
				sendMessageMail($email, BEZ_MAIL_AUTOR, $title, $message);
					
				//Перенаправляем пользователя на нужную нам страницу
				header('Location:'. BEZ_HOST .'?mode=reminder&send=ok');
				exit;
			}
			else
			{
				echo showErrorMessage('Нет такого пользователя!');
			}
		}
		else
		{
				echo showErrorMessage('Чтото пошло не так :(');
		}

	}
	else
	{
		echo showErrorMessage('Не верные данные!');
	}

}

//Если пользователь сменил пароль
if(isset($_POST['newPass']))
{
	//Утюжим переменные
	if(empty($_POST['pass']))
		$err[] = 'Поле Пароль не может быть пустым';
	
	if(empty($_POST['pass2']))
		$err[] = 'Поле Подтверждения пароля не может быть пустым';
	
	//Проверяем равенство паролей
	if($_POST['pass'] != $_POST['pass2'])
		$err[] = 'Пароли не совподают!';
	
	//Проверяем наличие ошибок и выводим пользователю
	if(count($err) > 0)
		echo showErrorMessage($err);
	else
	{
		//Получаем данные о пользователе
		$sql = 'SELECT * FROM `'. BEZ_DBPREFIX .'reg`
						WHERE `status` = 1
						AND `active_hex` = :active_hex';

		//Подготавливаем PDO выражение для SQL запроса
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':active_hex', $_GET['key'], PDO::PARAM_STR);
		if($stmt->execute())
		{
			//Получаем ответ от MySQL
			$rows = $stmt->fetch(PDO::FETCH_ASSOC);
			
			//Солим пароль
			$pass = md5(md5($_POST['pass']).$rows['salt']);
			
			//Создаем новый active_hex для защиты
			$active_hex = md5($pass);

			//Обновляем данные в таблице
			$sql = 'UPDATE `'. BEZ_DBPREFIX .'reg`
				SET 
					`pass` = :pass, 
					`active_hex` = :active_hex
				WHERE `id` = '. $rows['id'];
			
			//Подготавливаем PDO выражение для SQL запроса
			$stmt = $db->prepare($sql);
			
			//Если запрос выполнился
			if($stmt->execute(array(':pass' => $pass, ':active_hex' => $active_hex)))
			{
					//Отправляем сообщение на почту об успешной смене пароля
					$title = 'Вы успешно сменили пароль на http://bezramok-tlt.ru';
					$message = 'Вы успешно сменили пароль на '. $_POST['pass'] .'
					<p>для входа в систему перейдите по ссылке <a href="'. BEZ_HOST .'?mode=auth">'. BEZ_HOST .'?mode=auth</a></p>';
						
					sendMessageMail($email, BEZ_MAIL_AUTOR, $title, $message);
						
					//Перенаправляем пользователя на нужную нам страницу
					header('Location:'. BEZ_HOST .'?mode=reminder&newpass=ok');
					exit;
			}

		}
	}
	
}

?>