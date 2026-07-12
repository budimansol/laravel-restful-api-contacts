<?php

namespace Tests\Feature;

use App\Models\User;
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
    
}
