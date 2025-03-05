<?php

namespace App\Console\Commands;

use App\Http\Controllers\utils;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyWebinarUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-webinar-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Уведомляет пользователей о вебинарах';

    /**
     * Execute the console command.
     */
    public function handle()
    {
            // Работаем в часовом поясе Москвы
            $now = Carbon::now('Europe/Moscow');

            // Логика уведомления за 24 часа до вебинара
            // Выбираем вебинары, у которых время старта ровно через 24 часа (с допуском +- 5 минут, например)
            $start24 = $now->copy()->addDay();
            $webinars24 = Webinar::whereBetween('date', [
                $start24->copy()->subMinutes(5),
                $start24->copy()->addMinutes(5)
            ])->get();

            Log::critical($webinars24);

            foreach ($webinars24 as $webinar) {
                $this->sendTelegramNotification($webinar, 'Напоминаем, что вебинар "' . $webinar->title . '" начнется через 24 часа.');
            }

            // Логика уведомления в день проведения вебинара
            // Определяем время уведомления: если вебинар начинается после 12:00, то уведомляем в 10:00, иначе в 8:00
            // Для каждого вебинара, дата которого сегодня, проверяем, нужно ли отправлять уведомление
            $today = $now->toDateString();
            $webinarsToday = Webinar::whereDate('date', $today)->get();

            foreach ($webinarsToday as $webinar) {
                // Получаем время начала вебинара
                $eventTime = Carbon::parse($webinar->datetime, 'Europe/Moscow');
                // Определяем желаемое время уведомления в день вебинара
                if ($eventTime->hour >= 12) {
                    // если после 12, то уведомление в 10:00
                    $notificationTime = Carbon::createFromTime(10, 0, 0, 'Europe/Moscow');
                } else {
                    // иначе в 8:00
                    $notificationTime = Carbon::createFromTime(8, 0, 0, 'Europe/Moscow');
                }
                // Отправляем уведомление, если сейчас примерно совпадает с временем уведомления (например, +- 5 минут)
                if ($now->between($notificationTime->copy()->subMinutes(5), $notificationTime->copy()->addMinutes(5))) {
                    $this->sendTelegramNotification($webinar, 'Напоминаем, что сегодня вебинар "' . $webinar->title . '"!');
                }
        }
    }

    private function sendTelegramNotification ($webinar, $text) {
        foreach ($webinar->users as $user) {
            if ($user->notifications == 1) {
                utils::sendMessage($user->telegram_id, $text);
            }
        }
    }
}
