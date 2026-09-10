<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Two accounts to sign in with while developing.
 *
 * No password is hard-coded. FORMIVA_SEED_PASSWORD is used when set;
 * otherwise a random one is generated and printed once, which means nothing
 * shipped in this repository can ever be a working credential somewhere it
 * should not be. Existing accounts are left alone — re-seeding never resets
 * a password someone has already changed.
 */
final class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Staff seeding skipped: not for production.');

            return;
        }

        $password = (string) (env('FORMIVA_SEED_PASSWORD') ?: Str::password(16));
        $generated = env('FORMIVA_SEED_PASSWORD') === null;
        $created = [];

        foreach ([
            ['name' => 'Studio Admin', 'email' => 'admin@formiva.test', 'role' => UserRole::Admin],
            ['name' => 'Studio Editor', 'email' => 'editor@formiva.test', 'role' => UserRole::Editor],
        ] as $account) {
            if (User::query()->where('email', $account['email'])->exists()) {
                continue;
            }

            User::query()->create([
                ...$account,
                'password' => Hash::make($password),
                'is_active' => true,
            ]);

            $created[] = $account['email'];
        }

        if ($created === []) {
            $this->command?->info('Staff accounts already exist — left untouched.');

            return;
        }

        $this->command?->info('Created: '.implode(', ', $created));

        if ($generated) {
            $this->command?->warn('Generated password (shown once): '.$password);
        }
    }
}
