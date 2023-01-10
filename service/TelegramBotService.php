<?php

use Models\TelegramUser;
use Models\TelegramUserHistory;
use Models\TelegramUserInfo;

class TelegramBotService
{
    /**
     * Для наглядности данные ответов представлены в массиве
     * For clarity, the response data is presented in an array
     */
    private array $data = [
        'commands' => [
            [
                'command' => '/start',
                'description' => [
                    'ru' => 'Запустить / перезапустить бота',
                    'en' => 'Start / restart bot',
                    'tr' => 'Botu başlat / yeniden başlat',
                ],
            ],
            [
                'command' => '/ru',
                'description' => [
                    'ru' => 'Изменить язык на Русский',
                    'en' => 'Change the language to Russian',
                    'tr' => 'Dili Rusça olarak değiştir',
                ],
            ],
            [
                'command' => '/en',
                'description' => [
                    'ru' => 'Изменить язык на Английский',
                    'en' => 'Change the language to English',
                    'tr' => 'Dili İngilizce\'ye değiştir',
                ],
            ],
            [
                'command' => '/tr',
                'description' => [
                    'ru' => 'Изменить язык на Турецкий',
                    'en' => 'Change the language to Turkish',
                    'tr' => 'Dili Türkçe olarak değiştir',
                ],
            ],
        ],
        'text' => [
            'default' => [
                'ru' =>
                    'Вы ввели неизвестную команду.' . PHP_EOL .
                    'Выберите необходимое действие из доступных команд.' . PHP_EOL .
                    'Посмотреть все возможные команды можно нажав на кнопку "Все команды" внизу экрана.' . PHP_EOL .
                    '👇👇👇' . PHP_EOL,
                'en' =>
                    'You have entered an unknown command.' . PHP_EOL .
                    'Select the desired action from the available commands.' . PHP_EOL .
                    'You can view all possible commands by clicking on the "All commands" button at the bottom of the screen.' . PHP_EOL .
                    '👇👇👇' . PHP_EOL,
                'tr' =>
                    'You have entered an unknown command.' . PHP_EOL .
                    'Select the desired action from the available commands.' . PHP_EOL .
                    'You can view all possible commands by clicking on the "All commands" button at the bottom of the screen.' . PHP_EOL .
                    '👇👇👇' . PHP_EOL,
            ],
            '/start' => [
                'ru' =>
                    'Добро пожаловать в TelegramBot' . PHP_EOL .
                    'Мы определили Ваш основной язык как Русский',
                'en' =>
                    'Welcome to TelegramBot' . PHP_EOL .
                    'We have identified your primary language as English',
                'tr' =>
                    'Telegram Bot\'a Hoş Geldiniz' . PHP_EOL .
                    'Ana dilinizi Türkçe olarak belirledik',
            ],
            '/ru' => [
                'ru' => 'Установлен Русский язык',
                'en' => 'The Russian language is installed',
                'tr' => 'Rusça dili ayarlandı',
            ],
            '/en' => [
                'ru' => 'Установлен Английский язык',
                'en' => 'The English language is installed',
                'tr' => 'İngilizce ayarlandı',
            ],
            '/tr' => [
                'ru' => 'Установлен Турецкий язык',
                'en' => 'The Turkish language is installed',
                'tr' => 'Türkçe dili kuruldu',
            ],
            'Все команды' => [
                'ru' => 'Список доступных команд',
                'en' => 'List of available commands',
                'tr' => 'Mevcut komutların listesi',
            ],
            'All commands' => [
                'ru' => 'Список доступных команд',
                'en' => 'List of available commands',
                'tr' => 'Mevcut komutların listesi',
            ],
            'Tüm komutlar' => [
                'ru' => 'Список доступных команд',
                'en' => 'List of available commands',
                'tr' => 'Mevcut komutların listesi',
            ],
        ],
        'keyboard' => [
            'ru' => 'Все команды',
            'en' => 'All commands',
            'tr' => 'Tüm komutlar',
        ],
        'input_field_placeholder' => [
            'ru' => 'Номер комнаты',
            'en' => 'Room number',
            'tr' => 'Oda numarası',
        ],
    ];

