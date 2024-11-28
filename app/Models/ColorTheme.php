<?php

namespace App\Models;

use App\Casts\ColorWithReverse;
use App\Enums\ColorThemeTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ColorTheme extends Model implements Sortable
{
    use HasFactory, SoftDeletes, SortableTrait, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'color_themes';

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

        'name',

        'theme_1', 'theme_2', 'theme_3', 'theme_4',
        'light_1', 'light_2', 'light_3', 'light_4',
        'dark_1', 'dark_2', 'dark_3', 'dark_4',

        'marquee_1', 'marquee_2',

        'text_white', 'text_black',

        'bg_white', 'bg_black',

        'neutral_white',
        'neutral_black',
        'neutral_gray',
        'neutral_light_gray',
        'neutral_x_light_gray',
        'neutral_dark_gray',

        'active',
        'type',

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
        // Theme colors
        'theme_1' => ColorWithReverse::class,
        'theme_2' => ColorWithReverse::class,
        'theme_3' => ColorWithReverse::class,
        'theme_4' => ColorWithReverse::class,

        // Light colors
        'light_1' => ColorWithReverse::class,
        'light_2' => ColorWithReverse::class,
        'light_3' => ColorWithReverse::class,
        'light_4' => ColorWithReverse::class,

        // Dark colors
        'dark_1' => ColorWithReverse::class,
        'dark_2' => ColorWithReverse::class,
        'dark_3' => ColorWithReverse::class,
        'dark_4' => ColorWithReverse::class,

        // Marquee colors
        'marquee_1' => ColorWithReverse::class,
        'marquee_2' => ColorWithReverse::class,

        // Text colors
        'text_white' => ColorWithReverse::class,
        'text_black' => ColorWithReverse::class,

        // Background colors
        'bg_white' => ColorWithReverse::class,
        'bg_black' => ColorWithReverse::class,

        // Neutral colors
        'neutral_white' => ColorWithReverse::class,
        'neutral_black' => ColorWithReverse::class,
        'neutral_gray' => ColorWithReverse::class,
        'neutral_light_gray' => ColorWithReverse::class,
        'neutral_x_light_gray' => ColorWithReverse::class,
        'neutral_dark_gray' => ColorWithReverse::class,

        'active' => 'boolean',
        'type' => ColorThemeTypeEnum::class,
    ];

    /**
     * Configure sortable behavior.
     */
    public function buildSortQuery()
    {
        return static::query()->orderBy('order_column');
    }

    /**
     * Relationship with the restaurant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Relationship with the user who created this theme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'created_by_user_id');
    }

    /**
     * Relationship with the user who last updated this theme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'updated_by_user_id');
    }

    /**
     * Dynamic accessor for color attributes with "r" (reversed) or "o" (original) prefix.
     * Also supports accessing the color's original or reversed value by "r" or "o" prefixes.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        // Handle original and reversed colors based on prefix
        if (str_starts_with($key, 'r') || str_starts_with($key, 'o')) {
            $type = str_starts_with($key, 'r') ? 'reversed' : 'original';
            $originalKey = lcfirst(substr($key, 1)); // Remove "r" or "o" prefix

            if (array_key_exists($originalKey, $this->casts)) {
                return $this->$originalKey->$type ?? null; // Return original or reversed value
            }
        }

        // Return the default attribute if no match
        return parent::__get($key);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $colorTheme) {
            // If no records exist and the current record is being created with active = false,
            // throw an exception because the first record must have active = true.
            if (self::count() === 0 && !$colorTheme->active) {
                throw new \Exception('The first color theme must be active.');
            }

            // If the color theme already exists and we're trying to set active to false
            // while there is only one record in the table, throw an exception
            if ($colorTheme->exists && !$colorTheme->active && self::count() === 1) {
                throw new \Exception('Deactivation of the only color theme is not allowed. '
                    . 'There must be at least one active color theme in the system.');
            }

            // Deactivate all other themes when setting the current theme to active
            if ($colorTheme->active) {
                self::where('id', '!=', $colorTheme->id)->update(['active' => false]);
            }
        });

        static::deleting(function ($colorTheme) {
            // Prevent deletion if it’s the last row
            if (self::count() <= 1) {
                throw new \Exception('You cannot delete the last color theme.');
            }
        });

    }
}
