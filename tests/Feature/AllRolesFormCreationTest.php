<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllRolesFormCreationTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup(): array
    {
        $admin = User::factory()->create(['role' => 'employee_admin']);
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

        return compact('admin', 'manager', 'employee', 'requester', 'template', 'fieldTemplate');
    }

    public function test_employee_can_view_available_forms(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/requester/available-forms')
            ->assertOk();
    }

    public function test_manager_can_view_available_forms(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['manager'])
            ->get('/requester/available-forms')
            ->assertOk();
    }

    public function test_admin_can_view_available_forms(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['admin'])
            ->get('/requester/available-forms')
            ->assertOk();
    }

    public function test_employee_can_create_form(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->post('/requester/forms/create/' . $setup['template']->id, [
                'title' => 'Employee Form',
            ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('forms', [
            'title' => 'Employee Form',
            'user_id' => $setup['employee']->id,
            'status' => 'draft',
        ]);
    }

    public function test_manager_can_create_form(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['manager'])
            ->post('/requester/forms/create/' . $setup['template']->id, [
                'title' => 'Manager Form',
            ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('forms', [
            'title' => 'Manager Form',
            'user_id' => $setup['manager']->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_create_form(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['admin'])
            ->post('/requester/forms/create/' . $setup['template']->id, [
                'title' => 'Admin Form',
            ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('forms', [
            'title' => 'Admin Form',
            'user_id' => $setup['admin']->id,
            'status' => 'draft',
        ]);
    }

    public function test_employee_can_edit_own_draft_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['employee']->id,
            'title' => 'Employee Draft',
            'status' => 'draft',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $form->id . '/edit')
            ->assertOk();
    }

    public function test_manager_can_edit_own_draft_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['manager']->id,
            'title' => 'Manager Draft',
            'status' => 'draft',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['manager'])
            ->get('/forms/' . $form->id . '/edit')
            ->assertOk();
    }

    public function test_employee_can_submit_own_draft_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['employee']->id,
            'title' => 'Employee Draft',
            'status' => 'draft',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['employee'])
            ->post('/forms/' . $form->id . '/submit')
            ->assertRedirect();

        $form->refresh();
        $this->assertEquals('submitted', $form->status);
    }

    public function test_manager_can_submit_own_draft_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['manager']->id,
            'title' => 'Manager Draft',
            'status' => 'draft',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['manager'])
            ->post('/forms/' . $form->id . '/submit')
            ->assertRedirect();

        $form->refresh();
        $this->assertEquals('submitted', $form->status);
    }

    public function test_admin_can_submit_own_draft_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['admin']->id,
            'title' => 'Admin Draft',
            'status' => 'draft',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['admin'])
            ->post('/forms/' . $form->id . '/submit')
            ->assertRedirect();

        $form->refresh();
        $this->assertEquals('submitted', $form->status);
    }

    public function test_employee_can_view_own_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['employee']->id,
            'title' => 'Employee Form',
            'status' => 'draft',
        ]);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $form->id)
            ->assertOk();
    }

    public function test_employee_can_view_my_forms_page(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/requester/my-forms')
            ->assertOk();
    }

    public function test_employee_can_view_pending_corrections_page(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/requester/pending-corrections')
            ->assertOk();
    }

    public function test_employee_can_edit_own_corrections_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['employee']->id,
            'title' => 'Employee Corrections',
            'status' => 'corrections',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $form->id . '/edit')
            ->assertOk();
    }

    public function test_employee_can_resubmit_own_corrections_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['employee']->id,
            'title' => 'Employee Corrections',
            'status' => 'corrections',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['employee'])
            ->post('/forms/' . $form->id . '/submit')
            ->assertRedirect();

        $form->refresh();
        $this->assertEquals('submitted', $form->status);
    }

    public function test_employee_cannot_edit_others_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['requester']->id,
            'title' => 'Requester Draft',
            'status' => 'draft',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'value' => null,
        ]);

        $this->actingAs($setup['employee'])
            ->get('/forms/' . $form->id . '/edit')
            ->assertForbidden();
    }

    public function test_employee_cannot_submit_others_form(): void
    {
        $setup = $this->createSetup();
        $form = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['requester']->id,
            'title' => 'Requester Draft',
            'status' => 'draft',
        ]);

        $this->actingAs($setup['employee'])
            ->post('/forms/' . $form->id . '/submit')
            ->assertForbidden();
    }
}
