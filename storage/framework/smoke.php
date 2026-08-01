<?php

$u = App\Models\User::where('email', 'admin@simika.com')->first();

if ($u === null) {
    echo 'USER MISSING' . PHP_EOL;
} else {
    echo 'USER EXISTS status=' . $u->status
        . ' hash_ok=' . (Illuminate\Support\Facades\Hash::check('Admin@12345', $u->password) ? 'YES' : 'NO')
        . PHP_EOL;
}

echo 'total users: ' . App\Models\User::count() . PHP_EOL;
