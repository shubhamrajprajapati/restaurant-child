<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ColorWithReverse implements CastsAttributes
{
    /**
     * Get the value of the attribute, including the original and reversed color.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return object|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (!$value) {
            return null;
        }

        return (object) [
            'original' => $value,
            'reversed' => invert_hex_color($value),
        ];
    }

    /**
     * Set the value of the attribute.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