    /**
     * @property integer $id
     * @property string $token
     * @property string $description
     * @property string $prefix
     */
    private TelegramBot $telegram_bot;

    /**
     * @property integer $id
     * @property int $user_id
     * @property string $first_name
     * @property string $username
     * @property string $language_code
     */
    private TelegramUser $user;
    private string $url;
    private object $request;
    private string $userLanguage;

    public function __construct(TelegramBot $telegram_bot)
    {
        /**
         * Получаем данные бота из Базы Данных
         * Getting the bot data from the Database
         */
        $this->telegram_bot = $telegram_bot;

        /**
         * Формируем ссылку для отправки ответных сообщений
         * We form a link to send response messages
         */
        $this->url = "https://api.telegram.org/bot" . $this->telegram_bot->token . '/SendMessage';
    }


    /**
     * Основной метод класса для обработки входящих сообщений
     * The main method of the class for processing incoming messages
     * $request = file_get_contents('php://input');
     */
    public function run($request): void
    {
        $this->request = json_decode($request);

        /**
         * Определяем данные пользователи и если новый - добавляем данные в Базу Данных
         * We define the users data and if new, we add the data to the Database
         */

        /** @var TelegramUser $user */
        $user = TelegramUser::query()->where('user_id', '=', $this->request->message->chat->id)->first();
        if (!$user) {
            $user = new TelegramUser();
            $user->user_id = (int)$this->request->message->chat->id;
            $user->first_name = $this->request->message->chat->first_name;
            $user->username = $this->request->message->chat->username;
            $user->language_code = $this->request->message->from->language_code;
            $user->save();
        }
        $this->user = $user;
        $this->userLanguage = $this->request->message->from->language_code;

        /**
         * Получаем дополнительные данные пользователя
         * Getting additional user data
         */

        /** @var TelegramUserInfo $userInfo */
        $userInfo = TelegramUserInfo::query()->where('user_id', '=', $this->user->user_id)->first();

        /**
         * Обновляем настройки пользователя (например, изменяем язык сообщений)
         * Updating user settings (for example, changing the language of messages)
         */
        if ($userInfo) {
            $this->userLanguage = $userInfo->language_code;
        }

        /**
         * Сохраняем входящее сообщение в таблицу истории
         * We save the incoming message to the history table
         */
        $this->saveHistory($request);

        /**
         * На основе входящего сообщения реализуем логику ответа
         * Based on the incoming message, we implement the response logic
         */
        if (!$this->checkBotCommand()) {
            $this->sendMessage();
        }
    }

    /**
     * Сохраняем сообщение в Базу Данных
     * Saving the message to the Database
     */
    public function saveHistory($message): void
    {
        $userHistory = new TelegramUserHistory();
        $userHistory->user_id = $this->request->message->chat->id;
        $userHistory->user_message = $message;
        $userHistory->save();
    }

    /**
     * Отправки типового сообщения в телеграм бот
     * Sending a typical message to a telegram bot
     */
    public function sendMessage(): void
    {
        $data = [
            'chat_id' => $this->request->message->chat->id,
            'text' => 'Привет <b>' . $this->request->message->chat->first_name . '</b> ', $this->request->message->text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode($this->getKeyboard()),
        ];
        $response = Http::get($this->url, $data);

        $this->saveHistory($response);
    }

    /**
     * Проверяем наличие в сообщении команды
     * We check the presence of the command in the message
     * PHP >= 8.0
     */
    public function checkBotCommand(): bool
    {
        $is_command = false;
        $command = $this->request->message->text;
        if ($command) {
            $is_command = match ($command) {
                '/start' => $this->commandStart(),
                '/ru', '/en', '/tr' => $this->commandChangeLanguage(),
                'Все команды', 'All commands', 'Tüm komutlar' => $this->commandAllCommands(),
                default => $this->commandDefault(),
            };
        }
        if (isset($this->request->message->entities[0]->type) && $this->request->message->entities[0]->type === 'bot_command') {
            $this->saveUserCommand($command);
        }
        return $is_command;
    }

