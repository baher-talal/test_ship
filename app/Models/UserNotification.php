<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'url', 'read_at'];

    protected $dates = ['read_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Sends a notification to several users.
     *
     * @param  array|\Illuminate\Support\Collection  $userIds
     */
    public static function notify($userIds, $title, $message = null, $url = null, $type = 'info')
    {
        foreach (collect($userIds)->filter()->unique() as $id) {
            static::create([
                'user_id' => $id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
            ]);
        }
    }
}
