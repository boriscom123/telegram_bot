<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $room
 * @property string $language_code
 * @property string $last_command
 * @property string $created_at
 * @property string $updated_at
 */
class TelegramUserInfo extends Model
{
    /**
     * The database connection that should be used by the model.
     * Соединение с БД, которое должно использоваться моделью.
     *
     * @var string
     */
    protected $connection = 'global';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_info';

    public $timestamps = true;

    protected $dates = ['created_at', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'room',
        'language_code',
        'last_command',
    ];
}
