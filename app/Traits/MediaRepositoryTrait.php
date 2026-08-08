<?php

namespace App\Traits;

// Note : Need $media_root_folder in class
trait MediaRepositoryTrait
{
    // Single set
    public function setMedia($id)
    {
        $item = $this->getOne($id);
        foreach ($item->media as $id => $media) {
            $media->delete();
        }
        $media = $item->addMediaFromRequest('file')
            ->toMediaCollection();
        return $media;
    }

    // Multiple file
    public function addMedia($id)
    {
        $item = $this->getOne($id);
        $media = $item->addMediaFromRequest('file')
            ->toMediaCollection();
        return $media;
    }

    // Delete one
    public function removeMedia($id, $media_id)
    {
        $item = $this->getOne($id);
        foreach ($item->media as $id => $media) {
            if ($media->id == $media_id) {
                $media->delete();
                return true;
            }
        }
        return true;
    }
}
