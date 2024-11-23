<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    use HasFactory;
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function banking(){
        return $this->belongsTo(Banking::class);
    }

    public function verified_by($admin_id){
        return User::find($admin_id);
    }
}