    /**
     * Для каждой команды подготавливаем метод для формирования ответного сообщения
     * For each command, we prepare a method for generating a response message
     * Команда / command = '/start'
     */
    private function commandStart(): bool
    {
        $keyboardLang = [
            [
                'text' => '/ru',
            ],
            [
                'text' => '/en',
            ],
            [
                'text' => '/tr',
            ],
        ];
        $keyboard = $this->getKeyboard();
        array_unshift($keyboard['keyboard'], $keyboardLang);
        $data = [
            'chat_id' => $this->request->message->chat->id,
            'text' => $this->data['text']['/start'][$this->userLanguage],
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode($keyboard),
        ];
        $response = Http::get($this->url, $data);

        $this->saveHistory($response);

        return $response->ok();
    }


    /**
     * Подготавливаем метод для формирования ответа по умолчанию
     * Preparing a method for generating a default response
     */
    private function commandDefault(): bool
    {
        $keyboard = $this->getKeyboard();
        $data = [
            'chat_id' => $this->request->message->chat->id,
            'text' => $this->data['text']['default'][$this->userLanguage],
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode($keyboard),
        ];
        $response = Http::get($this->url, $data);

        $this->saveHistory($response);

        return $response->ok();
    }

    /**
     * Для каждой команды подготавливаем метод для формирования ответного сообщения
     * For each command, we prepare a method for generating a response message
     * Команда / command = 'Все команды', 'All commands', 'Tüm komutlar'
     */
    private function commandAllCommands(): bool
    {
        $text = $this->data['text'][$this->request->message->text][$this->userLanguage] . PHP_EOL . PHP_EOL;
        foreach ($this->data['commands'] as $command) {
            $text .= $command['command'] . ' - ' . $command['description'][$this->userLanguage] . PHP_EOL . PHP_EOL;
        }
        $data = [
            'chat_id' => $this->request->message->chat->id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode($this->getKeyboard()),
        ];
        $response = Http::get($this->url, $data);

        $this->saveHistory($response);

        return $response->ok();
    }

    /**
     * Для каждой команды подготавливаем метод для формирования ответного сообщения
     * For each command, we prepare a method for generating a response message
     * Команда / command = '/ru', '/en', '/tr'
     */
    private function commandChangeLanguage(): bool
    {
        $this->userLanguage = mb_substr($this->request->message->text, 1);
        $data = [
            'chat_id' => $this->request->message->chat->id,
            'text' => $this->data['text'][$this->request->message->text][$this->userLanguage],
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode($this->getKeyboard()),
        ];
        $response = Http::get($this->url, $data);

        $this->saveHistory($response);

        return $response->ok();
    }

    /**
     * Сохраняем команду, выполненную пользователем
     * Saving the command executed by the user
     */
    private function saveUserCommand($command): void
    {
        /** @var TelegramUserInfo $userInfo */
        $userInfo = TelegramUserInfo::query()->where('user_id', '=', $this->user->user_id)->first();
        if (!$userInfo) {
            $userInfo = new TelegramUserInfo();
            $userInfo->user_id = (int)$this->request->message->chat->id;
        }

        /**
         * Сохраняем язык пользователя, если отличается от основного
         */
        match ($command) {
            '/ru', '/en', '/tr' => $userInfo->language_code = mb_substr($command, 1),
            default => $userInfo->last_command = $command,
        };

        $userInfo->save();
    }

    /**
     * Формируем кнопки для дополнительной клавиатуры
     * Для предоставления пользователю возможности быстрого (интерактивного) реагирования или ответа
     * Кнопки выводятся внизу экрана
     *
     * Forming buttons for an additional keyboard
     * To provide the user with the possibility of a quick (interactive) response or response
     * The buttons are displayed at the bottom of the screen
     */
    private function getKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    [
                        'text' => $this->data['keyboard'][$this->userLanguage],
                    ],
                ],
            ],
            'one_time_keyboard' => false,
            'resize_keyboard' => true,
            'input_field_placeholder' => $this->data['input_field_placeholder'][$this->userLanguage],
        ];
    }
}