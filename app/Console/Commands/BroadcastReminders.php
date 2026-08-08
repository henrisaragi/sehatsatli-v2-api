<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MOdels\User;
use App\Notifications\BroadcastNotification;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use NotificationChannels\Fcm\FcmChannel;

class BroadcastReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sehatsatli-report:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // $users = User::all();
        // $notification = new BroadcastNotification();
        // foreach ($users as $user) {
        //     // $user->notify($user->devices, $notification);
        //     $user->notify(new BroadcastNotification());
        // }

        // $users = User::all();

        // foreach ($users as $user) {
        //     if ($user->devices == null) {
        //         continue;
        //     }
        //     // $user->notify(new BroadcastNotification($messages, $user));
        //     $notification = new BroadcastNotification;

        //     if ($user != null && $user->devices != null &&  $user->devices != '') {
        //         Notification::send($user->devices, $notification);
        //     }

        //     // $user->notify(new BroadcastNotification);

        //     $user->devices = FcmChannel::class;
        // }

        $SERVER_API_KEY = env('APP_SERVER_API_KEY');

        $firebaseTokenPETUGAS = User::where('status', 1)
            ->where('user_level', 4)
            ->pluck('devices')
            ->all();

        $subject = 'Notifikasi Sehatsatli';
        $body = 'Harap kirim laporan, JIKA TIDAK TERJADI KASUS SEHATSATLI di bulan ini';

        $data = array(
            'registration_ids' => $firebaseTokenPETUGAS,
            'notification' => [
                'title' => $subject,
                'body' => $body,
            ],
        );

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // return true;
    }
}
