<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeederContacts;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateSuccess(){
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'first_name' => 'budiman',
            'last_name' => 'dzakky',
            'email' => 'test@mail.com',
            'phone' => '0812313213'
        ],[
            'Authorization' => 'test'
        ])
        ->assertStatus(201)
        ->assertJson([
            'data' => [
                'first_name' => 'budiman',
                'last_name' => 'dzakky',
                'email' => 'test@mail.com',
                'phone' => '0812313213'
            ]
        ]);
    }
    
    public function testCreateFailed(){
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'first_name' => '',
            'last_name' => 'abcabcabcaslnasaabcabcabcaslnasaabcabcabcaslnasaabcabcabcaslnasaabcabcabcaslnasaabcabcabcaslnasaabcabcabcaslnasaabcabcabcaslnasa',
            'email' => '1231232',
            'phone' => 'ininomorhpsiapaininomorhpsiapaininomorhpsiapaininomorhpsiapaininomorhpsiapaininomorhpsiapaininomorhpsiapaininomorhpsiapaininomorhpsiap'
        ],[
            'Authorization' => 'test'
        ])
        ->assertStatus(400)
        ->assertJson([
            'errors' => [
                'first_name' => [
                    'The first name field is required.'
                ],
                'last_name' => [
                    'The last name field must not be greater than 100 characters.'
                ],
                'email' => [
                    'The email field must be a valid email address.'
                ], 
                'phone' => [
                    'The phone field must not be greater than 20 characters.'
                ]
            ]
        ]);
    }
    
    public function testUnauthorized(){
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'first_name' => 'budiman',
            'last_name' => 'dzakky',
            'email' => 'test@mail.com',
            'phone' => '0812313213'
        ],[
            'Authorization' => 'salalaalala'
        ])
        ->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
    
    public function testGetOneSuccess(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        
        $contact = Contact::query()->limit(1)->first();
        
        $this->get('/api/contacts/'. $contact->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $contact->id,
                'first_name' => 'test',
                'last_name' => 'test',
                'email' => 'test@mail.com',
                'phone' => '12345678'
            ]
        ]);
    }
    
    public function testGetOneNotFound(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $this->get('/api/contacts/1', [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Not Found'
                ]
            ]
        ]);
    }
    
    public function testGetOneOtherUser(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        
        $contact = Contact::query()->limit(1)->first();
        
        $this->get('/api/contacts/'. $contact->id, [
            'Authorization' => 'test2'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Not Found'
                ]
            ]
        ]);
    }
    
    public function testUpdateSuccess(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        
        $contact = Contact::query()->limit(1)->first();
        
        $this->put('/api/contacts/'. $contact->id,[
                'first_name' => 'test2',
                'last_name' => 'test2',
                'email' => 'test22@mail.com',
                'phone' => '12345678222'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'first_name' => 'test2',
                'last_name' => 'test2',
                'email' => 'test22@mail.com',
                'phone' => '12345678222'
            ]
        ]);
    }
    
    public function testUpdateValidationError(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        
        $contact = Contact::query()->limit(1)->first();
        
        $this->put('/api/contacts/'. $contact->id,[
                'first_name' => '',
                'last_name' => 'test2',
                'email' => 'test22@mail.com',
                'phone' => '12345678222'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
        ->assertJson([
            'errors' => [
                'first_name' => [
                    'The first name field is required.'
                ]
            ]
        ]);
    }
    
    public function testDelete(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        
        $contact = Contact::query()->limit(1)->first();
        
        $this->delete('/api/contacts/'. $contact->id,[], [
            'Authorization' => 'test'
        ])->assertStatus(200)
        ->assertJson([
            'data' => true
        ]);
    }
    
    public function testDeleteNotFound(){
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        
        $contact = Contact::query()->limit(1)->first();
        
        $this->delete('/api/contacts/'. ($contact->id + 1), [], [
            'Authorization' => 'test'
        ])->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => [
                    'Not Found'
                ]
            ]
        ]);
    }
    
    public function testSearchByFirstName(){
        $this->seed([UserSeeder::class, SearchSeederContacts::class ]);
        
        $response = $this->get('/api/contacts?name=first', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        $this->assertEquals(10, count($response['data']));
        $this->assertEquals(50, $response["meta"]["total"]);
    }
    
    public function testSearchByLastName(){
        $this->seed([UserSeeder::class, SearchSeederContacts::class ]);
        
        $response = $this->get('/api/contacts?name=last', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        $this->assertEquals(10, count($response['data']));
        $this->assertEquals(50, $response["meta"]["total"]);
    }
    
    public function testSearchByEmail(){
        $this->seed([UserSeeder::class, SearchSeederContacts::class ]);
        
        $response = $this->get('/api/contacts?email=@example.com', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        $this->assertEquals(10, count($response['data']));
        $this->assertEquals(50, $response["meta"]["total"]);
    }
    
    public function testSearchByPhone(){
        $this->seed([UserSeeder::class, SearchSeederContacts::class ]);
        
        $response = $this->get('/api/contacts?phone=628', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        $this->assertEquals(10, count($response['data']));
        $this->assertEquals(50, $response["meta"]["total"]);
    }
    
    public function testSearchNotFound(){
        $this->seed([UserSeeder::class, SearchSeederContacts::class ]);
        
        $response = $this->get('/api/contacts?name=notfounding', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        $this->assertEquals(0, count($response['data']));
        $this->assertEquals(0, $response["meta"]["total"]);
    }
    
    public function testSeachWithPage(){
        $this->seed([UserSeeder::class, SearchSeederContacts::class ]);
        
        $response = $this->get('/api/contacts?size=5&page=2', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();
        Log::info(json_encode($response, JSON_PRETTY_PRINT));
        $this->assertEquals(5, count($response['data']));
        $this->assertEquals(50, $response["meta"]["total"]);
        $this->assertEquals(2, $response["meta"]["current_page"]);
    }
    
}
