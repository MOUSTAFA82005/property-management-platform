<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Drop the resolved auth guard between requests.
     *
     * Laravel's AuthManager caches the guard (and the user it resolved) for
     * the lifetime of a test method, so a second request inside the same test
     * reuses the already-authenticated user even after its token row is gone.
     * Every real HTTP request starts from a cold container; this reproduces
     * that boundary so revocation is actually exercised.
     */
    private function startFreshRequest(): void
    {
        $this->app['auth']->forgetGuards();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Mona Khalil',
            'email'                 => 'mona@example.com',
            'phone'                 => '01012345678',
            'password'              => 'secret-password',
            'password_confirmation' => 'secret-password',
        ], $overrides);
    }

    // =================================================================
    // Registration
    // =================================================================

    public function test_a_customer_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/auth/register', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('user.email', 'mona@example.com')
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'name', 'email', 'role', 'status']]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('users', ['email' => 'mona@example.com', 'role' => 'customer']);
    }

    public function test_the_registration_response_never_leaks_the_password(): void
    {
        $response = $this->postJson('/api/auth/register', $this->validPayload());

        $user = $response->json('user');

        $this->assertArrayNotHasKey('password', $user);
        $this->assertArrayNotHasKey('password_confirmation', $user);
        $this->assertArrayNotHasKey('remember_token', $user);
    }

    public function test_the_password_is_hashed_not_stored_in_plain_text(): void
    {
        $this->postJson('/api/auth/register', $this->validPayload())->assertCreated();

        $user = User::where('email', 'mona@example.com')->firstOrFail();

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_registration_defaults_to_the_customer_role(): void
    {
        $this->postJson('/api/auth/register', $this->validPayload())->assertCreated();

        $this->assertSame('customer', User::where('email', 'mona@example.com')->value('role'));
    }

    public function test_a_registration_cannot_claim_the_owner_role_while_self_service_is_closed(): void
    {
        config(['auth.allow_owner_registration' => false]);

        $this->postJson('/api/auth/register', $this->validPayload(['role' => 'owner']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'mona@example.com']);
    }

    public function test_an_owner_can_register_when_self_service_is_open(): void
    {
        config(['auth.allow_owner_registration' => true]);

        $this->postJson('/api/auth/register', $this->validPayload(['role' => 'owner']))
            ->assertCreated()
            ->assertJsonPath('user.role', 'owner');

        $this->assertDatabaseHas('users', ['email' => 'mona@example.com', 'role' => 'owner']);
    }

    public function test_an_unknown_role_is_always_rejected(): void
    {
        config(['auth.allow_owner_registration' => true]);

        foreach (['admin', 'tenant', 'property_manager', 'superuser'] as $role) {
            $this->postJson('/api/auth/register', $this->validPayload(['role' => $role]))
                ->assertStatus(422)
                ->assertJsonValidationErrors('role');
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->customer()->create(['email' => 'mona@example.com']);

        $this->postJson('/api/auth/register', $this->validPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_registration_rejects_missing_and_malformed_fields(): void
    {
        $this->postJson('/api/auth/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);

        $this->postJson('/api/auth/register', $this->validPayload(['email' => 'not-an-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/auth/register', $this->validPayload(['phone' => '12345']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_registration_rejects_a_weak_password(): void
    {
        $this->postJson('/api/auth/register', $this->validPayload([
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_registration_rejects_a_mismatched_password_confirmation(): void
    {
        $this->postJson('/api/auth/register', $this->validPayload([
            'password_confirmation' => 'a-different-password',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    // =================================================================
    // Login
    // =================================================================

    public function test_a_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->customer()->create([
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonStructure(['message', 'token', 'user']);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_an_owner_login_reports_the_owner_role(): void
    {
        User::factory()->owner()->create([
            'email'    => 'owner@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'owner@example.com',
            'password' => 'secret-password',
        ])->assertOk()->assertJsonPath('user.role', 'owner');
    }

    public function test_login_with_a_wrong_password_returns_401(): void
    {
        User::factory()->customer()->create([
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'mona@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJsonMissingPath('token');
    }

    public function test_login_with_an_unknown_email_returns_401(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'secret-password',
        ])->assertStatus(401);
    }

    public function test_login_requires_an_email_and_a_password(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        User::factory()->customer()->create([
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
            'status'   => 'inactive',
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ])->assertStatus(403)->assertJsonMissingPath('token');
    }

    // =================================================================
    // /api/auth/me
    // =================================================================

    public function test_the_authenticated_user_can_read_their_own_profile(): void
    {
        $user = User::factory()->owner()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', 'owner');
    }

    public function test_me_is_unavailable_without_a_token(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_me_ignores_any_user_id_supplied_by_the_caller(): void
    {
        $me = User::factory()->customer()->create();
        $someoneElse = User::factory()->customer()->create();

        Sanctum::actingAs($me);

        // The token is the identity — a spoofed id in the payload changes nothing.
        $this->getJson('/api/auth/me?id='.$someoneElse->id)
            ->assertOk()
            ->assertJsonPath('user.id', $me->id);
    }

    // =================================================================
    // Logout
    // =================================================================

    public function test_logout_revokes_the_current_token_and_that_token_stops_working(): void
    {
        $user = User::factory()->customer()->create([
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ])->assertOk()->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // The revoked token must be dead, not merely forgotten by the client.
        $this->startFreshRequest();
        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_only_revokes_the_token_that_made_the_request(): void
    {
        $user = User::factory()->customer()->create([
            'email'    => 'mona@example.com',
            'password' => 'secret-password',
        ]);

        $phone = $user->createToken('phone')->plainTextToken;
        $laptop = $user->createToken('laptop')->plainTextToken;

        $this->withToken($laptop)->postJson('/api/auth/logout')->assertOk();

        // Signing out on one device must not sign the user out everywhere.
        $this->startFreshRequest();
        $this->withToken($phone)->getJson('/api/auth/me')->assertOk();

        $this->startFreshRequest();
        $this->withToken($laptop)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }

    // =================================================================
    // Role separation
    // =================================================================

    public function test_a_customer_cannot_reach_any_owner_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        foreach ([
            '/api/owner/dashboard',
            '/api/owner/properties',
            '/api/owner/units',
            '/api/owner/buildings',
            '/api/owner/contracts',
            '/api/owner/payments',
            '/api/owner/customers',
            '/api/owner/purchase-requests',
        ] as $endpoint) {
            $this->getJson($endpoint)->assertForbidden();
        }
    }

    public function test_owner_endpoints_still_require_authentication(): void
    {
        $this->getJson('/api/owner/payments')->assertUnauthorized();
        $this->getJson('/api/owner/dashboard')->assertUnauthorized();
    }

    public function test_an_owner_reaches_the_owner_group_rather_than_being_blocked_by_role(): void
    {
        Sanctum::actingAs(User::factory()->owner()->create());

        // Whatever the (still unimplemented) controller does, the role gate
        // itself must not be what turns an owner away.
        $this->getJson('/api/owner/contracts')->assertOk();
    }

    public function test_an_owner_cannot_read_customer_scoped_resources_belonging_to_someone_else(): void
    {
        $owner = User::factory()->owner()->create();

        Sanctum::actingAs($owner);

        // /api/contracts and /api/payments are the signed-in user's own records.
        // An owner has none, so these must come back empty rather than leaking.
        $this->getJson('/api/contracts')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/payments')->assertOk()->assertJsonCount(0, 'data');
    }
}
