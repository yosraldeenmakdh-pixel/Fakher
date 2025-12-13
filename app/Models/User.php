<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens ,HasRoles ;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password' ,
        'phone',
        'address' ,
        'image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class ,'user_id');
    // }
    public function orders_online()
    {
        return $this->hasMany(OrderOnline::class ,'user_id');
    }

    // public function officialInstitution()
    // {
    //     return $this->hasOne(OfficialInstitution::class);
    // }
    public function kitchen()
    {
        return $this->hasOne(Kitchen::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

}
