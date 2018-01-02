<?php
 /**
 * Скрипт распределения ресурсов
 * Site: http://bezramok-tlt.ru
 * Проверяем права на чтение данных,
 * только для зарегистрированных пользователей
 */

 //Проверяем зашел ли пользователь
 if($user === false){
 	echo '<div class="alert alert-danger"><h3 class="text-center">Доступ закрыт, Вы не вошли в систему!</h3></div>'."\n";
 }
 if($user === true) {
 	echo '<div class="alert alert-success"><h3 class="text-center">Поздравляю, Вы вошли в систему!</h3>'."\n";
 	echo '<p class="text-center"><a href="'.BEZ_HOST.'?mode=auth&exit=true" class="btn btn-warning">ВЫЙТИ</a></p></div>';
 }
 ?>
	