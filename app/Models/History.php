<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $appends = ['image_url'];

    public function getImageUrlAttribute() {
        $hostname = env('STORAGE_HOSTNAME', '');
        if($this->image) {
            return $hostname . $this->image;
        }
        return null;
    }
}
