<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $first_name
 * @property string $username
 * @property string $language_code
 */
class TelegramUser extends Model
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
    protected $table = 'user';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'username',
        'language_code',
    ];
}