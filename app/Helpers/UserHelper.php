<?php

namespace App\Helpers;

class UserHelper
{
    public static function getDisplayName($user = null): string
    {
        $name = '';

        if (is_array($user)) {
            $name = $user['name'] ?? ($user['display_name'] ?? (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            $name = trim($name);
            if (!$name && !empty($user['email'])) {
                $name = strstr($user['email'], '@', true) ?: $user['email'];
            }
        } elseif (is_object($user)) {
            $first = property_exists($user, 'first_name') ? $user->first_name : '';
            $last  = property_exists($user, 'last_name') ? $user->last_name : '';
            $name  = trim((property_exists($user, 'name') ? $user->name : ($first . ' ' . $last)));
            if (!$name && property_exists($user, 'email') && !empty($user->email)) {
                $atPos = strpos($user->email, '@');
                $name = $atPos !== false ? substr($user->email, 0, $atPos) : $user->email;
            }
        }

        if (!$name) {
            // Fallback to session or generic label
            try {
                $session = session();
                $email = $session ? (string) $session->get('email') : '';
                if ($email) {
                    $atPos = strpos($email, '@');
                    $name = $atPos !== false ? substr($email, 0, $atPos) : $email;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (!$name) {
            $name = 'User';
        }

        return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }

    public static function getDisplayRole($user = null): string
    {
        $role = '';

        if (is_array($user)) {
            $role = $user['role'] ?? '';
        } elseif (is_object($user)) {
            $role = property_exists($user, 'role') ? (string) $user->role : '';
        }

        if (!$role) {
            try {
                $session = session();
                $role = $session ? (string) $session->get('role') : '';
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (!$role) {
            $role = 'Accountant';
        }

        return htmlspecialchars($role, ENT_QUOTES, 'UTF-8');
    }
}
