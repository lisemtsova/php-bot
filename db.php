<?php
$host = "localhost";
$username = "lionssat_botdb";
$password = "UvBmtX%3%iAI";
$dbname = "lionssat_botdb";

//создаем соединение|создаем экземпляр класса mysqli и передаем в него данные
$conn = new mysqli($host, $username, $password, $dbname);

//проверяем соединение|метод connect_error возвращает описание ошибки, если соединение не установлено
if ($connection->connect_error) {
    //прекращение скрипта немедленно прекращается, а перед этим выводится строка 
    die("Connection failed: " . $connection->connect_error);

} else echo "Connection succesful";
