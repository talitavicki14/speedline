<?php

namespace App\Enums;

class BookingConfig
{
    public const OPEN_HOUR = 8;
    public const CLOSE_HOUR = 16;

    public static function getSlots(): array
    {
        self::validateConfig();

        $slots = [];
        for ($i = self::OPEN_HOUR; $i <= self::CLOSE_HOUR; $i++) {
            $slots[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }
        return $slots;
    }

    public static function lastSlot(): string
    {
        return str_pad(self::CLOSE_HOUR, 2, '0', STR_PAD_LEFT) . ':00';
    }

    public static function getOptions(): array
    {
        return array_map(fn($slot) => [
            'value' => $slot,
            'label' => $slot . ' WIB'
        ], self::getSlots());
    }

    public static function formatRange(): string
    {
        return sprintf('%s:00–%s:00',
            str_pad(self::OPEN_HOUR, 2, '0', STR_PAD_LEFT),
            str_pad(self::CLOSE_HOUR, 2, '0', STR_PAD_LEFT)
        );
    }

    private static function validateConfig(): void
    {
        if (self::CLOSE_HOUR >= 24) {
            throw new \Exception("Close hour must be less than 24.");
        }

        if ((self::CLOSE_HOUR - self::OPEN_HOUR) < 3) {
            throw new \Exception("The gap between open and close hours must be at least 3 hours.");
        }
    }
}
