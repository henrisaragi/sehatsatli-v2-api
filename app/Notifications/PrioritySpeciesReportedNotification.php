<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Notifications\FcmMessage;
use NotificationChannels\Fcm\FcmChannel;
//use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class PrioritySpeciesReportedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        // SehatSatli - Satwa Prioritas dilaporkan
        // Species pada kode laporan oleh Nama


        return FcmMessage::create()
            ->setData(['data1' => 'value', 'data2' => 'value2'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('SehatSatli - Satwa Prioritas di $upt')
                ->setBody('$species pada laporan $code oleh $reporter')
                ->setImage(url("/assets/logo.png")));
        // ->setAndroid(
        //     AndroidConfig::create()
        //         ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
        //         ->setNotification(AndroidNotification::create()->setColor('#0A0A0A'))
        // )->setApns(
        //     ApnsConfig::create()
        //         ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
        // );
    }
}
