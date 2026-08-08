<?php

namespace App\Notifications;

class FcmMessage extends \NotificationChannels\Fcm\FcmMessage
{
    public static function create(): self
    {
        return new self;
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->getName(),
            'data' => $this->getData(),
            'notification' => !is_null($this->getNotification()) ? $this->getNotification()->toArray() : null,
            'android' => !is_null($this->getAndroid()) ? $this->getAndroid()->toArray() : null,
            'webpush' => !is_null($this->getWebpush()) ? $this->getWebpush()->toArray() : null,
            'apns' => !is_null($this->getApns()) ? $this->getApns()->toArray() : null,
            'fcm_options' => !is_null($this->getFcmOptions()) ? $this->getFcmOptions()->toArray() : null,
        ];

        if ($token = $this->getToken()) {
            $data['token'] = $token;
        }

        if ($topic = $this->getTopic()) {
            $data['topic'] = $topic;
        }

        if ($condition = $this->getCondition()) {
            $data['condition'] = $condition;
        }

        return $data;
    }
}
