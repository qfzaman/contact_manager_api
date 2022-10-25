<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use \App\Models\Article;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function an_unauthenticated_user_should_redirected_to_login()
    {
        $response = $this->post('/api/contacts', $this->data());

        $response->assertRedirect('/login');

        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function a_list_of_contacts_can_be_fetched_for_the_authenticated_user()
    {
        $user = User::factory()->create();

        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $contact = Contact::factory()->create(['user_id' => $user->id]);
        $anotherContact = Contact::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->get('/api/contacts');

        $response->assertJsonCount(1)
            ->assertJson([
                'data' => [
                    [
                        'data' => [
                            'contact_id' => $contact->id
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_add_a_contact()
    {

        Sanctum::actingAs($this->user);

        $response = $this->post('/api/contacts', $this->data());

        $contact = Contact::first();

        $this->assertEquals('Test Name', $contact->name);
        $this->assertEquals('test@email.com', $contact->email);
        $this->assertEquals('05/14/1988', $contact->birthday->format('m/d/Y'));
        $this->assertEquals('ABC String', $contact->company);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
            ],
            'links' => [
                'self' => $contact->path(),
            ]
        ]);
    }

    /** @test */
    public function fields_are_required()
    {
        Sanctum::actingAs($this->user);

        collect(['name', 'email', 'birthday', 'company'])
            ->each(function ($field) {
                $response = $this->post(
                    '/api/contacts',
                    array_merge($this->data(), [$field => ''])
                );

                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Contact::all());
            });
    }

    /** @test */
    public function email_must_be_a_valid_email()
    {
        Sanctum::actingAs($this->user);

        $response = $this->post(
            '/api/contacts',
            array_merge($this->data(), ['email' => 'Not AN Email'])
        );

        $response->assertSessionHasErrors('email');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function birthdays_are_properly_stored()
    {
        Sanctum::actingAs($this->user);

        $response = $this->post(
            '/api/contacts',
            array_merge($this->data())
        );

        $this->assertCount(1, Contact::all());
        $this->assertInstanceOf(Carbon::class, Contact::first()->birthday);
        $this->assertEquals('05-14-1988', Contact::first()->birthday->format('m-d-Y'));
    }

    /** @test */
    public function a_contact_can_be_retrieved()
    {
        $this->withoutExceptionHandling();

        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get('/api/contacts/' . $contact->id);

        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'birthday' => $contact->birthday->format('m/d/Y'),
                'company' => $contact->company,
                'last_updated' => $contact->updated_at->diffForHumans(),
            ]
        ]);
    }

    /** @test */
    public function only_the_users_contacts_can_be_retrieved()
    {


        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);

        $response = $this->get('/api/contacts/' . $contact->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_contact_can_be_patched()
    {
        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->patch('/api/contacts/' . $contact->id, $this->data());

        $contact = $contact->fresh();

        $this->assertEquals('Test Name', $contact->name);
        $this->assertEquals('test@email.com', $contact->email);
        $this->assertEquals('05/14/1988', $contact->birthday->format('m/d/Y'));
        $this->assertEquals('ABC String', $contact->company);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'contact_id' => $contact->id,
            ],
            'links' => [
                'self' => $contact->path(),
            ]
        ]);
    }

    /** @test */
    public function only_the_owner_of_the_contact_can_patch_the_contact()
    {
        $contact = Contact::factory()->create();

        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);

        $response = $this->patch('/api/contacts/' . $contact->id, $this->data());

        $response->assertStatus(403);
    }

    /** @test */
    public function a_contact_can_be_deleted()
    {
        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(
            '/api/contacts/' . $contact->id
        );

        $this->assertCount(0, Contact::all());
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function only_the_owner_can_delete_the_contact()
    {
        $contact = Contact::factory()->create();

        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);

        $response = $this->delete('/api/contacts/' . $contact->id);

        $response->assertStatus(403);
    }

    private function data()
    {
        return [
            'name' => 'Test Name',
            'email' => 'test@email.com',
            'birthday' => '05/14/1988',
            'company' => 'ABC String',
        ];
    }
}
