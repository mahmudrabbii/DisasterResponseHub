<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    protected $table = 'people';
    protected $fillable = ['name', 'email', 'phone'];
    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(User::class, 'person_id');
    }
}
