<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RequesterApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeTemplate(?User $creator = null, bool $active = true): array
    {
        $creator = $creator ?? User::factory()->create(['role' => 'employee_manager']);
        $template = FormTemplate::create([
            'name' => 'Vacation Request',
            'description' => 'Request days off',
            'created_by' => $creator->id,
            'is_active' => $active,
        ]);
        $field = InputFieldTemplate::create([
            'form_template_id' => $template->id,
            'label' => 'Reason',
            'type' => 'text',
            'required' => true,
            'order' => 0,
        ]);
        $optional = InputFieldTemplate::create([
            'form_template_id' => $template->id,
            'label' => 'Notes',
            'type' => 'text',
            'required' => false,
            'order' => 1,
        ]);

        return compact('template', 'field', 'optional');
    }

    public function test_login_issues_a_sanctum_token(): void
    {
        $user = User::factory()->create([
            'role' => 'requester',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('user.email', $user->email);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct')]);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertStatus(422);
    }

    public function test_login_rejects_suspended_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'is_suspended' => true,
        ]);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertStatus(422);
    }

    public function test_protected_endpoints_require_authentication(): void
    {
        $this->getJson('/api/forms')->assertStatus(401);
        $this->getJson('/api/form-templates')->assertStatus(401);
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'requester']);
        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', 'requester');
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('phpunit');

        $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_can_list_active_form_templates(): void
    {
        $setup = $this->makeTemplate();
        $this->makeTemplate(active: false); // inactive - should not appear

        $user = User::factory()->create(['role' => 'requester']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/form-templates');
        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($setup['template']->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_can_show_form_template_with_fields(): void
    {
        $setup = $this->makeTemplate();
        Sanctum::actingAs(User::factory()->create(['role' => 'requester']));

        $this->getJson('/api/form-templates/' . $setup['template']->id)
            ->assertOk()
            ->assertJsonPath('data.id', $setup['template']->id)
            ->assertJsonPath('data.input_field_templates.0.label', 'Reason')
            ->assertJsonPath('data.input_field_templates.0.required', true);
    }

    public function test_inactive_template_is_not_shown(): void
    {
        $setup = $this->makeTemplate(active: false);
        Sanctum::actingAs(User::factory()->create(['role' => 'requester']));

        $this->getJson('/api/form-templates/' . $setup['template']->id)->assertNotFound();
    }

    public function test_can_create_form_from_template(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/form-templates/' . $setup['template']->id . '/forms', [
            'title' => 'My Vacation',
            'fields' => [
                $setup['field']->id => 'Family trip',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'My Vacation')
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('forms', [
            'title' => 'My Vacation',
            'user_id' => $user->id,
            'status' => 'draft',
        ]);
        $form = Form::where('title', 'My Vacation')->first();
        $this->assertDatabaseHas('form_fields', [
            'form_id' => $form->id,
            'input_field_template_id' => $setup['field']->id,
            'value' => 'Family trip',
        ]);
    }

    public function test_cannot_create_form_from_inactive_template(): void
    {
        $setup = $this->makeTemplate(active: false);
        Sanctum::actingAs(User::factory()->create(['role' => 'requester']));

        $this->postJson('/api/form-templates/' . $setup['template']->id . '/forms', ['title' => 'X'])
            ->assertForbidden();
    }

    public function test_create_form_validates_unknown_field_id(): void
    {
        $setup = $this->makeTemplate();
        Sanctum::actingAs(User::factory()->create(['role' => 'requester']));

        $this->postJson('/api/form-templates/' . $setup['template']->id . '/forms', [
            'title' => 'X',
            'fields' => [99999 => 'foo'],
        ])->assertStatus(422);
    }

    public function test_index_lists_only_own_forms(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $other = User::factory()->create(['role' => 'requester']);

        $mine = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'draft']);
        Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $other->id, 'title' => 'Theirs', 'status' => 'draft']);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/forms')->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEquals([$mine->id], $ids);
    }

    public function test_index_can_filter_by_status(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);

        Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Draft', 'status' => 'draft']);
        $submitted = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Submitted', 'status' => 'submitted']);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/forms?status=submitted')->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEquals([$submitted->id], $ids);
    }

    public function test_show_returns_own_form_with_fields(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'draft']);
        FormField::create(['form_id' => $form->id, 'input_field_template_id' => $setup['field']->id, 'value' => 'hello']);

        Sanctum::actingAs($user);
        $this->getJson('/api/forms/' . $form->id)
            ->assertOk()
            ->assertJsonPath('data.id', $form->id)
            ->assertJsonPath('data.fields.0.value', 'hello');
    }

    public function test_cannot_view_other_users_form(): void
    {
        $setup = $this->makeTemplate();
        $other = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $other->id, 'title' => 'Theirs', 'status' => 'draft']);

        Sanctum::actingAs(User::factory()->create(['role' => 'requester']));
        $this->getJson('/api/forms/' . $form->id)->assertForbidden();
    }

    public function test_can_update_draft_form_and_creates_revision(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'draft']);
        FormField::create(['form_id' => $form->id, 'input_field_template_id' => $setup['field']->id, 'value' => null]);

        Sanctum::actingAs($user);
        $response = $this->putJson('/api/forms/' . $form->id, [
            'fields' => [$setup['field']->id => 'Updated'],
            'revision_message' => 'first edit',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.fields.0.value', 'Updated');

        $this->assertDatabaseHas('form_fields', [
            'form_id' => $form->id,
            'input_field_template_id' => $setup['field']->id,
            'value' => 'Updated',
        ]);
        $this->assertDatabaseHas('revisions', ['form_id' => $form->id, 'message' => 'first edit']);
    }

    public function test_cannot_update_submitted_form(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'submitted']);
        FormField::create(['form_id' => $form->id, 'input_field_template_id' => $setup['field']->id, 'value' => 'x']);

        Sanctum::actingAs($user);
        $this->putJson('/api/forms/' . $form->id, ['fields' => [$setup['field']->id => 'y']])
            ->assertForbidden();
    }

    public function test_can_submit_form_with_required_fields_filled(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'draft']);
        FormField::create(['form_id' => $form->id, 'input_field_template_id' => $setup['field']->id, 'value' => 'reason']);
        FormField::create(['form_id' => $form->id, 'input_field_template_id' => $setup['optional']->id, 'value' => null]);

        Sanctum::actingAs($user);
        $this->postJson('/api/forms/' . $form->id . '/submit')
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $this->assertEquals('submitted', $form->fresh()->status);
    }

    public function test_submit_fails_with_missing_required_fields(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'draft']);
        FormField::create(['form_id' => $form->id, 'input_field_template_id' => $setup['field']->id, 'value' => null]);

        Sanctum::actingAs($user);
        $this->postJson('/api/forms/' . $form->id . '/submit')->assertStatus(422);
        $this->assertEquals('draft', $form->fresh()->status);
    }

    public function test_can_destroy_own_draft(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'draft']);

        Sanctum::actingAs($user);
        $this->deleteJson('/api/forms/' . $form->id)->assertOk();
        $this->assertEquals('deleted', $form->fresh()->status);
    }

    public function test_cannot_destroy_submitted_form(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'submitted']);

        Sanctum::actingAs($user);
        $this->deleteJson('/api/forms/' . $form->id)->assertForbidden();
    }

    public function test_can_add_comment_to_own_form(): void
    {
        $setup = $this->makeTemplate();
        $user = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $user->id, 'title' => 'Mine', 'status' => 'corrections']);

        Sanctum::actingAs($user);
        $this->postJson('/api/forms/' . $form->id . '/comments', ['body' => 'Done'])
            ->assertCreated()
            ->assertJsonPath('data.body', 'Done')
            ->assertJsonPath('data.is_employee_comment', false);

        $this->assertDatabaseHas('comments', [
            'form_id' => $form->id,
            'user_id' => $user->id,
            'body' => 'Done',
            'is_employee_comment' => false,
        ]);
    }

    public function test_cannot_comment_on_other_users_form(): void
    {
        $setup = $this->makeTemplate();
        $other = User::factory()->create(['role' => 'requester']);
        $form = Form::create(['form_template_id' => $setup['template']->id, 'user_id' => $other->id, 'title' => 'Theirs', 'status' => 'draft']);

        Sanctum::actingAs(User::factory()->create(['role' => 'requester']));
        $this->postJson('/api/forms/' . $form->id . '/comments', ['body' => 'Hi'])->assertForbidden();
    }
}
