<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['company_admin', 'manager', 'accountant', 'technician', 'customer'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_user_create_succeeds_with_valid_data(): void
    {
        $company = Company::create(['name' => 'Test Company']);
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('company_admin');
        $company->users()->attach($admin->id, ['role' => 'company_admin']);

        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id])
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'newuser@test.com',
                'phone' => '9876543210',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'technician',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('created_user_email', 'newuser@test.com');
        $response->assertSessionHas('created_user_name', 'New User');
        $response->assertSessionHas('created_user_password');

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com', 'name' => 'New User']);
        $user = User::where('email', 'newuser@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('technician'));
        $this->assertTrue($company->users()->where('users.id', $user->id)->exists());
    }
}
