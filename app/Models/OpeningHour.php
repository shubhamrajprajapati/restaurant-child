<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class OpeningHour extends Model implements Sortable
{
    use HasFactory, HasUuids, SoftDeletes, SortableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'opening_hours';

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
        // Day columns and time slots
        'monday_name', 'monday_start_time_1', 'monday_end_time_1', 'monday_start_time_2', 'monday_end_time_2', 'monday_open',
        'tuesday_name', 'tuesday_start_time_1', 'tuesday_end_time_1', 'tuesday_start_time_2', 'tuesday_end_time_2', 'tuesday_open',
        'wednesday_name', 'wednesday_start_time_1', 'wednesday_end_time_1', 'wednesday_start_time_2', 'wednesday_end_time_2', 'wednesday_open',
        'thursday_name', 'thursday_start_time_1', 'thursday_end_time_1', 'thursday_start_time_2', 'thursday_end_time_2', 'thursday_open',
        'friday_name', 'friday_start_time_1', 'friday_end_time_1', 'friday_start_time_2', 'friday_end_time_2', 'friday_open',
        'saturday_name', 'saturday_start_time_1', 'saturday_end_time_1', 'saturday_start_time_2', 'saturday_end_time_2', 'saturday_open',
        'sunday_name', 'sunday_start_time_1', 'sunday_end_time_1', 'sunday_start_time_2', 'sunday_end_time_2', 'sunday_open',

        'message', 'content', 'active',

        'order_column',

        'updated_by_user_id', 'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'monday_start_time_1' => 'datetime:H:i:s',
        'monday_end_time_1' => 'datetime:H:i:s',
        'monday_start_time_2' => 'datetime:H:i:s',
        'monday_end_time_2' => 'datetime:H:i:s',
        'tuesday_start_time_1' => 'datetime:H:i:s',
        'tuesday_end_time_1' => 'datetime:H:i:s',
        'tuesday_start_time_2' => 'datetime:H:i:s',
        'tuesday_end_time_2' => 'datetime:H:i:s',
        'wednesday_start_time_1' => 'datetime:H:i:s',
        'wednesday_end_time_1' => 'datetime:H:i:s',
        'wednesday_start_time_2' => 'datetime:H:i:s',
        'wednesday_end_time_2' => 'datetime:H:i:s',
        'thursday_start_time_1' => 'datetime:H:i:s',
        'thursday_end_time_1' => 'datetime:H:i:s',
        'thursday_start_time_2' => 'datetime:H:i:s',
        'thursday_end_time_2' => 'datetime:H:i:s',
        'friday_start_time_1' => 'datetime:H:i:s',
        'friday_end_time_1' => 'datetime:H:i:s',
        'friday_start_time_2' => 'datetime:H:i:s',
        'friday_end_time_2' => 'datetime:H:i:s',
        'saturday_start_time_1' => 'datetime:H:i:s',
        'saturday_end_time_1' => 'datetime:H:i:s',
        'saturday_start_time_2' => 'datetime:H:i:s',
        'saturday_end_time_2' => 'datetime:H:i:s',
        'sunday_start_time_1' => 'datetime:H:i:s',
        'sunday_end_time_1' => 'datetime:H:i:s',
        'sunday_start_time_2' => 'datetime:H:i:s',
        'sunday_end_time_2' => 'datetime:H:i:s',

        'monday_open' => 'boolean',
        'tuesday_open' => 'boolean',
        'wednesday_open' => 'boolean',
        'thursday_open' => 'boolean',
        'friday_open' => 'boolean',
        'saturday_open' => 'boolean',
        'sunday_open' => 'boolean',

        'active' => 'boolean',
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
