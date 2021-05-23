<?php

namespace App\Http\Controllers;

use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public static function sendEmailVerificationLink($user, $token)
    {
        $url = url('/email/verify?email='.$user['email'].'&token='.$token);
        $user->notify(new EmailVerificationNotification($url));
    }
}
