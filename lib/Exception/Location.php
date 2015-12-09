<?php
namespace FMUP\Exception;

class Location extends \FMUP\Exception
{
    public function getLocation()
    {
        $path = $this->getMessage();
        if (strpos($path, '://') === false) {
            if ($path{0} != '/') {
                $path = '/' . $path;
            }
        }
        return $path;
    }
}
