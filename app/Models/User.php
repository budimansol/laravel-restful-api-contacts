<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;
    
    protected $fillable = [
        'username',
        'password',
        'first_name',
        'last_name'
    ];
    
    public function contacts(): HasMany {
        return $this->hasMany(Contact::class, 'user_id', 'id');
    }
}
