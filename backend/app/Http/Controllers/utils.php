<?php

namespace App\Http\Controllers;


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
}
