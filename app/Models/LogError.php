<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LogError extends Model
{
    protected $primaryKey = "id";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'user_agent', 'message', 'created_at', 'updated_at'
    ];

    public static function insertLogError($message, $user = 'system', $agent = null){
        if($agent != null){
            $userAgent = $agent;
        }else{
            $userAgent = ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'console';
        }
        $a = new LogError([
            'id' => Str::uuid(),
            'user_agent' => $userAgent,
            'message' => $message,
        ]);
        if($a->save()){
            return 'Error code: '.$a->id;
        }

        return null;

    }

}
