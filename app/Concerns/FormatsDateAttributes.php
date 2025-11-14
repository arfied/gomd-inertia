<?php

namespace App\Concerns;

use Carbon\Carbon;
use Exception;

trait FormatsDateAttributes
{
    /**
     * Safely format a date attribute that might be a string or Carbon instance
     *
     * @param string $attribute The attribute name
     * @param string $format The date format
     * @param string $default The default value if the attribute is null
     * @return string
     */
    public function formatDate($attribute, $format = 'M d, Y', $default = 'Not specified')
    {
        if (!$this->$attribute) {
            return $default;
        }

        if ($this->$attribute instanceof Carbon) {
            return $this->$attribute->format($format);
        }

        // Try to parse the string as a date
        try {
            return Carbon::parse($this->$attribute)->format($format);
        } catch (Exception $e) {
            // If parsing fails, return the original string
            return $this->$attribute;
        }
    }

    /**
     * Format a datetime attribute with time
     *
     * @param string $attribute The attribute name
     * @param string $format The date format
     * @param string $default The default value if the attribute is null
     * @return string
     */
    public function formatDateTime($attribute, $format = 'M d, Y g:i A', $default = 'Not specified')
    {
        return $this->formatDate($attribute, $format, $default);
    }

    /**
     * Format a date attribute as a relative time (e.g., "2 days ago")
     *
     * @param string $attribute The attribute name
     * @param string $default The default value if the attribute is null
     * @return string
     */
    public function formatDateDiffForHumans($attribute, $default = 'Not specified')
    {
        if (!$this->$attribute) {
            return $default;
        }

        if ($this->$attribute instanceof Carbon) {
            return $this->$attribute->diffForHumans();
        }

        try {
            return Carbon::parse($this->$attribute)->diffForHumans();
        } catch (Exception $e) {
            return $this->$attribute;
        }
    }

    /**
     * Format multiple date attributes
     *
     * @param array $attributes Array of attribute names
     * @param string $format The date format
     * @param string $default The default value if the attribute is null
     * @return array
     */
    public function formatDates(array $attributes, $format = 'M d, Y', $default = 'Not specified')
    {
        $formatted = [];
        foreach ($attributes as $attribute) {
            $formatted[$attribute] = $this->formatDate($attribute, $format, $default);
        }
        return $formatted;
    }

    /**
     * Get a property of a date attribute (like dayName, month, etc.)
     *
     * @param string $attribute The attribute name
     * @param string $property The Carbon property to get (dayName, month, year, etc.)
     * @param mixed $default The default value if the attribute is null or invalid
     * @return mixed
     */
    public function getDateProperty($attribute, $property, $default = null)
    {
        if (!$this->$attribute) {
            return $default;
        }

        if ($this->$attribute instanceof Carbon) {
            return $this->$attribute->$property;
        }

        try {
            return Carbon::parse($this->$attribute)->$property;
        } catch (Exception $e) {
            return $default;
        }
    }
}
