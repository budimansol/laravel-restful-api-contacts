<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SearchSeederContacts extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'test')->first();
        for($i = 0; $i < 50; $i++){
            Contact::create([
                'first_name' => 'first' . $i,
                'last_name' => 'last' . $i,
                'email' => Str::random(15) . '@example.com',
                'phone' => '628' . $i,
                'user_id' => $user->id
            ]);
        }
    }
}
