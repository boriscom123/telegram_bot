<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $user_message
 */
class TelegramUserHistory extends Model
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
    protected $table = 'user_history';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_message',
    ];
}
