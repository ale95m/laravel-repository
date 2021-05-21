<?php


namespace Easy\Helpers;


class Translate
{
    public static function translateAttribute(string $attribute)
    {
        $translate_route = 'validation.attributes.' . $attribute;
        $attribute_name = trans($translate_route);
        return $translate_route == $attribute_name ? $attribute : $attribute_name;
    }
}
