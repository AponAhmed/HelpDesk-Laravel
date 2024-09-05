<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('mail.{userId}.{mailChannel}.{box}', function ($user, $userId, $mailChannel, $box) {
    return (int) $user->id === (int) $userId;
});
