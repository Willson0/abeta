<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Analytic;
use App\Models\support;
use App\Models\User;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\SupportsBasicAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Psy\Util\Json;

class WebhookController extends Controller
{
    private static $mailingConfig = [
        "admin_mailing_fullname" => "По ФИО",
        "admin_mailing_username" => "По Нику",
        "admin_mailing_id" => "По ID",
    ];

    public function tgmessage (Request $request) {
        try {
            $menu = [
                "name" => "Меню",
                "menu" => [
                    "admin_settings" => [
                        "name" => "Настройки",
                        "menu" => [
                            "admin_settings_datacollection" => [
                                "name" => "Сбор данных",
                                "menu" => [
                                    "admin_settings_datacollection_what" => [
                                        "name" => "Что собираем",
                                    ],
                                    "admin_settings_datacollection_phone" => [
                                        "name" => "Требовать телефон",
                                    ],
                                    "admin_settings_datacollection_venture" => [
                                        "name" => "Данные для венчурных сделок",
                                    ]
                                ]
                            ],
                            "admin_settings_support" => [
                                "name" => "Чат поддержки",
                            ],
                            "admin_settings_calendly" => [
                                "name" => "Синхронизация с Calendly",
                            ],
                            "admin_settings_statistics" => [
                                "name" => "Статистика",
                                "menu" => [
                                    "admin_settings_statistics_toppopular" => [
                                        "name" => "Топ популярности",
                                    ],
                                    "admin_settings_statistics_allcountusers" => [
                                        "name" => "Сколько юзеров",
                                    ],
                                    "admin_settings_statistics_countusers" => [
                                        "name" => "Статистика пользователей"
                                    ],
                                    "admin_settings_statistics_search" => [
                                        "name" => "Поиск"
                                    ],
                                ]
                            ],
                            "admin_settings_group_chat" => [
                                "name" => "Чат группы",
                                "menu" => [
                                    "admin_settings_group_chat_added" => [
                                        "name" => "Добавлены"
                                    ],
                                    "admin_settings_group_chat_blocked" => [
                                        "name" => "Заблокированы"
                                    ],
                                    "admin_settings_group_chat_requirements" => [
                                        "name" => "Требования"
                                    ]
                                ]
                            ],
                            "admin_settings_venture" => [
                                "name" => "Венчурные сделки"
                            ]
                        ],
                    ],
                    "admin_mailing" => [
                        "name" => "Рассылка"
                    ],
                    "admin_events" => [
                        "name" => "Ивенты",
                        "menu" => [
                            "admin_events_add" => [
                                "name" => "Добавить"
                            ],
                            "admin_events_actual" => [
                                "name" => "Актуальные"
                            ],
                            "admin_events_archive" => [
                                "name" => "Архив"
                            ],
                        ]
                    ],
                    "admin_materials" => [
                        "name" => "Материалы",
                        "menu" => [
                            "admin_events_add" => [
                                "name" => "Добавить"
                            ],
                            "admin_events_actual" => [
                                "name" => "Актуальные"
                            ],
                        ]
                    ],
                    "admin_edit" => [
                        "name" => "Редактировать тексты",
                    ]
                ]
            ];

        $token = env("TELEGRAM_BOT_TOKEN"); // Токен бота
        $editurl = "https://api.telegram.org/bot$token/editMessageReplyMarkup";
        $sendurl = "https://api.telegram.org/bot$token/sendMessage";

        Log::critical($request);
        if (isset($request->callback_query)) {
            $requestUser = $request->callback_query["from"];
            $user = User::where("telegram_id", "=", $requestUser["id"])->first();

            if (isset($request["callback_query"]["data"])) {
                $url = "https://api.telegram.org/bot$token/answerCallbackQuery";

                if ($request["callback_query"]["data"] == "refuse_phone") {

                    $user->step = "enter_full_name";
                    $user->save();

                    utils::answerData("Вы успешно отказались от добавления телефона\nОтправьте своё ФИО в формате 'Фамилия Имя Отчество'", $request, $user);
                    utils::requestFullname($user->telegram_id);
                }
                else if ($request["callback_query"]["data"] == "refuse_fullname") {
                    $user->step = "";
                    $user->save();


                    utils::answerData("Вы успешно отказались от добавления ФИО", $request, $user);
                    utils::sendMessage($user->telegram_id, "Все данные успешно добавлены");
                }
                else if ($request["callback_query"]["data"] == "admin_settings_datacollection_phone_accept") {
                    utils::updateSettings("require_phone", true);

                    utils::answerData("Телефон обязателен при регистрации", $request, $user);
                    utils::returnToAdmin($menu, $user, "Телефон обязателен для регистрации.");
                }
                else if ($request["callback_query"]["data"] == "admin_settings_datacollection_phone_decline") {
                    utils::updateSettings("require_phone", false);

                    utils::answerData("Телефон необязателен при регистрации", $request, $user);
                    utils::returnToAdmin($menu, $user, "Телефон необязателен для регистрации.");
                }
                else if ($request["callback_query"]["data"] == "admin_settings_datacollection_what_check") {
                    $fields = utils::getSettings()["webinar_fields"];
                    $arr = [];

                    foreach ($fields as $key => $field)
                        $arr[] = ["text" => $field, "callback_data" => "admin_settings_datacollection_what_check_".$key];
                    $arr = array_chunk($arr, 1);

                    utils::answerData("Просмотр полей формы", $request, $user);

                    $user->step = "admin_settings_datacollection_what_checking";
                    $user->save();

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Поля формы: ",
                        "reply_markup" => [
                            "inline_keyboard" => $arr,
                        ],
                    ]);
                }
                else if (preg_match('/^admin_settings_datacollection_what_check_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    $field = utils::getSettings()["webinar_fields"][$number];
                    utils::answerData("Выбрано поле $field", $request, $user);

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Поле: $field",
                        "reply_markup" => [
                            "inline_keyboard" => [
                                [
                                    ["text" => "Изменить", "callback_data" => "admin_settings_datacollection_what_edit_$number"],
                                    ["text" => "Удалить", "callback_data" => "admin_settings_datacollection_what_delete_$number"],
                                ],
                                [
                                    ["text" => "Назад", "callback_data" => "admin_settings_datacollection_what_check"],
                                ]
                            ],
                        ],
                    ]);
                }
                else if (preg_match('/^admin_settings_datacollection_what_edit_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    $field = utils::getSettings()["webinar_fields"][$number];
                    utils::answerData("Изменяется поле $field", $request, $user);

                    $user->step = "admin_settings_datacollection_what_edit_".$number;
                    $user->save();
                    utils::sendMessage($user->telegram_id, "Отправьте новое значение для поля $field: ");
                }
                else if (preg_match('/^admin_settings_datacollection_what_delete_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    $field = utils::getSettings()["webinar_fields"];
                    $old = $field[$number];

                    unset($field[$number]);
                    utils::updateSettings("webinar_fields", $field);
                    utils::answerData("Поле $old удалено", $request, $user);

                    utils::sendMessage($user->telegram_id, "Поле $old удалено");

                    list($fields, $arr, $key, $field) = $this->sendDataCollectionWhat($user, $sendurl);
                }
                else if ($request["callback_query"]["data"] == "admin_settings_datacollection_what_add") {
                    utils::answerData("Просмотр полей формы", $request, $user);

                    $user->step = "admin_settings_datacollection_what_adding";
                    $user->save();

                    utils::sendMessage($user->telegram_id, "Отправьте значение нового поля: ");
                }
                else if ($request["callback_query"]["data"] == "admin_mailing_change") {
                    utils::answerData("Изменение сообщения", $request, $user);

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Изменение сообщения.\nСтарое сообщение:\n" .
                            Cache::get("admins")[$user->id]["mailing"] . "\n\nВведите новое сообщение:",
                    ]);

                    $user->step = "admin_mailing";
                    $user->save();
                }
                else if ($this->findSubarrayByKey(self::$mailingConfig, $request["callback_query"]["data"], $result)) {
                    utils::answerData("Поиск $result", $request, $user);
                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Введите данные пользователя для поиска:",
                    ]);

