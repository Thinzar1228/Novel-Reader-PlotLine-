<?php
namespace Helpers;

class HTTP
{
    // Ensure this matches your folder name in htdocs
    static $base = "http://localhost/plotline"; 

    static function redirect($page, $q = "")
    {
        // Add a slash only if the $page doesn't have one
        $path = (strpos($page, '/') === 0) ? $page : "/$page";
        $url = static::$base . $path;
        
        if($q) $url .= "?$q";

        header("location: $url");
        exit();
    }
}