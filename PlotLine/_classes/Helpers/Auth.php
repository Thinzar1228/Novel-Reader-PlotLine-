<?php
namespace Helpers;

class Auth
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        // Use the Helper to stay in the plotline folder
        HTTP::redirect("/index.php");
    }

    public static function isAdmin() {
        $user = self::check(); // This handles session_start and basic login check
        
        if ($user && ((int)$user->role_id === 3 || (int)$user->role_id === 2)) {
            return $user;
        }

        // If not admin/manager, send back to home or login
        HTTP::redirect("/index.php", "error=unauthorized");
    }
}