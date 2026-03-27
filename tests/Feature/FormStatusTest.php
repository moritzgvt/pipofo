<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup(): array
    {
        $manager = User::factory()->create(['role' => 'employee_manager']);
        $employee = User::factory()->create(['role' => 'employee_base']);
        $requester = User::factory()->create(['role' => 'requester']);

        $template = FormTemplate::create([
            'name' => 'Test Template',
            'created_by' => $manager->id,
            'is_active' => true,
        ]);

        $fieldTemplate = InputFieldTemplate::create([
            'form_template_id' => $template->id,
            'label' => 'Test Field',
            'type' => 'text',
            'required' => false,
            'order' => 0,
        ]);

        $form = Form::create([
            'form_template_id' => $template->id,
            'user_id' => $requester->id,
            'title' => 'Test Form',
            'status' => 'draft',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $fieldTemplate->id,
            'value' => null,
        ]);

        $form->assignedEmployees()->attach($employee->id);

        return compact('manager', 'employee', 'requester', 'template', 'form', 'fieldTemplate');
    }

    public function test_requester_can_view_dashboard(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])->get('/dashboard')->assertOk();
    }

    public function test_employee_can_view_dashboard(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])->get('/dashboard')->assertOk();
    }

    public function test_requester_can_view_available_forms(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])->get('/requester/available-forms')->assertOk();
    }

    public function test_requester_can_create_form(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['requester'])
            ->post('/requester/forms/create/' . $setup['template']->id, [
                'title' => 'New Form',
            ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('forms', ['title' => 'New Form', 'status' => 'draft']);
    }

    public function test_requester_can_edit_draft_form(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertOk();
    }

    public function test_requester_can_submit_draft_form(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])
            ->post('/forms/' . $setup['form']->id . '/submit')
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_requester_cannot_edit_submitted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['requester'])
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertForbidden();
    }

    public function test_employee_can_accept_submitted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['employee'])
            ->post('/employee/forms/' . $setup['form']->id . '/accept')
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('accepted', $setup['form']->status);
    }

    public function test_employee_can_decline_submitted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['employee'])
            ->post('/employee/forms/' . $setup['form']->id . '/decline')
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('declined', $setup['form']->status);
    }

    public function test_employee_can_request_corrections(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['employee'])
            ->post('/employee/forms/' . $setup['form']->id . '/request-corrections', [
                'comment' => 'Please fix your name field.',
            ])
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('corrections', $setup['form']->status);
        $this->assertDatabaseHas('comments', ['body' => 'Please fix your name field.']);
    }

    public function test_requester_can_edit_corrections_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'corrections']);

        $this->actingAs($setup['requester'])
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertOk();
    }

    public function test_requester_can_resubmit_corrections_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'corrections']);

        $this->actingAs($setup['requester'])
            ->post('/forms/' . $setup['form']->id . '/submit')
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_requester_cannot_accept_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['requester'])
            ->post('/employee/forms/' . $setup['form']->id . '/accept')
            ->assertForbidden();
    }

    public function test_nobody_can_edit_accepted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'accepted']);

        $this->actingAs($setup['requester'])
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertForbidden();

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertForbidden();
    }

    public function test_manager_can_create_template(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['manager'])
            ->get('/form-templates/create')
            ->assertOk();
    }

    public function test_requester_cannot_access_templates(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])
            ->get('/form-templates')
            ->assertForbidden();
    }

    public function test_manager_can_assign_employee_to_form(): void
    {
        $setup = $this->createSetup();
        $newEmployee = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($setup['manager'])
            ->post('/forms/' . $setup['form']->id . '/assign-employee', [
                'employee_id' => $newEmployee->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('form_employees', [
            'form_id' => $setup['form']->id,
            'employee_id' => $newEmployee->id,
        ]);
    }

    public function test_revision_is_created_on_update(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])
            ->put('/forms/' . $setup['form']->id, [
                'fields' => [$setup['fieldTemplate']->id => 'New Value'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('revisions', ['form_id' => $setup['form']->id]);
        $this->assertDatabaseHas('form_fields', ['value' => 'New Value']);
    }

    public function test_employee_can_view_submitted_forms_list(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/employee/submitted')
            ->assertOk();
    }

    public function test_employee_can_view_corrections_list(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/employee/corrections')
            ->assertOk();
    }

    public function test_employee_can_view_completed_forms_list(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/employee/completed')
            ->assertOk();
    }

    public function test_unassigned_base_employee_cannot_view_form(): void
    {
        $setup = $this->createSetup();
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->get('/forms/' . $setup['form']->id)
            ->assertForbidden();
    }

    public function test_unassigned_base_employee_cannot_edit_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertForbidden();
    }

    public function test_unassigned_base_employee_cannot_update_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->put('/forms/' . $setup['form']->id, [
                'fields' => [$setup['fieldTemplate']->id => 'Hacked Value'],
            ])
            ->assertForbidden();
    }

    public function test_unassigned_base_employee_cannot_accept_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->post('/employee/forms/' . $setup['form']->id . '/accept')
            ->assertForbidden();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_unassigned_base_employee_cannot_decline_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->post('/employee/forms/' . $setup['form']->id . '/decline')
            ->assertForbidden();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_unassigned_base_employee_cannot_request_corrections(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->post('/employee/forms/' . $setup['form']->id . '/request-corrections', [
                'comment' => 'Should not work',
            ])
            ->assertForbidden();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_unassigned_base_employee_cannot_comment_on_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->post('/forms/' . $setup['form']->id . '/comments', [
                'body' => 'Should not work',
            ])
            ->assertForbidden();
    }

    public function test_unassigned_base_employee_cannot_view_revisions(): void
    {
        $setup = $this->createSetup();
        $unassigned = User::factory()->create(['role' => 'employee_base']);

        $this->actingAs($unassigned)
            ->get('/forms/' . $setup['form']->id . '/revisions')
            ->assertForbidden();
    }

    public function test_assigned_base_employee_can_view_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $setup['form']->id)
            ->assertOk();
    }

    public function test_assigned_base_employee_can_edit_submitted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $setup['form']->id . '/edit')
            ->assertOk();
    }

    public function test_employee_submitted_list_only_shows_assigned_forms(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        // Create another submitted form NOT assigned to the employee
        $otherForm = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['requester']->id,
            'title' => 'Unassigned Form',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($setup['employee'])
            ->get('/employee/submitted');

        $response->assertOk();
        $response->assertSeeText($setup['form']->title);
        $response->assertDontSeeText('Unassigned Form');
    }

    public function test_assigned_base_employee_cannot_view_draft_form(): void
    {
        $setup = $this->createSetup();
        $this->assertEquals('draft', $setup['form']->status);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $setup['form']->id)
            ->assertForbidden();
    }

    public function test_assigned_base_employee_cannot_comment_on_draft_form(): void
    {
        $setup = $this->createSetup();
        $this->assertEquals('draft', $setup['form']->status);

        $this->actingAs($setup['employee'])
            ->post('/forms/' . $setup['form']->id . '/comments', [
                'body' => 'Should not work',
            ])
            ->assertForbidden();
    }

    public function test_assigned_base_employee_cannot_view_revisions_of_draft_form(): void
    {
        $setup = $this->createSetup();
        $this->assertEquals('draft', $setup['form']->status);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $setup['form']->id . '/revisions')
            ->assertForbidden();
    }

    public function test_employee_dashboard_excludes_draft_forms(): void
    {
        $setup = $this->createSetup();
        $this->assertEquals('draft', $setup['form']->status);

        $response = $this->actingAs($setup['employee'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertDontSeeText($setup['form']->title);
    }

    public function test_requester_cannot_store_form_from_inactive_template(): void
    {
        $setup = $this->createSetup();
        $setup['template']->update(['is_active' => false]);

        $this->actingAs($setup['requester'])
            ->post('/requester/forms/create/' . $setup['template']->id, [
                'title' => 'Should Not Work',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('forms', ['title' => 'Should Not Work']);
    }

    public function test_requester_cannot_submit_form_with_empty_required_fields(): void
    {
        $setup = $this->createSetup();
        // Make field required
        $setup['fieldTemplate']->update(['required' => true]);

        // Value is null (not filled in)
        $this->actingAs($setup['requester'])
            ->post('/forms/' . $setup['form']->id . '/submit')
            ->assertRedirect()
            ->assertSessionHasErrors('fields')
            ->assertSessionHas('missing_fields', [$setup['fieldTemplate']->id]);

        $setup['form']->refresh();
        $this->assertEquals('draft', $setup['form']->status);
    }

    public function test_requester_can_submit_form_with_filled_required_fields(): void
    {
        $setup = $this->createSetup();
        $setup['fieldTemplate']->update(['required' => true]);

        // Fill in the required field
        FormField::where('form_id', $setup['form']->id)
            ->where('input_field_template_id', $setup['fieldTemplate']->id)
            ->update(['value' => 'Filled Value']);

        $this->actingAs($setup['requester'])
            ->post('/forms/' . $setup['form']->id . '/submit')
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_requester_can_save_form_with_empty_required_fields(): void
    {
        $setup = $this->createSetup();
        $setup['fieldTemplate']->update(['required' => true]);

        // Save (update) with empty required field should succeed
        $this->actingAs($setup['requester'])
            ->put('/forms/' . $setup['form']->id, [
                'fields' => [$setup['fieldTemplate']->id => ''],
            ])
            ->assertRedirect();

        $setup['form']->refresh();
        $this->assertEquals('draft', $setup['form']->status);
    }

    public function test_manager_can_delete_form(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['manager'])
            ->delete('/forms/' . $setup['form']->id)
            ->assertRedirect(route('employee.forms'));

        $setup['form']->refresh();
        $this->assertEquals('deleted', $setup['form']->status);
    }

    public function test_admin_can_delete_form(): void
    {
        $setup = $this->createSetup();
        $admin = User::factory()->create(['role' => 'employee_admin']);

        $this->actingAs($admin)
            ->delete('/forms/' . $setup['form']->id)
            ->assertRedirect(route('employee.forms'));

        $setup['form']->refresh();
        $this->assertEquals('deleted', $setup['form']->status);
    }

    public function test_requester_can_delete_draft_form(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['requester'])
            ->delete('/forms/' . $setup['form']->id)
            ->assertRedirect(route('requester.my-forms'));

        $setup['form']->refresh();
        $this->assertEquals('deleted', $setup['form']->status);
    }

    public function test_requester_cannot_delete_submitted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['requester'])
            ->delete('/forms/' . $setup['form']->id)
            ->assertForbidden();

        $setup['form']->refresh();
        $this->assertEquals('submitted', $setup['form']->status);
    }

    public function test_employee_cannot_delete_form(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['employee'])
            ->delete('/forms/' . $setup['form']->id)
            ->assertForbidden();

        $this->assertDatabaseHas('forms', ['id' => $setup['form']->id]);
    }

    public function test_deleted_form_not_shown_in_requester_my_forms(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $response = $this->actingAs($setup['requester'])
            ->get('/requester/my-forms');

        $response->assertOk();
        $response->assertDontSeeText($setup['form']->title);
    }

    public function test_deleted_form_not_shown_in_employee_forms(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $response = $this->actingAs($setup['manager'])
            ->get('/employee/forms');

        $response->assertOk();
        $response->assertDontSeeText($setup['form']->title);
    }

    public function test_manager_can_view_deleted_forms_page(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $response = $this->actingAs($setup['manager'])
            ->get('/employee/deleted');

        $response->assertOk();
        $response->assertSeeText($setup['form']->title);
    }

    public function test_employee_base_cannot_view_deleted_forms_page(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['employee'])
            ->get('/employee/deleted')
            ->assertForbidden();
    }

    public function test_requester_cannot_view_deleted_forms_page(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['requester'])
            ->get('/employee/deleted')
            ->assertForbidden();
    }

    public function test_requester_cannot_view_deleted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $this->actingAs($setup['requester'])
            ->get('/forms/' . $setup['form']->id)
            ->assertForbidden();
    }

    public function test_manager_can_view_deleted_form(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $this->actingAs($setup['manager'])
            ->get('/forms/' . $setup['form']->id)
            ->assertOk();
    }

    public function test_deleted_form_not_shown_in_requester_dashboard(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $response = $this->actingAs($setup['requester'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertDontSeeText($setup['form']->title);
    }

    public function test_deleted_form_not_shown_in_employee_dashboard(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'deleted']);

        $response = $this->actingAs($setup['employee'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertDontSeeText($setup['form']->title);
    }
}
