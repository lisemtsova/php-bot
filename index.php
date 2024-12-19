<?php
/*
$data = '{"update_id":718609979, 
"message":{"message_id":113,"from":{"id":5113901919,"is_bot":false,"first_name":"Lisa","username":"lisemtsova","language_code":"ru"},
"chat":{"id":5113901919,"first_name":"Lisa","username":"lisemtsova","type":"private"},"date":1733933124,"text":"123"}}';
*/
require 'db.php'; //включение и выполнение файла в текущем скрипте

$token = "7550424559:AAGjkC1AMjuShoXQGU5G5iysr0HlKJR48iw";
$website = "https://api.telegram.org/bot" . $token; //строка с url объединяется с токеном для получения полного url 


//--- Меню с командами
$commands = [
    ['command' => 'start', 'description' => 'Запуск'],
    ['command' => 'menu', 'description' => 'Меню'],
];

$data = [
    'commands' => json_encode($commands)
];

$url = $website . "/setMyCommands";
file_get_contents($url . '?' . http_build_query($data));


//--- Получение данных от Telegram
$update = file_get_contents("php://input");
//--- Логи
$f = fopen('log.txt', 'w');
fwrite($f, $update);
fclose($f);
//--- Обработка данных
$update = json_decode($update, true); //декодирует строку полученную в формате json. 
//принимает строку, возвращает ассоциативный массив


if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    switch ($text) {
        case "/start":
            $text = "\tПриветствуем!\n\nЭто бот компании Разбиратор - сервиса для авторазборок.\n\nЗдесь вы можете получить больше информации по нашему сервису.";
            sendMessage($chatId, $text);
            break;
        case "/menu":
            $inline_keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => 'О компании', 'callback_data' => 'companyInfo'],
                    ],

                    [
                        ['text' => 'Выгрузка на площадки', 'callback_data' => 'unloading'],
                        ['text' => 'Отзывы', 'callback_data' => 'reviews']
                    ],

                    [
                        ['text' => 'Складской учет', 'callback_data' => 'warehouse'],
                        ['text' => 'Поддержка', 'callback_data' => 'support']
                    ],

                    [
                        ['text' => 'Ваш сайт-витрина', 'callback_data' => 'website'],
                        ['text' => 'Работа со смартфона', 'callback_data' => 'mobile']
                    ],

                    [
                        ['text' => 'Цены и тарифы', 'callback_data' => 'prices'],
                        ['text' => 'FAQ', 'callback_data' => 'FAQ']
                    ],

                    [
                        ['text' => 'Бесплатный пробный период', 'callback_data' => 'trial'],
                    ]
                ]
            ];

            $data = [
                'chat_id' => $chatId,
                'text' => "Выберите нужный раздел",
                'reply_markup' => json_encode($inline_keyboard)
            ];
            sendButtons($data);
            break;
        default:
            sendMessage($chatId, "Неизвестная команда");
            break;
    }
} elseif (isset($update['callback_query'])) {
    $chatId = $update['callback_query']['message']['chat']['id'];
    $callbackData = $update['callback_query']['data'];

    //подготавливаем запрос, используя плейсхолдер
    $stmt = $conn->prepare("SELECT `text` FROM `companyInfo` WHERE `name` = ?");
    //привязываем параметр ("s" указывает, что параметр — это строка)
    $stmt->bind_param("s", $callbackData);
    $stmt->execute(); //выполняем запрос

    $result = $stmt->get_result(); //получаем результат
    $result = $result->fetch_assoc(); //возвращает результат в виде ассоц. массива

    sendMessage($chatId, $result['response_text']);
}


//--- Возврат в бота

function sendButtons($data)
{
    global $website;
    $url = $website . "/sendMessage?" . http_build_query($data);
    file_get_contents($url);
}

function sendMessage($chatId, $text)
{
    global $website;
    $url = $website . "/sendMessage";

    $postData = [
        'chat_id' => $chatId,
        'text' => $text
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($postData)
        ]
    ];

    $context = stream_context_create($options); //создает контекст потока, который будет использоваться для выполнения HTTP-запроса
    file_get_contents($url, false, $context);
}
