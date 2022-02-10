<?php

declare(strict_types=1);

namespace App\Enum;

class DatumTypeEnum
{
    public const TYPE_TEXT = 'text';
    public const TYPE_COUNTRY = 'country';
    public const TYPE_DATE = 'date';
    public const TYPE_RATING = 'rating';
    public const TYPE_NUMBER = 'number';

    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
    public const TYPE_SIGN = 'sign';

    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_COUNTRY,
        self::TYPE_DATE,
        self::TYPE_RATING,
        self::TYPE_NUMBER,
        self::TYPE_IMAGE,
        self::TYPE_FILE,
        self::TYPE_SIGN,
    ];

    public const TYPES_TRANS_KEYS = [
        self::TYPE_TEXT => 'label.text',
        self::TYPE_SIGN => 'label.sign',
        self::TYPE_IMAGE => 'label.image',
        self::TYPE_COUNTRY => 'label.country',
        self::TYPE_FILE => 'label.file',
        self::TYPE_DATE => 'label.date',
        self::TYPE_RATING => 'label.rating',
        self::TYPE_NUMBER => 'label.number',
    ];

    public static function getTypesLabels() : array
    {
        return self::TYPES_TRANS_KEYS;
    }

    public static function getTypeLabel(string $type) : string
    {
        return self::TYPES_TRANS_KEYS[$type];
    }
}
