<?php

namespace App\Http\Controllers;


use App\Models\AdminCookie;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class utils
{
    static public function sendMessage ($chat_id, $text) {
        $token = env("TELEGRAM_BOT_TOKEN"); // Токен бота
        $url = "https://api.telegram.org/bot$token/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $chat_id,
            'text' => $text,
            "reply_markup" => [
                "remove_keyboard" => true
            ]
        ]);

        if ($response->ok()) return 1;
        else return 0;
    }

    static public function requestFullname ($chat_id) {
        $token = env("TELEGRAM_BOT_TOKEN");
        $url = "https://api.telegram.org/bot$token/sendMessage";

        Http::post($url, [
            'chat_id' => $chat_id,
            'text' => "Телефон успешно обновлен.",
            "reply_markup" => [
                "remove_keyboard" => true,
            ]
        ]);

        $response = Http::post($url, [
            'chat_id' => $chat_id,
            'text' => "Отправьте своё ФИО в формате 'Фамилия Имя Отчество'",
            "reply_markup" => [
                "inline_keyboard" => [
                    [
                        [
                            "text" => "Пропустить",
                            "callback_data" => "refuse_fullname"
                        ]
                    ]
                ],
            ]
        ]);

        if ($response->ok()) return 1;
        else return 0;
    }


    public static function isSafe(string $botToken, string $initData): bool
    {
        [$checksum, $sortedInitData] = self::convertInitData($initData);
        $secretKey                   = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $hash                        = bin2hex(hash_hmac('sha256', $sortedInitData, $secretKey, true));

        return 0 === strcmp($hash, $checksum);
    }

    private static function convertInitData(string $initData): array
    {
        $initDataArray = explode('&', rawurldecode($initData));
        $needle        = 'hash=';
        $hash          = '';

        foreach ($initDataArray as &$data) {
            if (substr($data, 0, \strlen($needle)) === $needle) {
                $hash = substr_replace($data, '', 0, \strlen($needle));
                $data = null;
            }
        }
        $initDataArray = array_filter($initDataArray);
        sort($initDataArray);

        return [$hash, implode("\n", $initDataArray)];
    }

    public static function getSettings()
    {
        return json_decode(file_get_contents(storage_path('app/settings.json')), true);
    }

    public static function updateSettings($param, $arg)
    {
        $settings = self::getSettings();
        $settings[$param] = $arg;
        file_put_contents(storage_path('app/settings.json'), json_encode($settings));
        return true;
    }

    public static function returnToAdmin ($menu, $user, $text) {
        $user->step = "admin_menu";
        $user->save();

        $keyboard = [];
        foreach ($menu["menu"] as $button) $keyboard[] = ["text" => $button["name"]];
        $keyboard = array_chunk($keyboard, 2);

        $token = env("TELEGRAM_BOT_TOKEN");

        $url = "https://api.telegram.org/bot$token/sendMessage";
        Http::post($url, [
            'chat_id' => $user->telegram_id,
            'text' => $text,
            "reply_markup" => [
                "keyboard" => $keyboard,
            ]
        ]);
    }

    public static function answerData ($text, $request, $user, $deleteMarkup = true) {
        $token = env("TELEGRAM_BOT_TOKEN"); // Токен бота
        $editurl = "https://api.telegram.org/bot$token/editMessageReplyMarkup";
        $url = "https://api.telegram.org/bot$token/answerCallbackQuery";

        Http::post($url, [
            "callback_query_id" => $request["callback_query"]["id"],
            'text' => $text,
        ]);

        if ($deleteMarkup)
        Http::post($editurl, [
            "chat_id" => $user->telegram_id,
            "message_id" => $request["callback_query"]["message"]["message_id"],
            "reply_markup" => [
                "inline_keyboard" => [[]],
            ],
        ]);
    }
    static function gen_cookie ($user, $isadmin = false) {
        if ($isadmin) $cookieclass = AdminCookie::class;
        else $cookieclass = Cookie::class;

        do $cookie = self::gen_str(32);
        while ($cookieclass::where("cookie", $cookie)->exists());

        $cookieclass::create([
            "user_id" => $user->id,
            "cookie" => $cookie
        ]);
        return $cookie;
    }

    static public function gen_str ($length) {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';
        for($i = 0; $i < $length; $i++) {
            $random_character = $permitted_chars[mt_rand(0, strlen($permitted_chars) - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

    static function index ($class, $request) {
        $limit = 10;
        if ($request->has("limit")) $limit = $request->limit;

        $query = $class::take($limit);

        if ($request->has("sort")) $query->orderby("id", $request->sort);
        if ($request->has('datesort')) $query->orderby('id', $request->datesort);
        if ($request->has('offset')) $query->offset($request->offset);
        if ($request->has('namesort')) $query->orderby('title', $request->namesort);
        if ($request->has('blocked')) $query->whereNotNull("blocked_at");
        if ($request->has("datefrom")) {
            if ($class === Webinar::class) $query->whereDate('date', ">=", $request->datefrom);
            else $query->whereDate('created_at', ">=", $request->datefrom);
        }
        if ($request->has("dateto")) {
            if ($class === Webinar::class) $query->whereDate('date', "<=", $request->dateto);
            else $query->whereDate('created_at', "<=", $request->dateto);
        }
        if ($request->has("ip")) $query->where("ip", $request->ip);
        if ($request->has("user")) {
            $userids = User::where("id", $request->user)
                ->orWhere("name", "like", "%$request->user%")
                ->orWhere("surname", "like", "%$request->user%")
                ->orWhere("username", "like", "%$request->user%")
                ->pluck("id");
            $query->whereIn("user_id", $userids);
        }
        if ($request->has("s")) {
            if ($class === User::class)
                $query->where("telegram_id", "like", "%$request->s%")
                    ->orWhere("fullname", "like", "%$request->s%")
                    ->orWhere("username", "like", "%$request->s%");
            else $query->where("title", "like", "%$request->s%");
        }
        $countpage = ceil($query->count()/$limit);
        if ($request->has('page') and $limit) $query->skip(($request->page - 1) * $limit);

        $response["data"] = $query->get();
        $response["count"] = $countpage;

        return $response;
    }
}
