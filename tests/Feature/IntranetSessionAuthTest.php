<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\IntranetJwt;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IntranetSessionAuthTest extends TestCase
{
    use RefreshDatabase;

    private string $jwtSecret = 'test-intranet-jwt-secret-key-2026-tableros';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.secret_rh' => $this->jwtSecret,
            'jwt.cookie_name' => 'token',
            'jwt.require_intranet_cookie' => true,
        ]);

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Super Administrador', 'estado' => 2],
            ['id' => 2, 'name' => 'Administrador', 'estado' => 2],
            ['id' => 3, 'name' => 'Limitado', 'estado' => 2],
            ['id' => 4, 'name' => 'Consultor', 'estado' => 2],
        ]);
    }

    private function makeIntranetJwt(array $overrides = []): string
    {
        $payload = array_merge([
            'id_colaborador' => 'RH-1001',
            'numero_colaborador' => '1001',
            'nombre' => 'Colaborador Prueba',
            'correo' => 'prueba@ldrhumanresources.com',
            'marca' => 'LDR',
            'puesto' => 'Analista',
            'area' => 'TI',
            'nombre_sede' => 'CDMX',
            'iat' => time() - 60,
            'exp' => time() + 3600,
        ], $overrides);

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function createUserWithToken(array $attributes = []): array
    {
        $user = User::create(array_merge([
            'role_id' => 2,
            'external_rh_id' => 'RH-1001',
            'collaborator_number' => '1001',
            'name' => 'Colaborador Prueba',
            'email' => 'prueba@ldrhumanresources.com',
            'password' => Hash::make('secret-password'),
            'estado' => 2,
        ], $attributes));

        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    public function test_login_fails_with_session_expired_code_when_cookie_is_missing(): void
    {
        $response = $this->postJson('/api/login');

        $response->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_SESSION_EXPIRED,
            ]);
    }

    public function test_login_fails_with_session_expired_code_when_cookie_jwt_is_expired(): void
    {
        $expiredJwt = $this->makeIntranetJwt([
            'iat' => time() - 7200,
            'exp' => time() - 3600,
        ]);

        $response = $this->withCredentials()
            ->withUnencryptedCookie('token', $expiredJwt)
            ->postJson('/api/login');

        $response->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_SESSION_EXPIRED,
            ]);
    }

    public function test_login_fails_when_cookie_jwt_has_invalid_signature(): void
    {
        $badJwt = JWT::encode([
            'id_colaborador' => 'RH-1001',
            'correo' => 'prueba@ldrhumanresources.com',
            'exp' => time() + 3600,
        ], 'wrong-secret-key-for-invalid-signature', 'HS256');

        $response = $this->withCredentials()
            ->withUnencryptedCookie('token', $badJwt)
            ->postJson('/api/login');

        $response->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_SESSION_EXPIRED,
            ]);
    }

    public function test_login_succeeds_with_valid_intranet_cookie(): void
    {
        $validJwt = $this->makeIntranetJwt();

        $response = $this->withCredentials()
            ->withUnencryptedCookie('token', $validJwt)
            ->postJson('/api/login');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'external_rh_id', 'email', 'role_id'],
            ]);

        $this->assertDatabaseHas('users', [
            'external_rh_id' => 'RH-1001',
            'email' => 'prueba@ldrhumanresources.com',
            'role_id' => 3,
        ]);
    }

    public function test_protected_routes_succeed_when_sanctum_token_and_intranet_cookie_are_valid(): void
    {
        [, $token] = $this->createUserWithToken();
        $validJwt = $this->makeIntranetJwt();

        $this->withCredentials()
            ->withUnencryptedCookie('token', $validJwt)
            ->withToken($token)
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJsonPath('user.email', 'prueba@ldrhumanresources.com');

        $this->withCredentials()
            ->withUnencryptedCookie('token', $validJwt)
            ->withToken($token)
            ->getJson('/api/session/check')
            ->assertStatus(200)
            ->assertJson(['authenticated' => true]);
    }

    public function test_protected_routes_fail_when_intranet_cookie_is_removed(): void
    {
        [, $token] = $this->createUserWithToken();

        $this->withToken($token)
            ->getJson('/api/session/check')
            ->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_SESSION_EXPIRED,
            ]);

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_SESSION_EXPIRED,
            ]);
    }

    public function test_protected_routes_fail_when_intranet_cookie_expires(): void
    {
        [, $token] = $this->createUserWithToken();
        $expiredJwt = $this->makeIntranetJwt([
            'iat' => time() - 7200,
            'exp' => time() - 10,
        ]);

        $this->withCredentials()
            ->withUnencryptedCookie('token', $expiredJwt)
            ->withToken($token)
            ->getJson('/api/session/check')
            ->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_SESSION_EXPIRED,
            ]);
    }

    public function test_protected_routes_return_user_mismatch_when_cookie_belongs_to_another_user(): void
    {
        [, $token] = $this->createUserWithToken([
            'external_rh_id' => 'RH-1001',
            'email' => 'usuario1@ldrhumanresources.com',
        ]);

        $otherUserJwt = $this->makeIntranetJwt([
            'id_colaborador' => 'RH-2002',
            'numero_colaborador' => '2002',
            'correo' => 'usuario2@ldrhumanresources.com',
        ]);

        $this->withCredentials()
            ->withUnencryptedCookie('token', $otherUserJwt)
            ->withToken($token)
            ->getJson('/api/session/check')
            ->assertStatus(401)
            ->assertJson([
                'code' => IntranetJwt::CODE_USER_MISMATCH,
            ]);
    }

    public function test_local_collaborator_mode_works_without_intranet_cookie_when_flag_is_disabled(): void
    {
        config(['jwt.require_intranet_cookie' => false]);
        [, $token] = $this->createUserWithToken();

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJsonPath('user.email', 'prueba@ldrhumanresources.com');

        $this->withToken($token)
            ->getJson('/api/session/check')
            ->assertStatus(200)
            ->assertJson(['authenticated' => true]);
    }
}
