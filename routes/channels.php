<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{user_id}', fn (User $user, string $user_id): bool => (string) $user->getKey() === $user_id);
