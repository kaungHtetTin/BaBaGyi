<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $appends = ['launch_url', 'fallback_url'];

    public function getLaunchUrlAttribute(): ?string
    {
        return \App\Support\ContactLink::launch($this->service, $this->contact);
    }

    public function getFallbackUrlAttribute(): ?string
    {
        $url = $this->launch_url;
        return $url && str_starts_with($url, 'https://') ? $url
            : ($url ? 'https://www.viber.com/en/download/' : null);
    }
}
