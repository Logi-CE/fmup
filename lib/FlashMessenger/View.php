<?php
namespace FMUP\FlashMessenger;

class View
{
    public static function getPath()
    {
        return implode(DIRECTORY_SEPARATOR, array(__DIR__, 'View')) . DIRECTORY_SEPARATOR;
    }
}
