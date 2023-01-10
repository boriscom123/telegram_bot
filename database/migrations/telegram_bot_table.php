<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * (string) token = 'Токен для бота полученный от BotFather' / 'Bot token received from BotFather'
     * (string) description = 'Описание бота' / 'Bot Description'
     * (string) prefix = 'Префикс таблиц' / 'Table prefix'
     */
    private array $data = [
        'telegram_bots' => [
            [
                'token' => '',
                'description' => '',
                'prefix' => '',
            ],
        ],
    ];

    /**
     * Соединение с БД, которое должно использоваться миграцией.
     *
     * @var string
     */
    protected $connection = 'global';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Таблица для хранения информации о ботах / Table for storing information about bots
         */
        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique()->comment('Token для доступа в телеграм бот');
            $table->string('description', 128)->unique()->comment('Описание бота');
            $table->string('prefix', 32)->unique()->comment('Префикс для таблиц');
        });

        foreach ($this->data['telegram_bots'] as $bot) {
            /**
             * Таблица для хранения информации данных пользователей / A table for storing user data information
             */
            Schema::create($bot['prefix'] . 'user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique()->comment('ID Пользователя в телеграм / User ID in telegram');
                $table->string('first_name', 36)->comment('Имя в телеграм');
                $table->string('username', 36)->comment('Имя пользователя в телеграм без символа @');
                $table->string('language_code', 3)->comment('Язык пользователя телеграм');
            });

            /**
             * Таблица для хранения информации дополнительный данных пользователей / A table for storing information and user data
             * Например последняя команда или установка языковых параметров / For example, the last command or language parameters
             */
            Schema::create($bot['prefix'] . 'user_info', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique()->comment('ID Пользователя в телеграм / User ID in telegram');
                $table->string('language_code', 3)->nullable()->comment('Язык пользователя если не совпадает с основным');
                $table->string('last_command', 16)->nullable()->comment('Последняя команда пользователя');
                $table->timestamps();
            });

            /**
             * Таблица для хранения истории сообщений / Table for storing message history
             * Сообщения есть входящие и исходящие / There are incoming and outgoing messages
             */
            Schema::create($bot['prefix'] . 'user_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('ID Пользователя в телеграм / User ID in telegram');
                $table->json('user_message');
            });
        }

        /**
         * После формирования таблиц добавляем данные
         *
         * Структура TelegramBot / Structure TelegramBot
         * @property integer $id
         * @property string $token
         * @property string $description
         * @property string $prefix
         *
         */
        foreach ($this->data['telegram_bots'] as $bot) {
            $telegram_bot = TelegramBot::query()->where('token', '=', $bot['token'])->first();
            if (!$telegram_bot) {
                $telegram_bot = new TelegramBot();
                $telegram_bot->token = $bot['token'];
            }
            $telegram_bot->description = $bot['description'];
            $telegram_bot->prefix = $bot['prefix'];

            $telegram_bot->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_bots');

        foreach ($this->data['telegram_bots'] as $bot) {
            Schema::dropIfExists($bot['prefix'] . 'user');
            Schema::dropIfExists($bot['prefix'] . 'user_info');
            Schema::dropIfExists($bot['prefix'] . 'user_history');
        }
    }
};
