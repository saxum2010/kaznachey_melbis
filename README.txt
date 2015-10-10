#Модуль для CMS Melbis
======

#Установка
-------------
1). Разместите папку Kaznachey в каталог /pay_mod/
2). Откройте клиентское приложение Melbis Shop. Зайдите в раздел Настройки - Способы оплаты и создайте новый вариант оплаты
3). В поле HTML-код для совершения оплаты вставьте текст: <br>

<!-- BEGIN KAZNACHEY-->
<P align=center><STRONG><FONT color=#ff0000>Внимание!</FONT></STRONG> </P>
<P align=center><STRONG>Транзакция платежа осуществляется непосредственно на защищенном сайте компании  "Казначей".</STRONG></P><BR>
<P><select name="cc_types" id="cc_types"></select></P><BR>
<P align=center><INPUT onclick="document.location='./pay_go.php?type=kaznachey&amp;{PHPSESSID}'" type=button value="Оплатить"></P>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script src="./pay_mod/kaznachey/scripts.js" type="text/javascript"></script>
<!-- END KAZNACHEY-->


4). Выполните отправку данных на сервере из клиентской программы Melbis Shop. <br>

5). Зайдите по FTP на ваш сервер в директорию, где установлен магазин и откройте для редактирования файл: /pay_mod/kaznachey/payment.php 

>	public	$merchantGuid = 'ВАШ КОД МЕРЧАНТА;
>	public	$merchnatSecretKey = 'ВАШ СЕКРЕТНЫЙ КОД';