<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'country',
        'address_1',
        'address_2',
        'identity_number',
        'verification_type',
        'kyc_approval',
        'kyc_approval_description',
        'referral_code',
        'upline_id',
        'hierarchyList',
        'status',
        'role',
        'setting_rank_id',
        'total_affiliate',
        'self_deposit',
        'valid_affiliate_deposit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getChildrenIds(): array
    {
        return User::query()->where('hierarchyList', 'like', '%-' . $this->id . '-%')
            ->where('status', 1)
            ->pluck('id'
            )->toArray();
    }

    public function setReferralId(): void
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvwxyz';
        $idLength = strlen((string)$this->id);

        $temp_code = substr(str_shuffle($characters), 0, 8 - $idLength);
        $alphabetId = '';

        foreach (str_split((string)$this->id) as $digit) {
            $alphabetId .= $characters[$digit];
        }

        $this->referral_code = $temp_code . $alphabetId;
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        $user = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('user')
            ->logOnly(['name', 'email', 'password', 'country', 'phone', 'verification_type', 'identity_number', 'kyc_approval', 'kyc_approval_description', 'upline_id', 'hierarchyList', 'referral_code', 'status', 'role', 'setting_rank_id'])
            ->setDescriptionForEvent(function (string $eventName) use ($user) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} {$user->name}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    public function wallets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id' );
    }

    public function upline(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id', 'id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'upline_id', 'id');
    }

    public function rank(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(SettingRank::class, 'setting_rank_id', 'id');
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(InvestmentSubscription::class, 'user_id', 'id');
    }

    public function coins(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Coin::class, 'user_id', 'id');
    }

    public function coinStaking(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CoinStacking::class, 'user_id', 'id');
    }

    public function binary(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CoinMultiLevel::class, 'user_id', 'id');
    }
}
