<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
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
    
    public function testGetUserSuccess(){
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            "data" => [
                'username' => 'test',
                'first_name' => 'test',
            ]
        ]);
    }
    
    public function testUnauthorized(){
        $this->get('/api/users/current')
        ->assertStatus(401)
        ->assertJson([
            "errors" => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
    
    public function testInvalidToken(){
        $this->seed([UserSeeder::class]);
        
        $this->get('/api/users/current', [
            'Authorization' => '123'
        ])->assertStatus(401)
        ->assertJson([
            "errors" => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
    
    public function testUpdateName(){
        $this->seed([UserSeeder::class]);
        
        $oldUser = User::where('username', 'test')->first();
        
        $this->patch('/api/users/current', [
            'last_name' => 'Budiman'
        ],[
            'Authorization' => 'test'
        ])
        ->assertStatus(200)
        ->assertJson([
            "data" => [
                'username' => 'test',
                'last_name' => 'Budiman'
            ]
        ]);
        
        $newUser = User::where('username', 'test')->first();
        $this->assertNotEquals($oldUser->last_name, $newUser->last_name);
    }
    
    public function testUpdatePassword(){
        $this->seed([UserSeeder::class]);
        
        $oldUser = User::where('username', 'test')->first();
        
        $this->patch('/api/users/current', [
            'password' => '123123'
        ],[
            'Authorization' => 'test'
        ])
        ->assertStatus(200)
        ->assertJson([
            "data" => [
                'username' => 'test'
            ]
        ]);
        
        $newUser = User::where('username', 'test')->first();
        $this->assertNotEquals($oldUser->password, $newUser->password);
    }
    
    public function testUpdateFailed(){
        $this->seed([UserSeeder::class]);        
        $this->patch('/api/users/current', [
            'first_name' => '12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n12312312312wqofjweifewionio23n'
        ],[
            'Authorization' => 'test'
        ])
        ->assertStatus(400)
        ->assertJson([
            "errors" => [
                'first_name' => [
                    'The first name field must not be greater than 100 characters.'
                ]
            ]
        ]);
    }
    
    public function testLogoutSuccess(){
        $this->seed([UserSeeder::class]);
        $this->delete(uri : '/api/users/logout', headers : [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
        $user = User::where('username', 'test')->first();
        $this->assertNull($user->token);
    }
    
    public function testLogoutFailed(){
        $this->seed([UserSeeder::class]);
        $this->delete('/api/users/logout', [
            'Authorization' => '123123'
        ])->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
}