                    $user->step = $request["callback_query"]["data"];
                    $user->save();
                }
                else if (preg_match('/^admin_mailing_add_\d+$/', $request["callback_query"]["data"]) ||
                        preg_match('/^admin_mailing_remove_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("+ пользователь", $request, $user, false);
                    $admins = Cache::get("admins");

                    if (str_contains($request["callback_query"]["data"], "admin_mailing_add_")) $admins[$user->id]["selected"][] = $number;
                    else unset($admins[$user->id]["selected"][array_search($number, $admins[$user->id]["selected"])]);

                    Cache::forever("admins", $admins);

                    $selectedUsers = $admins[$user->id]["selected"];
                    $search = $admins[$user->id]["search"];

                    if ($user->step === "admin_mailing_fullname") $data = User::where("fullname", "like", "%$search%")->get();
                    else if ($user->step === "admin_mailing_username") $data = User::where("username", "like", "%$search%")->get();
                    else if ($user->step === "admin_mailing_id") $data = User::where("telegram_id", "=", "$search")->get();
                    $keyboard = $this->getKeyboard($data, $selectedUsers);

                    Http::post($editurl, [
                        "chat_id" => $user->telegram_id,
                        "message_id" => $request["callback_query"]["message"]["message_id"],
                        "reply_markup" => [
                            "inline_keyboard" => $keyboard,
                        ],
                    ]);
                }
                else if ($request["callback_query"]["data"] === "admin_mailing_return") {
                    utils::answerData("Возвращение", $request, $user);

                    $this->sendMailing($user, $sendurl);
                    return response()->json(["status" => "ok"], 200);
                }
                else if ($request["callback_query"]["data"] === "admin_mailing_send") {
                    utils::answerData("Отправлено", $request, $user);
                    $admins = Cache::get("admins");

                    $users = User::whereIn("id", $admins[$user->id]["selected"])->get();
                    $webinars = Webinar::whereIn("id", $admins[$user->id]["selectedWebinars"])->get();
                    foreach ($webinars as $webinar) {
                        foreach ($webinar->users as $us) {
                            utils::sendMessage($us->telegram_id, $admins[$user->id]["mailing"]);
                        }
                    }
                    foreach ($users as $us) {
                        utils::sendMessage($us->telegram_id, $admins[$user->id]["mailing"]);
                    }
                    unset($admins[$user->id]["selected"]);
                    unset($admins[$user->id]["selectedWebinars"]);
                    unset($admins[$user->id]["mailing"]);
                    Cache::forever("admins", $admins);

                    utils::returnToAdmin($menu, $user, "Рассылка успешно отправлена пользователям");
                    return response("", 200);
                }
                else if ($request["callback_query"]["data"] === "admin_mailing_interests") {
                    utils::answerData("По интересам", $request, $user, false);

                    Http::post($editurl, [
                        "chat_id" => $user->telegram_id,
                        "message_id" => $request["callback_query"]["message"]["message_id"],
                        "reply_markup" => [
                            "inline_keyboard" => [
                                [["text" => "Выбрать материал", "callback_data" => "admin_mailing_interests_material"]],
                                [["text" => "Выбрать ивент", "callback_data" => "admin_mailing_interests_event"]],
                            ],
                        ],
                    ]);
                }
                else if ($request["callback_query"]["data"] === "admin_mailing_interests_event") {
                    utils::answerData("По интересам", $request, $user, false);
                    $data = Webinar::where("date", ">", Carbon::now())->get();

                    $selectedWebs = Cache::get("admins")[$user->id]["selectedWebinars"];

                    $keyboard = [];
                    $keyboard[] = [
                        "text" => "Назад",
                        "callback_data" => "admin_mailing_return",
                    ];
                    foreach ($data as $record) {
                        if (in_array($record->id, $selectedWebs)) {
                            $keyboard[] = [
                                "text" => "✅ " . $record->title . " (id: " . $record->id . ")",
                                "callback_data" => "admin_mailing_interests_event_remove_" . $record->id
                            ];
                        } else
                            $keyboard[] = [
                                "text" => $record->title . " (id: " . $record->id . ")",
                                "callback_data" => "admin_mailing_interests_event_add_" . $record->id
                            ];
                    }

                    $keyboard = array_chunk($keyboard, 1);

                    Http::post($editurl, [
                        "chat_id" => $user->telegram_id,
                        "message_id" => $request["callback_query"]["message"]["message_id"],
                        "reply_markup" => [
                            "inline_keyboard" => $keyboard,
                        ],
                    ]);
                }
                else if (preg_match('/^admin_mailing_interests_event_add_\d+$/', $request["callback_query"]["data"]) ||
                    preg_match('/^admin_mailing_interests_event_remove_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("+ вебинар", $request, $user, false);
                    $admins = Cache::get("admins");

                    if (str_contains($request["callback_query"]["data"], "admin_mailing_interests_event_add_")) $admins[$user->id]["selectedWebinars"][] = $number;
                    else unset($admins[$user->id]["selectedWebinars"][array_search($number, $admins[$user->id]["selectedWebinars"])]);

                    Cache::forever("admins", $admins);

                    $selectedWebs = $admins[$user->id]["selectedWebinars"];
                    $data = Webinar::where("date", ">", Carbon::now())->get();

                    $keyboard = [];
                    $keyboard[] = [
                        "text" => "Назад",
                        "callback_data" => "admin_mailing_return",
                    ];
                    foreach ($data as $record) {
                        if (in_array($record->id, $selectedWebs)) {
                            $keyboard[] = [
                                "text" => "✅ " . $record->title . " (id: " . $record->id . ")",
                                "callback_data" => "admin_mailing_interests_event_remove_" . $record->id
                            ];
                        } else
                            $keyboard[] = [
                                "text" => $record->title . " (id: " . $record->id . ")",
                                "callback_data" => "admin_mailing_interests_event_add_" . $record->id
                            ];
                    }

                    $keyboard = array_chunk($keyboard, 1);

                    Http::post($editurl, [
                        "chat_id" => $user->telegram_id,
                        "message_id" => $request["callback_query"]["message"]["message_id"],
                        "reply_markup" => [
                            "inline_keyboard" => $keyboard,
                        ],
                    ]);
                }
                else if ($request["callback_query"]["data"] === "admin_mailing_sendall") {
                    utils::answerData("Отправка всем пользователям", $request, $user, true);

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Вы уверены, что хотите отправить сообщение всем пользователям?",
                        "reply_markup" => [
                            "inline_keyboard" => [
                                [
                                    ["text" => "Да", "callback_data" => "admin_mailing_sendall_accept"],
                                    ["text" => "Нет", "callback_data" => "admin_mailing_return"],
                                ]
                            ]
                        ]
                    ]);
                }
                else if ($request["callback_query"]["data"] === "admin_mailing_sendall_accept") {
                    utils::answerData("Отправлено", $request, $user);
                    $admins = Cache::get("admins");

                    $users = User::all();
                    foreach ($users as $us) {
                        utils::sendMessage($us->telegram_id, $admins[$user->id]["mailing"]);
                    }
                    unset($admins[$user->id]["selected"]);
                    unset($admins[$user->id]["selectedWebinars"]);
                    unset($admins[$user->id]["mailing"]);
                    Cache::forever("admins", $admins);

                    utils::returnToAdmin($menu, $user, "Рассылка успешно отправлена всем пользователям");
                    return response("", 200);
                }
                else if ($request["callback_query"]["data"] === "admin_events_add_confines") {
                    utils::answerData("Отправлено", $request, $user);

                    $fields = utils::getSettings()["webinar_fields"];
                    $forbidden = Cache::get("admins")[$user->id]["forbidden"];

                    $keyboard = [];
                    $keyboard[] = [
                        "text" => "Готово",
                        "callback_data" => "admin_events_add_form",
                    ];
                    foreach ($fields as $key => $field) {
                        if (in_array($key, $forbidden)) {
                            $keyboard[] = [
                                "text" => "❌ " . $field,
                                "callback_data" => "admin_events_add_confines_remove_" . $key
                            ];
                        } else
                            $keyboard[] = [
                                "text" => $field,
                                "callback_data" => "admin_events_add_confines_add_" . $key
                            ];
                    }

                    $keyboard = array_chunk($keyboard, 1);

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Выберите поля, которые не хотите добавлять в форму регистрации на вебинар",
                        "reply_markup" => [
                            "inline_keyboard" => $keyboard,
                        ],
                    ]);
                }
                else if (preg_match('/^admin_events_add_confines_add_\d+$/', $request["callback_query"]["data"]) ||
                    preg_match('/^admin_events_add_confines_remove_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("Поле изменено", $request, $user, false);
                    $admins = Cache::get("admins");

                    if (str_contains($request["callback_query"]["data"], "admin_events_add_confines_add_")) $admins[$user->id]["forbidden"][] = $number;
                    else unset($admins[$user->id]["forbidden"][array_search($number, $admins[$user->id]["forbidden"])]);

                    Cache::forever("admins", $admins);

                    $forbidden = $admins[$user->id]["forbidden"];
                    $data = Webinar::where("date", ">", Carbon::now())->get();

                    $fields = utils::getSettings()["webinar_fields"];

                    $keyboard = [];
                    $keyboard[] = [
                        "text" => "Готово",
                        "callback_data" => "admin_events_add_form",
                    ];
                    foreach ($fields as $key => $field) {
                        if (in_array($key, $forbidden)) {
                            $keyboard[] = [
                                "text" => "❌ " . $field,
                                "callback_data" => "admin_events_add_confines_remove_" . $key
                            ];
                        } else
                            $keyboard[] = [
                                "text" => $field,
                                "callback_data" => "admin_events_add_confines_add_" . $key
                            ];
                    }
                    $keyboard = array_chunk($keyboard, 1);

                    Http::post($editurl, [
                        "chat_id" => $user->telegram_id,
                        "message_id" => $request["callback_query"]["message"]["message_id"],
                        "reply_markup" => [
                            "inline_keyboard" => $keyboard,
                        ],
                    ]);
                }
                else if ($request["callback_query"]["data"] === "admin_events_add_form") {
                    utils::answerData("Поле изменено", $request, $user);

                    $user->step = "admin_events_add_form_title";
                    $user->save();

                    utils::sendMessage($user->telegram_id, "Отправьте заголовок:");
                }
                else if (preg_match('/^admin_events_add_edit_[A-Za-z]+$/i', $request["callback_query"]["data"])) {
                    utils::answerData("Изменение поля", $request, $user);
                    utils::sendMessage($user->telegram_id, "Отправьте новое значение поля: ");

                    $user->step = $request["callback_query"]["data"];
                    $user->save();
                }
                else if ($request["callback_query"]["data"] === "admin_events_add_form_add") {
                    utils::answerData("Сохранение контента", $request, $user);
                    $admins = Cache::get("admins");

                    $fields = [];
                    foreach (utils::getSettings()["webinar_fields"] as $key => $field)
                        if (!in_array($key, $admins[$user->id]["forbidden"])) $fields[] = $field;

                    if ($admins[$user->id]["type"] === "analytics") {
                        $name = "analytics_" . time() . ".jpg";
                        $image = "analytics/images/".$name;
                        Storage::disk("public")->put($image, (string) $admins[$user->id]["eventImage"]);

                        if ($admins[$user->id]["eventPdf"]) {
                            $name = "analytics_" . time() . ".pdf";
                            $pdf = "analytics/pdf/" . $name;
                            Storage::disk("public")->put($pdf, (string)$admins[$user->id]["eventPdf"]);
                        } else $pdf = null;

                        Analytic::create([
                            "title" => $admins[$user->id]["eventTitle"],
                            "description" => $admins[$user->id]["eventDescription"],
                            "link" => $admins[$user->id]["eventLink"],
                            "image" => $image,
                            "pdf" => $pdf,
                            "locked" => 1,
                            "fields" => json_encode($fields),
                        ]);
                    } else {
                        $name = "webinar_" . time() . ".jpg";
                        $filePath = "webinars/".$name;
                        Storage::disk("public")->put($filePath, (string) $admins[$user->id]["eventImage"]);

                        $web = Webinar::create([
                            "title" => $admins[$user->id]["eventTitle"],
                            "description" => $admins[$user->id]["eventDescription"],
                            "link" => $admins[$user->id]["eventLink"],
                            "date" => $admins[$user->id]["eventDate"],
                            "image" => $filePath,
                            "fields" => json_encode($fields),
                        ]);
                    }

                    utils::returnToAdmin($menu, $user, "Новый контент был успешно добавлен!");
                }
                else if (str_contains($request["callback_query"]["data"], "admin_events_actual_tools_")) {
                    utils::answerData("Изменение страницы", $request, $user);

                    $admins = Cache::get("admins");
                    if ($request["callback_query"]["data"] == "admin_events_actual_tools_next") $admins[$user->id]["page"] += 1;
                    else $admins[$user->id]["page"] -= 1;
                    Cache::forever("admins", $admins);

                    $this->getEvents($user, Cache::get("admins")[$user->id]["old"]);
                }
                else if (preg_match('/^admin_events_actual_show_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("Вебинар №$number", $request, $user);

                    $type = Cache::get("admins")[$user->id]["type"];
                    if ($type == "analytics") {
                        $analytic = Analytic::find($number);
                        $photo = Storage::disk("public")->get($analytic->image);

                        Http::attach(
                            "photo",
                            $photo,
                            "image.jpg"
                        )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                            "chat_id" => $user->telegram_id,
                            "caption" => "Новый материал: Аналитика.\n\nЗаголовок: " . $analytic->title
                                . "\nОписание: " . $analytic->description
                                . "\nСсылка на видео: " . $analytic->link
                                . "\nПоля: " . implode(", ", json_decode($analytic->fields)),
                        ]);

                        if ($analytic->pdf) {
                            $pdf = Storage::disk("public")->get($analytic->pdf);
                            Http::attach(
                                "document",
                                $pdf,
                                "document.pdf"
                            )->post("https://api.telegram.org/bot{$token}/sendDocument", [
                                "chat_id" => $user->telegram_id,
                                "caption" => "ПДФ Документ: ",
                            ]);
                        }
                    }
                    else {
                        $webinar = Webinar::find($number);
                        $photo = Storage::disk("public")->get($webinar->image);

                        Http::attach(
                            "photo",
                            $photo,
                            "photo.jpg"
                        )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                            "chat_id" => $user->telegram_id,
                            "caption" => "Ивент $webinar->id ID: Вебинар.\n\nЗаголовок: " . $webinar->title
                                . "\nОписание: " . $webinar->description
                                . "\nСсылка: " . $webinar->link
                                . "\nДата: " . $webinar->date
                                . "\nПоля: " . implode(", ", json_decode($webinar->fields)),
                        ]);
                    }
                    $this->getEvents($user, Cache::get("admins")[$user->id]["old"]);
                }
                else if (preg_match('/^admin_events_actual_edit_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    $admins = Cache::get("admins");
                    if ($admins[$user->id]["type"] === "analytics") {
                        utils::answerData("Аналитика №$number", $request, $user);
                        $analytic = Analytic::find($number);

                        $admins[$user->id]["edit_webinar"] = $analytic->id;
                        Cache::forever("admins", $admins);

                        $this->actualEditAnalytic($user, $analytic);
                    } else {
                        utils::answerData("Вебинар №$number", $request, $user);

                        $webinar = Webinar::find($number);
                        $photo = Storage::disk("public")->get($webinar->image);

                        $admins[$user->id]["edit_webinar"] = $webinar->id;
                        Cache::forever("admins", $admins);

                        $this->actualEditWebinar($photo, $token, $user, $webinar);
                    }
                }
                else if (str_contains($request["callback_query"]["data"], "admin_events_actual_edit_")) {
                    utils::answerData("Edit", $request, $user);

                    $user->step = $request["callback_query"]["data"];
                    $user->save();

                    utils::sendMessage($user->telegram_id, "Отправьте новое значение поля:");
                }
                else if (preg_match('/^admin_events_actual_delete_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("Вебинар №$number", $request, $user);

                    $type = Cache::get("admins")[$user->id]["type"];
                    if ($type === "analytics") $webinar = Analytic::find($number);
                    else $webinar = Webinar::find($number);

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Вы уверены, что хотите удалить вебинар $webinar->title (ID: $webinar->id)?",
                        "reply_markup" => [
                            "inline_keyboard" => [
                                [
                                    ["text" => "✅ Да", "callback_data" => "admin_events_actual_delete_accept_" . $webinar->id],
                                    ["text" => "❌ Нет", "callback_data" => "admin_events_actual_delete_decline"],
                                ]
                            ]
                        ]
                    ]);
                }
                else if (preg_match('/^admin_events_actual_delete_accept_\d+$/', $request["callback_query"]["data"])) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("Вебинар №$number", $request, $user);

                    $type = Cache::get("admins")[$user->id]["type"];
                    if ($type === "analytics") $webinar = Analytic::find($number);
                    else $webinar = Webinar::find($number);

                    utils::sendMessage($user->telegram_id, "Вебинар №$webinar->id ($webinar->title) успешно удален!");
                    $webinar->delete();

                    $this->getEvents($user, Cache::get("admins")[$user->id]["old"]);
                }
                else if ($request["callback_query"]["data"] == "admin_events_actual_delete_decline") {
                    utils::answerData("Вебинар", $request, $user);
                    utils::sendMessage($user->telegram_id, "Вы отказались от удаления ивента!");

                    $this->getEvents($user, Cache::get("admins")[$user->id]["old"]);
                }
                else if ($request["callback_query"]["data"] == "admin_events_add_form_link_skip") {
                    utils::answerData("Пропустить", $request, $user);

                    $admins = cache::get("admins");
                    $admins[$user->id]["eventLink"] = null;
                    Cache::forever("admins", $admins);

                    if ($admins[$user->id]["type"] === "analytics") {
                        $user->step = "admin_events_add_form_pdf";
                        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                            "chat_id" => $user->telegram_id,
                            "text" => "Отправьте ссылку на пдф-файл: ",
                            "reply_markup" => json_encode([
                                "inline_keyboard" => [
                                    [["text" => "Пропустить", "callback_data" => "admin_events_add_form_pdf_skip"]],
                                ],
                            ])
                        ]);
                    }
                    $user->save();
                }
                else if ($request["callback_query"]["data"] == "admin_events_add_form_pdf_skip") {
                    utils::answerData("Пропустить", $request, $user);

                    $admins = cache::get("admins");
                    $admins[$user->id]["eventPdf"] = null;
                    Cache::forever("admins", $admins);

                    $user->step = "admin_events_add_form_image";
                    $user->save();
                    utils::sendMessage($user->telegram_id, "Отправьте картинку: ");
                }
                else if ($request["callback_query"]["data"] == "admin_settings_statistics_toppopular_materials") {
                    utils::answerData("Материалы по популярности", $request, $user);
                    $analytics = Analytic::orderBy("downloads", "desc")->get();

                    $list = "";
                    foreach ($analytics as $key => $analytic)
                        if (!$analytic->downloads == 0)
                        $list .= "\n" . ($key+1) . ") $analytic->title ($analytic->downloads)";

                    $str = "Топ популярности материалов по скачиванию:\n\n" . $list;
                    $str .= "\n\n*если материал не указан, то количество скачиваний равно нулю.";
                    utils::returnToAdmin($menu, $user, $str);
                }
                else if ($request["callback_query"]["data"] == "admin_settings_statistics_toppopular_events") {
                    utils::answerData("Вебинары по популярности", $request, $user);
                    $webinars = Webinar::withCount("users")->orderBy("users_count", "desc")->get();

                    $list = "";
                    foreach ($webinars as $key => $analytic)
                        if (!$analytic->users_count == 0)
                        $list .= "\n" . ($key+1) . ") $analytic->title ($analytic->users_count)";

                    $str = "Топ популярности вебинаров по количеству регистрации пользователей:\n\n" . $list;
                    $str .= "\n\n*если вебинар не указан, то количество регистраций равно нулю.";
                    utils::returnToAdmin($menu, $user, $str);
                }
                else if (str_contains($request["callback_query"]["data"], "admin_settings_statistics_search_")) {
                    utils::answerData("Поиск", $request, $user);
                    $field = str_replace("admin_settings_statistics_search_", "", $request["callback_query"]["data"]);

                    $admins = Cache::get("admins");
                    $admins[$user->id]["type"] = $field;
                    Cache::forever("admins", $admins);

                    utils::sendMessage($user->telegram_id, "Введите значение для поиска: ");
                    $user->step = "admin_settings_statistics_search";
                    $user->save();
                }
                else if (str_contains($request["callback_query"]["data"], "admin_settings_support_claim_")) {
                    $number = 0;
                    if (preg_match('/(\d+)$/', $request["callback_query"]["data"], $matches))
                        $number = (int)$matches[1];

                    utils::answerData("Support", $request, $user);

                    $support = Support::find($number);
                    $supUser = User::find($support->user_id);

                    $support->admin_id = Admin::where("telegram_id", $user->telegram_id)->first()->id;
                    $support->save();

                    $user->step = "support";
                    $user->save();
                    $supUser->step = "support";
                    $supUser->save();

                    utils::sendMessage($user->telegram_id, "Вы начали чат с $supUser->fullname.\n\nВопрос от пользователя:\n$support->text");
                    utils::sendMessage($supUser->telegram_id, "С вами начал чат администратор $user->fullname ($user->telegram_id).\n\nВаш вопрос:\n$support->text");
                }
            }
        }
        if (isset($request->message)) {
            $message = [...$request->message];

            if (!isset($message["text"])) $message["text"] = "";

            $requestUser = $message["from"];
            $user = User::where("telegram_id", "=", $requestUser["id"])->first();

            if (!$user) {

                $user = User::create([
                    "telegram_id" => $requestUser["id"],
                    "username" => $requestUser["username"] ?? "",
                    "fullname" => $requestUser["first_name"] ?? "",
                    "phone" => null,
                    "notifications" => true,
                ]);

                $url = "https://api.telegram.org/bot$token/sendMessage";
                Http::post($url, [
                    'chat_id' => $user->telegram_id,
                    'text' => "Отправьте номер вашего телефона",
                    "reply_markup" => [
                        "keyboard" => [
                            [["text" => "📞 Поделиться номером", "request_contact" => true,]]
                        ]
                    ]
                ]);

                // TODO: проверить работоспособность
                if (!utils::getSettings()["require_phone"])
                    Http::post($url, [
                        'chat_id' => $user->telegram_id,
                        'text' => "Или же откажитесь",
                        "reply_markup" => [
                            "inline_keyboard" => [
                                [["text" => "✖️ Отказаться", "callback_data" => "refuse_phone",]]
                            ]
                        ]
                    ]);

                $user->step = "send_phone";
                $user->save();
                return response()->json(["status" => "ok"], 200);
            }

            $urlReaction = "https://api.telegram.org/bot$token/setMessageReaction";
            if ($user->step === "response") {
                $support = Support::where("user_id", $user->id)->whereNotNull("admin_id")->first();
                if (!$support) {
                    $admin = Admin::where("telegram_id", $user->telegram_id)->first();
                    if (!$admin) {
                        $user->step = "";
                        $user->save();
                        return response("", 200);
                    }

                    $support = Support::where("admin_id", $admin->id)->first();
                    if (!$support) utils::returnToAdmin($menu, $user, "Нет активных вопросов в чат поддержки");

                    utils::sendMessage($support->user->telegram_id, "Сообщение от администратора $user->fullname ($user->telegram_id):\n\n{$message["text"]}");
                    Http::post($urlReaction, [
                        'chat_id' => $user->telegram_id,
                        "message_id" => $message["message_id"],
                        "reaction" => ["✅"],
                    ]);
                    return response ("", 200);
                }

                $admin = $support->admin;
                utils::sendMessage($admin->telegram_id, "Сообщение от пользователя $user->fullname ($user->telegram_id):\n\n{$message["text"]}");
                Http::post($urlReaction, [
                    'chat_id' => $user->telegram_id,
                    "message_id" => $message["message_id"],
                    "reaction" => ["✅"],
                ]);
            }
            else if ($user->step === "send_phone") {
                if (isset($message["contact"])) {
                    $user->phone = $message["contact"]["phone_number"];
                    $user->step = "enter_full_name";
                    $user->save();

                    utils::requestFullname($user->telegram_id);
                }
                return response()->json(["status" => "ok"], 200);
            }

            else if ($user->step === "enter_full_name") {
                $pattern = '/^[А-ЯЁ][а-яё]+(?: [А-ЯЁ][а-яё]+){1,2}$/u';
                if (!preg_match($pattern, $message["text"])) {
                    utils::sendMessage($user->telegram_id, "Неправильное значение!");
                    return response()->json(["status" => "ok"], 200);
                }

                $user["fullname"] = $message["text"];
                $user["step"] = null;

                $user->save();
                utils::sendMessage($user->telegram_id, "ФИО было успешно добавлено!");

                return response()->json(["status" => "ok"], 200);
            }
            if ($message["text"] == "/admin") {
                if (!Admin::where("telegram_id", $requestUser["id"])->exists())
                    utils::sendMessage($requestUser["id"], "Отказано в доступе.");

                $user["step"] = "admin_menu";
                $user->save();

                $keyboard = [];
                foreach ($menu["menu"] as $button) $keyboard[] = ["text" => $button["name"]];
                $keyboard = array_chunk($keyboard, 2);

                $url = "https://api.telegram.org/bot$token/sendMessage";
                Http::post($url, [
                    'chat_id' => $user->telegram_id,
                    'text' => "Вы успешно авторизовались в систему администрирования!",
                    "reply_markup" => [
                        "keyboard" => $keyboard,
                    ]
                ]);
                return response()->json(["status" => "ok"], 200);
            }
            else if (preg_match('/^admin_settings_datacollection_what_edit_\d+$/', $user->step)) {
                $number = 0;
                if (preg_match('/(\d+)$/', $user->step, $matches))
                    $number = (int)$matches[1];

                $field = utils::getSettings()["webinar_fields"];

                $old = $field[$number];
                $field[$number] = $message["text"];

                utils::updateSettings("webinar_fields", $field);
                utils::sendMessage($user->telegram_id, "Поле \"$old\" успешно изменено на \"$field[$number]\"");

                $this->sendDataCollectionWhat($user, $sendurl);
            }
            else if ($user->step === "admin_settings_datacollection_what_adding") {
                $field = utils::getSettings()["webinar_fields"];
                $field[] = $message["text"];
                utils::updateSettings("webinar_fields", $field);

                utils::sendMessage($user->telegram_id, "Поле успешно добавлено!");

                $this->sendDataCollectionWhat($user, $sendurl);
                return response()->json(["status" => "ok"], 200);
            }
            else if ($user->step === "admin_mailing") {
                $admins = Cache::get("admins");
                $admins[$user->id]["mailing"] = $message["text"];
                Cache::forever("admins", $admins);

                $this->sendMailing($user, $sendurl);
                return response()->json(["status" => "ok"], 200);
            }
            else if ($this->findSubarrayByKey(self::$mailingConfig, $user->step, $result)) {
                if (strlen($message["text"]) < 3) utils::sendMessage($user->telegram_id, "Поиск не менее 3х символов");
                else {
                    $text = $message["text"];

                    if ($user->step === "admin_mailing_fullname") $data = User::where("fullname", "like", "%$text%")->get();
                    else if ($user->step === "admin_mailing_username") $data = User::where("username", "like", "%$text%")->get();
                    else if ($user->step === "admin_mailing_id") $data = User::where("telegram_id", "=", "$text")->get();

                    $selectedUsers = Cache::get("admins")[$user->id]["selected"];

                    $admins = Cache::get("admins");
                    $admins[$user->id]["search"] = $message["text"];
                    Cache::forever("admins", $admins);

                    $keyboard = $this->getKeyboard($data, $selectedUsers);

                    Http::post($sendurl, [
                        "chat_id" => $user->telegram_id,
                        "text" => "Поиск пользователей $result:\n- " . $message["text"],
                        "reply_markup" => [
                            "inline_keyboard" => $keyboard,
                        ]
                    ]);
                }
                return response()->json(["status" => "ok"], 200);
            }
            else if ($user->step === "admin_events_add_form_title") {
                if (strlen($message["text"]) < 3) {
                    utils::sendMessage($user->telegram_id, "Заголовок должен быть не менее 3х символов!");
                    return response()->json(["status" => "ok"], 200);
                }

                $admins = Cache::get("admins");
                $admins[$user->id]["eventTitle"] = $message["text"];
                Cache::forever("admins", $admins);

                $user->step = "admin_events_add_form_description";
                $user->save();
                utils::sendMessage($user->telegram_id, "Отправьте содержание: ");
            }
            else if ($user->step === "admin_events_add_form_description") {
                if (strlen($message["text"]) < 3) {
                    utils::sendMessage($user->telegram_id, "Содержание должен быть не менее 3х символов!");
                    return response()->json(["status" => "ok"], 200);
                }

                $admins = Cache::get("admins");
                $admins[$user->id]["eventDescription"] = $message["text"];
                Cache::forever("admins", $admins);

                $user->step = "admin_events_add_form_link";
                $user->save();

                if ($admins[$user->id]["type"] === "analytics")
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        "chat_id" => $user->telegram_id,
                        "text" => "Отправьте ссылку на видео-материал: ",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => [
                                [["text" => "Пропустить", "callback_data" => "admin_events_add_form_link_skip"]],
                            ],
                        ])
                    ]);
                else utils::sendMessage($user->telegram_id, "Отправьте ссылку на конференцию: ");
            }
            else if ($user->step === "admin_events_add_form_link") {
                // TODO: добавить проверку на валидность ссылки
                if (strlen($message["text"]) < 3) {
                    utils::sendMessage($user->telegram_id, "Ссылка должна быть не менее 3х символов!");
                    return response()->json(["status" => "ok"], 200);
                }

                $admins = Cache::get("admins");
                $admins[$user->id]["eventLink"] = $message["text"];
                Cache::forever("admins", $admins);

                if ($admins[$user->id]["type"] === "analytics") {
                    $user->step = "admin_events_add_form_pdf";
                    Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        "chat_id" => $user->telegram_id,
                        "text" => "Отправьте ссылку на пдф-файл: ",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => [
                                [["text" => "Пропустить", "callback_data" => "admin_events_add_form_pdf_skip"]],
                            ],
                        ])
                    ]);
                }
                else {
                    $user->step = "admin_events_add_form_date";
                    utils::sendMessage($user->telegram_id, "Отправьте дату конференции (в формате 'YYYY-MM-DD hh:mm:ss'): ");
                }
                $user->save();
            }
            else if ($user->step === "admin_events_add_form_date") {
                if (!preg_match('/^\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01])\s(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)$/', $message["text"])) {
                    utils::sendMessage($user->telegram_id,"Неправильный формат даты!");
                    return response()->json(["status" => "ok"], 200);
                }

                $admins = Cache::get("admins");
                $admins[$user->id]["eventDate"] = Carbon::parse($message["text"]);
                Cache::forever("admins", $admins);

                $user->step = "admin_events_add_form_image";
                $user->save();
                utils::sendMessage($user->telegram_id, "Отправьте картинку для вебинара");
            }
            else if ($user->step === "admin_events_add_form_pdf") {
                $pdf = $this->downloadPDF($user, $message);
                if (!$pdf) return response("", 200);

                $admins = Cache::get("admins");
                $admins[$user->id]["eventPdf"] = $pdf->body();
                Cache::forever("admins", $admins);

                $user->step = "admin_events_add_form_image";
                $user->save();
                utils::sendMessage($user->telegram_id, "Отправьте картинку: ");
            }
            else if ($user->step === "admin_events_add_form_image") {
                $imageContent = $this->downloadImage($user, $message);
                if (!$imageContent) return response("", 200);

                $admins = Cache::get("admins");
                $admins[$user->id]["eventImage"] = $imageContent->body();
                Cache::forever("admins", $admins);

                if ($admins[$user->id]["type"] === "analytics") $this->sendAddAnalyticMenu($user);
                else $this->sendAddWebinarMenu($user);

                $user->step = "admin_events_add_form_check";
                $user->save();
            }
            else if (preg_match('/^admin_events_add_edit_[A-Za-z]+$/i', $user->step)) {
                $array = explode("_", $user->step);
                $field = end($array);

                $admins = Cache::get("admins");
                if ($field == "Image") {
                    $imageContent = $this->downloadImage($user, $message);
                    if (!$imageContent) return response("", 200);

                    $admins[$user->id]["eventImage"] = $imageContent->body();
                } else if ($field === "Pdf") {
                    $pdf = $this->downloadPDF($user, $message);
                    if (!$pdf) return response("", 200);

                    $admins[$user->id]["eventPdf"] = $pdf->body();
                }
                else $admins[$user->id]["event$field"] = $message["text"];
                Cache::forever("admins", $admins);

                if ($admins[$user->id]["type"] === "analytics") $this->sendAddAnalyticMenu($user);
                else $this->sendAddWebinarMenu($user);
            }
            else if (str_contains($user->step, "admin_events_actual_edit_")) {
                $field = str_replace("admin_events_actual_edit_", "", $user->step);

                $type = Cache::get("admins")[$user->id]["type"];
                $id = Cache::get("admins")[$user->id]["edit_webinar"];

                if ($type == "analytics") $webinar = Analytic::find($id);
                else $webinar = Webinar::find($id);

                if ($field === "image") {
                    $imageContent = $this->downloadImage($user, $message);
                    if (!$imageContent) return response("", 200);

                    Storage::disk("public")->delete($webinar->image);

                    $newFile = "$type/photo_" . time() . ".jpg";
                    Storage::disk("public")->put($newFile, $imageContent);
                    $webinar->image = $newFile;

                } if ($field === "pdf") {
                    $pdf = $this->downloadPDF($user, $message);
                    if (!$pdf) return response("", 200);

                    Storage::disk("public")->delete($webinar->pdf);
                    $newFile = "$type/pdf_" . time() . ".pdf";
                    Storage::disk("public")->put($newFile, $pdf->body());
                    $webinar->pdf = $newFile;

                } else $webinar[$field] = $message["text"];
                $webinar->save();

                $photo = Storage::disk("public")->get($webinar->image);

                if ($type == "analytics") $this->actualEditAnalytic($user, $webinar);
                else $this->actualEditWebinar($photo, $token, $user, $webinar);
            }
            else if ($user->step == "admin_settings_statistics_search") {
                $field = Cache::get("admins")[$user->id]["type"];

                if ($field == "analytics") $data = Analytic::where("title", "like", "%" . $message["text"] . "%")->get();
                else $data = Webinar::where("title", "like", "%" . $message["text"] . "%")->get();

                $text = "Поиск по запросу: " . $message["text"] . "\n\n";
                foreach ($data as $record) $text .= "\n$record->id) $record->title";

                utils::returnToAdmin($menu, $user, $text);
            }
            $result = [];

            if ($user->step == "admin_menu") $result = $menu;
            else $this->findSubarrayByKey($menu, $user->step, $result);

            if ($result) {
                $newstep = "";
                $this->findKeyByName($result["menu"], $message["text"], $newstep);

                $newmenu = $result["menu"][$newstep];

                $user->step = $newstep;
                $user->save();

                if (isset($newmenu["menu"])) {
                    $keyboard = [];
                    foreach ($newmenu["menu"] as $button) $keyboard[] = ["text" => $button["name"]];
                    $keyboard = array_chunk($keyboard, 2);
                    $keyboard[] = [["text" => "Назад"]];

                    $url = "https://api.telegram.org/bot$token/sendMessage";
                    Http::post($url, [
                        'chat_id' => $user->telegram_id,
                        'text' => "Выбрана категория: " . $newmenu["name"],
                        "reply_markup" => [
                            "keyboard" => $keyboard,
                        ]
                    ]);
                } else {
                    $url = "https://api.telegram.org/bot$token/sendMessage";
                    Http::post($url, [
                        'chat_id' => $user->telegram_id,
                        'text' => "Выбрана категория: " . $newmenu["name"],
                        "reply_markup" => [
                            "remove_keyboard" => true
                        ]
                    ]);

                    if ($user->step === 'admin_settings_datacollection_phone') {
                        Http::post($url, [
                            'chat_id' => $user->telegram_id,
                            'text' => "Обязательно требовать номер телефона при регистрации?",
                            "reply_markup" => [
                                "inline_keyboard" => [
                                    [
                                        [
                                            "text" => "Да",
                                            "callback_data" => "admin_settings_datacollection_phone_accept"
                                        ],
                                        [
                                            "text" => "Нет",
                                            "callback_data" => "admin_settings_datacollection_phone_decline"
                                        ]
                                    ]
                                ],
                            ]
                        ]);
                    }
                    else if ($user->step === 'admin_settings_datacollection_what') {
                        Http::post($url, [
                            'chat_id' => $user->telegram_id,
                            'text' => "Выберите действие:",
                            "reply_markup" => [
                                "inline_keyboard" => [
                                    [
                                        [
                                            "text" => "Посмотреть",
                                            "callback_data" => "admin_settings_datacollection_what_check"
                                        ],
                                        [
                                            "text" => "Добавить",
                                            "callback_data" => "admin_settings_datacollection_what_add"
                                        ]
                                    ]
                                ],
                            ]
                        ]);
                    }
                    else if ($user->step === 'admin_mailing') {
                        $admins = Cache::get("admins");
                        $admins[$user->id]["selected"] = [];
                        $admins[$user->id]["selectedWebinars"] = [];
                        Cache::forever("admins", $admins);

                        utils::sendMessage($user->telegram_id, "Отправьте текст сообщения: ");
                    }
                    else if ($user->step === 'admin_events_add') {
                        $admins = Cache::get("admins");
                        $admins[$user->id]["forbidden"] = [];
                        Cache::forever("admins", $admins);

                        Http::post($url, [
                            'chat_id' => $user->telegram_id,
                            'text' => "Создать вебинар с ограничениями (выбрать поля для заполнения пользователем) или без ограничений?",
                            "reply_markup" => [
                                "inline_keyboard" => [
                                    [
                                        ["text" => "С ограничениями", "callback_data" => "admin_events_add_confines"],
                                        ["text" => "Без ограничений", "callback_data" => "admin_events_add_form"]
                                    ]
                                ],
                            ]
                        ]);
                    }
                    else if ($user->step === 'admin_events_actual') {
                        $admins = Cache::get("admins");
                        $admins[$user->id]["page"] = 1;
                        $admins[$user->id]["old"] = 0;
                        Cache::forever("admins", $admins);

                        $this->getEvents($user);
                    }
                    else if ($user->step === 'admin_events_archive') {
                        $admins = Cache::get("admins");
                        $admins[$user->id]["page"] = 1;
                        $admins[$user->id]["old"] = 1;
                        Cache::forever("admins", $admins);

                        $this->getEvents($user, 1);
                    }
                    else if ($user->step === 'admin_settings_statistics_toppopular') {
                        Http::post($url, [
                            'chat_id' => $user->telegram_id,
                            'text' => "Выберите категорию для топа популярности: ",
                            "reply_markup" => [
                                "inline_keyboard" => [
                                    [
                                        ["text" => "Материалы", "callback_data" => "admin_settings_statistics_toppopular_materials"],
                                        ["text" => "Ивенты", "callback_data" => "admin_settings_statistics_toppopular_events"]
                                    ]
                                ],
                            ]
                        ]);
                    }
                    else if ($user->step === 'admin_settings_statistics_allcountusers') {
                        $countAllUsers = User::count();
                        $countDayUsers = User::whereBetween("created_at", [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->count();
                        $count24HourUsers = User::whereBetween("created_at", [Carbon::now()->subHours(24), Carbon::now()])->count();
                        $countWeekUsers = User::whereBetween("created_at", [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
                        $countMonthUsers = User::whereBetween("created_at", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
                        $countSubMonthUsers = User::whereBetween("created_at", [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])->count();
                        $count30DayUsers = User::whereBetween("created_at", [Carbon::now()->subDays(30), Carbon::now()])->count();

                        $text = <<<EOT
                        Количество всех пользователей: $countAllUsers
                        Количество пользователей за 24 часа: $count24HourUsers
                        Количество пользователей за этот день: $countDayUsers
                        Количество пользователей за эту неделю: $countWeekUsers
                        Количество пользователей за этот месяц: $countMonthUsers
                        Количество пользователей за предыдущий месяц: $countSubMonthUsers
                        Количество пользователей за 30 дней: $count30DayUsers
                        EOT;
                        ;

                        utils::returnToAdmin($menu, $user, $text);
                    }
                    else if ($user->step === 'admin_settings_statistics_search') {
                        Http::post($url, [
                            'chat_id' => $user->telegram_id,
                            'text' => "Выберите категорию для поиска: ",
                            "reply_markup" => [
                                "inline_keyboard" => [
                                    [
                                        ["text" => "Материалы", "callback_data" => "admin_settings_statistics_search_analytics"],
                                        ["text" => "Ивенты", "callback_data" => "admin_settings_statistics_search_webinars"]
                                    ]
                                ],
                            ]
                        ]);
                    }
                    else if ($user->step === 'admin_settings_support') {
                        $data = Support::where("admin_id", null)->get();
                        $count = Support::where("admin_id", null)->count();

                        $keyboard = [];
                        foreach ($data as $record)
                            $keyboard[] = ["text" => "[$record->user_id] $record->text", "callback_data" => "admin_settings_support_claim_$record->id"];
                        $keyboard = array_chunk($keyboard, 1);

                        Http::post($url, [
                            'chat_id' => $user->telegram_id,
                            'text' => "Незакрытые вопросы в поддержку ($count): ",
                            "reply_markup" => [
                                "inline_keyboard" => $keyboard
                            ]
                        ]);
                    }
                }
                if ($user->step === 'admin_events') {
                    $admins = Cache::get("admins");
                    $admins[$user->id]["type"] = "webinars";
                    Cache::forever("admins", $admins);
                }
                else if ($user->step === 'admin_materials') {
                    $admins = Cache::get("admins");
                    $admins[$user->id]["type"] = "analytics";
                    Cache::forever("admins", $admins);
                }
            }
        }} catch (\Exception $e) {
            Log::critical($e);
        }
        return response()->json(["status" => "ok"], 200);
    }

    /**
     * @param $user
     * @param string $sendurl
     * @return array
     */
    protected function sendDataCollectionWhat($user, string $sendurl): array
    {
        $fields = utils::getSettings()["webinar_fields"];
        $arr = [];

        foreach ($fields as $key => $field)
            $arr[] = ["text" => $field, "callback_data" => "admin_settings_datacollection_what_check_" . $key];
        $arr = array_chunk($arr, 1);

        $user->step = "admin_settings_datacollection_what_checking";
        $user->save();

        Http::post($sendurl, [
            "chat_id" => $user->telegram_id,
            "text" => "Поля формы: ",
            "reply_markup" => [
                "inline_keyboard" => $arr,
            ],
        ]);
        return array($fields, $arr, $key, $field);
    }

    /**
     * @param $user
     * @param string $sendurl
     * @return void
     */
    protected function sendMailing($user, string $sendurl): void
    {
        $text = Cache::get("admins")[$user->id]["mailing"];

        $keyboard = [];
        foreach (self::$mailingConfig as $key => $item)
            $keyboard[] = ["text" => $item, "callback_data" => $key];
        $keyboard[] = ["text" => "По интересам", "callback_data" => "admin_mailing_interests"];
        $keyboard[] = ["text" => "Изменить сообщение", "callback_data" => "admin_mailing_change"];
        $keyboard[] = ["text" => "Отправить сообщение", "callback_data" => "admin_mailing_send"];
        $keyboard[] = ["text" => "Отправить всем пользователям", "callback_data" => "admin_mailing_sendall"];

        $keyboard = array_chunk($keyboard, 2);
        Http::post($sendurl, [
            "chat_id" => $user->telegram_id,
            "text" => "Текст сообщения:\n" . $text .
                "\n\nКому вы хотите отправить это сообщение?",
            "reply_markup" => [
                "inline_keyboard" => $keyboard
            ],
        ]);
    }

    /**
     * @param $data
     * @param mixed $selectedUsers
     * @return array
     */
    protected function getKeyboard($data, mixed $selectedUsers): array
    {
        $keyboard = [];
        $keyboard[] = [
            "text" => "Назад",
            "callback_data" => "admin_mailing_return",
        ];
        foreach ($data as $record) {
            if (in_array($record->id, $selectedUsers)) {
                $keyboard[] = [
                    "text" => "✅ " . $record->fullname . " (id: " . $record->telegram_id . ")",
                    "callback_data" => "admin_mailing_remove_" . $record->id
                ];
            } else
                $keyboard[] = [
                    "text" => $record->fullname . " (id: " . $record->telegram_id . ")",
                    "callback_data" => "admin_mailing_add_" . $record->id
                ];
        }

        $keyboard = array_chunk($keyboard, 1);
        return $keyboard;
    }

    protected function findKeyByName($array, $targetName, &$foundKey = null)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (isset($value['name']) && $value['name'] === $targetName) {
                    $foundKey = $key;
                    return true;
                }
            }
        }
        return false;
    }

    protected function findSubarrayByKey($array, $key, &$result = null) {
        foreach ($array as $k => $value) {
            if ($k === $key) {
                $result = $value;
                return true;
            }
            if (is_array($value)) {
                if ($this->findSubarrayByKey($value, $key, $result)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getEvents($user, $old = 0)
    {
        $page = Cache::get("admins")[$user->id]["page"];

        if ($old) $sign = "<";
        else $sign = ">";

        $admins = Cache::get("admins");
        if ($admins[$user->id]["type"] === "analytics") $query = Analytic::query();
        else $query = Webinar::where("date", $sign, Carbon::now());

        $count = $query->count();
        $data = $query->limit(10)->offset(($page-1)*10)->get();

        $keyboard = [];
        foreach ($data as $web) {
            $keyboard[] = [
                ["text" => $web->title, "callback_data" => "admin_events_actual_show_$web->id"],
                ["text" => "✏️", "callback_data" => "admin_events_actual_edit_$web->id"],
                ["text" => "🗑️", "callback_data" => "admin_events_actual_delete_$web->id"],
            ];
        }
        $tools = [];
        if ($page !== 1) $tools[] = ["text" => "<<<", "callback_data" => "admin_events_actual_tools_back"];
        if (ceil($count / 10) > $page) $tools[] = ["text" => ">>>", "callback_data" => "admin_events_actual_tools_next"];
        $keyboard[] = $tools;

        $token = env("TELEGRAM_BOT_TOKEN");
        $url = "https://api.telegram.org/bot$token/sendMessage";
        Http::post($url, [
            'chat_id' => $user->telegram_id,
            'text' => "Список актуальных вебинаров ({$page} стр.):",
            "reply_markup" => [
                "inline_keyboard" => $keyboard,
            ]
        ]);
    }

    /**
     * @param string|null $photo
     * @param mixed $token
     * @param $user
     * @param $webinar
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    protected function actualEditWebinar(?string $photo, mixed $token, $user, $webinar): void
    {
        Http::attach(
            "photo",
            $photo,
            "photo.jpg"
        )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
            "chat_id" => $user->telegram_id,
            "caption" => "Ивент $webinar->id ID: Вебинар.\n\nЗаголовок: " . $webinar->title
                . "\nОписание: " . $webinar->description
                . "\nСсылка: " . $webinar->link
                . "\nДата: " . $webinar->date
                . "\nПоля: " . json_decode($webinar->fields),
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [["text" => "Изменить заголовок", "callback_data" => "admin_events_actual_edit_title"]],
                    [["text" => "Изменить описание", "callback_data" => "admin_events_actual_edit_description"]],
                    [["text" => "Изменить ссылку", "callback_data" => "admin_events_actual_edit_link"]],
                    [["text" => "Изменить дату", "callback_data" => "admin_events_actual_edit_date"]],
                    [["text" => "Изменить картинку", "callback_data" => "admin_events_actual_edit_image"]],
                ],
            ])
        ]);
    }
    protected function sendAddAnalyticMenu ($user) {
        $admins = Cache::get("admins");
        $token = env("TELEGRAM_BOT_TOKEN");

        Http::attach(
            "photo",
            $admins[$user->id]["eventImage"],
            "image.jpg"
        )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
            "chat_id" => $user->telegram_id,
            "caption" => "Новый материал: Аналитика.\n\nЗаголовок: " . $admins[$user->id]["eventTitle"]
                . "\nОписание: " . $admins[$user->id]["eventDescription"]
                . "\nСсылка на видео: " . $admins[$user->id]["eventLink"],
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [["text" => "Изменить заголовок", "callback_data" => "admin_events_add_edit_Title"]],
                    [["text" => "Изменить описание", "callback_data" => "admin_events_add_edit_Description"]],
                    [["text" => "Изменить ссылку", "callback_data" => "admin_events_add_edit_Link"]],
                    [["text" => "Изменить картинку", "callback_data" => "admin_events_add_edit_Image"]],
                    [["text" => "Изменить pdf-файл", "callback_data" => "admin_events_add_edit_Pdf"]],
                    [["text" => "Добавить аналитику", "callback_data" => "admin_events_add_form_add"]],
                ],
            ])
            // TODO: Настроить "актуальные" аналитики.
            // TODO: также "сбор данных" и интеграцию Calendly.
        ]);

        Http::attach(
            "document",
            $admins[$user->id]["eventPdf"],
            "document.pdf"
        )->post("https://api.telegram.org/bot{$token}/sendDocument", [
            "chat_id" => $user->telegram_id,
            "caption" => "ПДФ Документ: ",
        ]);
    }
    protected function actualEditAnalytic ($user, $analytic) {
        $admins = Cache::get("admins");
        $token = env("TELEGRAM_BOT_TOKEN");

        $image = Storage::disk("public")->get($analytic->image);

        Http::attach(
            "photo",
            $image,
            "image.jpg"
        )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
            "chat_id" => $user->telegram_id,
            "caption" => "Редактирование материала: Аналитика.\n\nЗаголовок: " . $analytic->title
                . "\nОписание: " . $analytic->description
                . "\nСсылка на видео: " . $analytic->link
                . "\nПоля: " . json_decode($analytic->fields),
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [["text" => "Изменить заголовок", "callback_data" => "admin_events_actual_edit_title"]],
                    [["text" => "Изменить описание", "callback_data" => "admin_events_actual_edit_description"]],
                    [["text" => "Изменить ссылку", "callback_data" => "admin_events_actual_edit_link"]],
                    [["text" => "Изменить картинку", "callback_data" => "admin_events_actual_edit_image"]],
                    [["text" => "Изменить pdf-файл", "callback_data" => "admin_events_actual_edit_pdf"]],
                ],
            ])
        ]);

        Log::critical($analytic);
        if ($analytic->pdf) {
            $pdf = Storage::disk("public")->get($analytic->pdf);
            Log::critical($analytic->pdf);
            Log::critical($pdf);
            Http::attach(
                "document",
                $pdf,
                "document.pdf"
            )->post("https://api.telegram.org/bot{$token}/sendDocument", [
                "chat_id" => $user->telegram_id,
                "caption" => "ПДФ Документ: ",
            ]);
        }
    }

    protected function sendAddWebinarMenu ($user) {
        $admins = Cache::get("admins");
        $token = env("TELEGRAM_BOT_TOKEN");

        Http::attach(
            "photo",
            $admins[$user->id]["eventImage"],
            "preview.jpg"
        )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
            "chat_id" => $user->telegram_id,
            "caption" => "Новый ивент: Вебинар.\n\nЗаголовок: " . $admins[$user->id]["eventTitle"]
                . "\nОписание: " . $admins[$user->id]["eventDescription"]
                . "\nСсылка: " . $admins[$user->id]["eventLink"]
                . "\nДата: " . $admins[$user->id]["eventDate"],
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [["text" => "Изменить заголовок", "callback_data" => "admin_events_add_edit_Title"]],
                    [["text" => "Изменить описание", "callback_data" => "admin_events_add_edit_Description"]],
                    [["text" => "Изменить ссылку", "callback_data" => "admin_events_add_edit_Link"]],
                    [["text" => "Изменить дату", "callback_data" => "admin_events_add_edit_Date"]],
                    [["text" => "Изменить картинку", "callback_data" => "admin_events_add_edit_Image"]],
                    [["text" => "Добавить вебинар", "callback_data" => "admin_events_add_form_add"]],
                ],
            ])
        ]);
    }
    protected function downloadImage ($user, $message) {
        if (!isset($message["photo"][0])) {
            utils::sendMessage($user->telegram_id,"Отправьте картинку!");
            return false;
        }
        $token = env("TELEGRAM_BOT_TOKEN");
        $file_id = end($message["photo"])["file_id"];
        $response = Http::post("https://api.telegram.org/bot{$token}/getFile", [
            "file_id" => $file_id,
        ]);

        $fileData = $response->json();
        $filePath = $fileData["result"]["file_path"];

        return Http::get("https://api.telegram.org/file/bot{$token}/{$filePath}");
    }

    protected function downloadPDF ($user, $message) {
        if (!isset($message["document"])) {
            utils::sendMessage($user->telegram_id,"Отправьте файл!");
            return false;
        }
        if ($message["document"]["mime_type"] !== "application/pdf") {
            utils::sendMessage($user->telegram_id,"Отправьте файл формата PDF!");
            return false;
        }

        $token = env("TELEGRAM_BOT_TOKEN");
        $file_id = $message["document"]["file_id"];
        $response = Http::post("https://api.telegram.org/bot{$token}/getFile", [
            "file_id" => $file_id,
        ]);

        $fileData = $response->json();
        $filePath = $fileData["result"]["file_path"];

        return Http::get("https://api.telegram.org/file/bot{$token}/{$filePath}");
    }
}
