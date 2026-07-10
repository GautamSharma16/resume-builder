<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['Admin', 'admin@test.com', 'admin', 'password123'],
            ['Company', 'company@test.com', 'company', 'password123'],
            ['SEO', 'seo@test.com', 'seo', 'password123'],
            ['Developer', 'dev@test.com', 'developer', 'password123'],
            ['Article Writer', 'article@test.com', 'article_writer', 'password123'],
            ['User', 'user@test.com', 'user', 'password123'],
            ['Admin User', 'admin@cvbliss.test', 'admin', 'Super@123'],
            ['SEO Manager', 'seo@cvbliss.test', 'seo', 'Seo@123'],
            ['Developer', 'developer@cvbliss.test', 'developer', 'Dev@123'],
            ['Article Writer', 'writer@cvbliss.test', 'article_writer', 'Writer@123'],
            ['Admin User', 'siddhartha.verma@cvbliss.in', 'admin', 'Admin@123'],
            ['Developer Staff', 'developer@resumebuilder.com', 'developer', 'Dev@123'],
            ['SEO Staff', 'seo@resumebuilder.com', 'seo', 'Seo@123'],
            ['Article Staff', 'article@resumebuilder.com', 'article_writer', 'Article@123'],
            ['Company Staff', 'company@resumebuilder.com', 'company', 'Company@123'],
            ['Test User', 'user@resumebuilder.com', 'user', 'user@123'],
            ['Demo User', 'demo@resumebuilder.com', 'user', 'demo@123'],
        ];

        foreach ($users as [$name, $email, $role, $password]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'role' => $role,
                    'mobile' => '9876543210',
                    'provider' => 'email',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
