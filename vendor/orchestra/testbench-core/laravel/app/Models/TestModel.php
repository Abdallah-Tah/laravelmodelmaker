<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasFactory;

    protected $fillable = [

    ];

    public function testModel2()
    {
        return $this->hasMany(App\Models\TestModel2::class);
    }
    public function testModel3()
    {
        return $this->belongsTo(App\Models\TestModel3::class);
    }
    public function testModel4()
    {
        return $this->hasOne(App\Models\TestModel4::class);
    }
    public function testModel5()
    {
        return $this->belongsToMany(App\Models\TestModel5::class);
    }
}
