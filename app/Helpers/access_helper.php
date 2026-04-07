<?php

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->inGroup('admin');
    }
}

if (! function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $user = auth()->user();

        return $user?->id;
    }
}

if (! function_exists('can_access_owner')) {
    function can_access_owner(int $ownerId): bool
    {
        $currentUserId = current_user_id();

        if ($currentUserId === null) {
            return false;
        }

        return is_admin() || $currentUserId === $ownerId;
    }
}
