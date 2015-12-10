<?php
namespace FMUP\FlashMessenger;

class View
{
    static public function getPath()
    {
        return implode(DIRECTORY_SEPARATOR, array(__DIR__, 'View')) . DIRECTORY_SEPARATOR;
    }
}
