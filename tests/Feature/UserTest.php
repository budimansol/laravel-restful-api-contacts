<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess(){
        $this->post('/api/users', [
            'username' => 'Dzakky',
            'password' => 'rahasia',
            'first_name' => 'Dzakky',
            'last_name' => 'Budiman'
        ])->assertStatus(201)
        ->assertJson([
                "data" => [
                    'username' => 'Dzakky',
                    'first_name' => 'Dzakky',
                    'last_name' => 'Budiman'
                ]
            ]
        );
    }
    
    public function testRegisterFailed(){
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'first_name' => '',
            'last_name' => ''
        ])->assertStatus(400)
        ->assertJson([
                "errors" => [
                    'username' => [
                        'The username field is required.'
                    ],
                    'password' => [
                        "The password field is required."
                    ],
                    'first_name' => [
                        "The first name field is required." 
                    ]
                ]
            ]
        );
    }
    
    public function testRegisterDuplicate(){
        $this->testRegisterSuccess();
        
        $this->post('/api/users', [
            'username' => 'Dzakky',
            'password' => 'rahasia',
            'first_name' => 'Dzakky',
            'last_name' => 'Budiman'
        ])->assertStatus(400)
        ->assertJson([
                "errors" => [
                    'username' => [
                        'Username already exist'
                    ]
                ]
            ]
        );
    }
    
    public function testreRegisterUser(){
        $this->testRegisterSuccess();
        User::query()->delete();
        $this->post('/api/users', [
            'username' => 'Dzakky',
            'password' => 'rahasia',
            'first_name' => 'Dzakky',
            'last_name' => 'Budiman'
        ])->assertStatus(201)
        ->assertJson([
                "data" => [
                    'username' => 'Dzakky',
                    'first_name' => 'Dzakky',
                    'last_name' => 'Budiman'
                ]
            ]
        );
    }
    
    public function testLoginSuccess(){
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            "data" => [
                'username' => 'test',
                'first_name' => 'test'
            ]
        ]);
        
        $user = User::where('username', 'test')->first();
        $this->assertNotNull($user->token);
    }
    
    public function testLoginUserNotFound(){
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                'message' => [
                    'Username or Password is Wrong'
                ]
            ]
        ]);
    }
    
    public function testLoginPasswordWrong(){
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => '123123'
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                'message' => [
                    'Username or Password is Wrong'
                ]
            ]
        ]);
    }
    
    public function testLoginUserDeleted(){
        $this->seed([UserSeeder::class]);
        User::query()->delete();
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                'message' => [
                    'Username or Password is Wrong'
                ]
            ]
        ]);
    }
    
}
