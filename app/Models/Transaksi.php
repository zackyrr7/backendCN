<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'harga',
        'tanggal',
        'jenis',
        'users_id',
        'asal',
    ];

 public function user()
 {
    return $this->belongsTo(User::class);
 }   
}
