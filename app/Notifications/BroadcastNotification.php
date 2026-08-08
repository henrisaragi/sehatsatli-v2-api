<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use App\Notifications\FcmMessage;
// use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class BroadcastNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $title;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->title = "Notifikasi Sehatsatli";
        $this->message = "Harap kirim laporan, JIKA TIDAK TERJADI KASUS SEHATSATLI di bulan ini";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['mail'];
        return [FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            // ->setData(['title' => $this->title, 'data2' => $this->message])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($this->title)
                ->setBody($this->message)
                ->setImage('https://app.sehatsatli.id/assets/logo-color.de3fd3b8.png'));
        // ->setAndroid(
        //     AndroidConfig::create()
        //         ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
        //         ->setNotification(AndroidNotification::create()->setColor('#0A0A0A'))
        // )->setApns(
        //     ApnsConfig::create()
        //         ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
        // );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toArray($notifiable)
    // {
    //     return [
    //         //
    //     ];
    // }

    public function fcmProject($notifiable, $message)
    {
        return 'com.sehatsatli';
    }
}
