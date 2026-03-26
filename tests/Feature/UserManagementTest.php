<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // --- Access Control ---

    public function test_admin_can_access_user_index(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_manager_can_access_user_index(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $this->actingAs($manager)->get(route('users.index'))->assertOk();
    }

    public function test_employee_cannot_access_user_index(): void
    {
        $employee = User::factory()->create(['role' => 'employee_base']);
        $this->actingAs($employee)->get(route('users.index'))->assertForbidden();
    }

    public function test_requester_cannot_access_user_index(): void
    {
        $requester = User::factory()->create(['role' => 'requester']);
        $this->actingAs($requester)->get(route('users.index'))->assertForbidden();
    }

    // --- Admin sees all users ---

    public function test_admin_sees_all_users(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $employee = User::factory()->create(['role' => 'employee_base']);
        $requester = User::factory()->create(['role' => 'requester']);

        $response = $this->actingAs($admin)->get(route('users.index'));
        $response->assertOk();
        $response->assertSeeText($admin->name);
        $response->assertSeeText($manager->name);
        $response->assertSeeText($employee->name);
        $response->assertSeeText($requester->name);
    }

    // --- Manager sees only employee_base and requester ---

    public function test_manager_sees_only_employees_and_requesters(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin', 'name' => 'AdminOnly Person']);
        $manager = User::factory()->create(['role' => 'employee_manager', 'name' => 'Current Manager']);
        $otherManager = User::factory()->create(['role' => 'employee_manager', 'name' => 'OtherMgr Person']);
        $employee = User::factory()->create(['role' => 'employee_base', 'name' => 'Base Employee']);
        $requester = User::factory()->create(['role' => 'requester', 'name' => 'Requester Person']);

        $response = $this->actingAs($manager)->get(route('users.index'));
        $response->assertOk();
        $response->assertDontSeeText('AdminOnly Person');
        $response->assertDontSeeText('OtherMgr Person');
        $response->assertSeeText('Base Employee');
        $response->assertSeeText('Requester Person');
    }

    // --- Admin creates any role ---

    public function test_admin_can_create_manager(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Manager',
            'email' => 'newmanager@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee_manager',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newmanager@example.com',
            'role' => 'employee_manager',
        ]);
    }

    public function test_admin_can_create_employee(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Employee',
            'email' => 'employee@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee_base',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'employee@example.com',
            'role' => 'employee_base',
        ]);
    }

    public function test_admin_can_create_requester(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Requester',
            'email' => 'requester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'requester',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'requester@example.com',
            'role' => 'requester',
        ]);
    }

    public function test_admin_can_create_admin(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Admin',
            'email' => 'admin2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee_admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin2@example.com',
            'role' => 'employee_admin',
        ]);
    }

    // --- Manager creates employee and requester ---

    public function test_manager_can_create_employee(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'New Employee',
            'email' => 'employee@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee_base',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'employee@example.com',
            'role' => 'employee_base',
        ]);
    }

    public function test_manager_can_create_requester(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'New Requester',
            'email' => 'requester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'requester',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'requester@example.com',
            'role' => 'requester',
        ]);
    }

    public function test_manager_cannot_create_manager(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Another Manager',
            'email' => 'manager2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee_manager',
        ]);

        $response->assertSessionHasErrors('role');
    }

    // --- Manager cannot view/edit admin or manager users ---

    public function test_manager_cannot_view_admin_user(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $this->actingAs($manager)->get(route('users.show', $admin))->assertForbidden();
    }

    public function test_manager_cannot_edit_admin_user(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $this->actingAs($manager)->get(route('users.edit', $admin))->assertForbidden();
    }

    // --- Editing ---

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $user = User::factory()->create(['role' => 'requester']);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('users.show', $user));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    // --- Suspend / Reactivate ---

    public function test_admin_can_suspend_user(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $user = User::factory()->create(['role' => 'requester', 'is_suspended' => false]);

        $response = $this->actingAs($admin)->patch(route('users.toggle-suspend', $user));
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_suspended);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $user = User::factory()->create(['role' => 'requester', 'is_suspended' => true]);

        $response = $this->actingAs($admin)->patch(route('users.toggle-suspend', $user));
        $response->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->is_suspended);
    }

    public function test_cannot_suspend_self(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $response = $this->actingAs($admin)->patch(route('users.toggle-suspend', $admin));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertFalse($admin->is_suspended);
    }

    // --- Suspended user cannot log in ---

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => 'requester',
            'is_suspended' => true,
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // --- Show view with forms ---

    public function test_employee_show_displays_assigned_forms(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $employee = User::factory()->create(['role' => 'employee_base']);

        $template = \App\Models\FormTemplate::create([
            'name' => 'Test Template',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $form = \App\Models\Form::create([
            'form_template_id' => $template->id,
            'user_id' => $admin->id,
            'title' => 'Assigned Form',
            'status' => 'submitted',
        ]);

        $form->assignedEmployees()->attach($employee->id);

        $response = $this->actingAs($admin)->get(route('users.show', $employee));
        $response->assertOk();
        $response->assertSeeText('Assigned Forms');
        $response->assertSeeText('Assigned Form');
    }

    public function test_requester_show_displays_created_forms(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $requester = User::factory()->create(['role' => 'requester']);

        $template = \App\Models\FormTemplate::create([
            'name' => 'Test Template',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        \App\Models\Form::create([
            'form_template_id' => $template->id,
            'user_id' => $requester->id,
            'title' => 'My Created Form',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('users.show', $requester));
        $response->assertOk();
        $response->assertSeeText('Created Forms');
        $response->assertSeeText('My Created Form');
    }

    // --- Role editing ---

    public function test_admin_can_change_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $user = User::factory()->create(['role' => 'requester']);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'employee_base',
        ]);

        $response->assertRedirect(route('users.show', $user));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'employee_base']);
    }

    public function test_admin_can_change_user_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
        $user = User::factory()->create(['role' => 'employee_base']);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'employee_admin',
        ]);

        $response->assertRedirect(route('users.show', $user));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'employee_admin']);
    }

    public function test_manager_can_change_employee_to_requester(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $user = User::factory()->create(['role' => 'employee_base']);

        $response = $this->actingAs($manager)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'requester',
        ]);

        $response->assertRedirect(route('users.show', $user));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'requester']);
    }

    public function test_manager_cannot_change_user_to_manager(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $user = User::factory()->create(['role' => 'employee_base']);

        $response = $this->actingAs($manager)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'employee_manager',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_manager_cannot_change_user_to_admin(): void
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $user = User::factory()->create(['role' => 'requester']);

        $response = $this->actingAs($manager)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'employee_admin',
        ]);

        $response->assertSessionHasErrors('role');
    }
}
