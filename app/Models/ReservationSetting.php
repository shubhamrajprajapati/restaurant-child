<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ReservationSetting extends Model implements Sortable
{
    use HasFactory, HasUuids, SoftDeletes, SortableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservation_settings';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'restaurant_id',
        'opening_hour_id',

        'name',
        'active',

        'ask_name',
        'ask_email',
        'ask_telephone',
        'ask_address',

        'emails',

        'success_msg',
        'close_msg',
        'email_msg',

        'link_with_opening_hours',
        'mail_to_self',
        'mail_to_customer',

        'mail_delay',
        'time_interval',

        'order_column',

        'updated_by_user_id',
        'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',

        'ask_name' => 'boolean',
        'ask_email' => 'boolean',
        'ask_telephone' => 'boolean',
        'ask_address' => 'boolean',

        'emails' => 'array',

        'link_with_opening_hours' => 'boolean',
        'mail_to_self' => 'boolean',
        'mail_to_customer' => 'boolean',

        'mail_delay' => 'integer',
        'time_interval' => 'integer',
    ];

    /**
     * Configure sortable behavior.
     */
    public function buildSortQuery()
    {
        return static::query()->orderBy('order_column');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function opening_hours(): BelongsTo
    {
        return $this->belongsTo(OpeningHour::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'created_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'updated_by_user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $reservationSetting) {
            // Deactivate all other themes when setting the current theme to active
            if ($reservationSetting->active) {
                self::where('id', '!=', $reservationSetting->id)->update(['active' => false]);
            }
        });

        static::deleting(function ($reservationSetting) {
            // Prevent deletion if it’s the last row
            if (self::count() <= 1) {
                throw new \Exception('You cannot delete the last reservation setting.');
            }
        });

    }
}
