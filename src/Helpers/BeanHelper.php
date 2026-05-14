<?php

declare(strict_types=1);

namespace App\Helpers;

class BeanHelper
{
    public static function castBeanProperties(\RedBeanPHP\OODBBean $bean): \RedBeanPHP\OODBBean
    {
        if (isset($bean->id)) {
            $bean->id = (int) $bean->id;
        }

        // FK fields all need int - templates and controllers rely on this
        $intFields = [
            'category_id',
            'venue_id',
            'user_id',
            'order_id',
            'ticket_id',
            'event_id',
            'quantity',
            'capacity',
        ];
        
        foreach ($intFields as $field) {
            if (isset($bean->$field)) {
                $bean->$field = (int) $bean->$field;
            }
        }
        
        return $bean;
    }
    
    public static function castBeanArray(array $beans): array
    {
        return array_map([self::class, 'castBeanProperties'], $beans);
    }

    public static function toInt(mixed $value): int
    {
        return (int) $value;
    }

    public static function isValidBean(?\RedBeanPHP\OODBBean $bean): bool
    {
        return $bean !== null && isset($bean->id) && $bean->id > 0;
    }
}
