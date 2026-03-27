<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeFormsFilterTest extends TestCase
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

        $template2 = FormTemplate::create([
            'name' => 'Other Template',
            'created_by' => $manager->id,
            'is_active' => true,
        ]);

        InputFieldTemplate::create([
            'form_template_id' => $template->id,
            'label' => 'Test Field',
            'type' => 'text',
            'required' => false,
            'order' => 0,
        ]);

        $submittedForm = Form::create([
            'form_template_id' => $template->id,
            'user_id' => $requester->id,
            'title' => 'Submitted Form',
            'status' => 'submitted',
            'submitted_at' => '2026-01-15 10:00:00',
        ]);
        $submittedForm->assignedEmployees()->attach($employee->id);

        $correctionsForm = Form::create([
            'form_template_id' => $template2->id,
            'user_id' => $requester->id,
            'title' => 'Corrections Form',
            'status' => 'corrections',
            'submitted_at' => '2026-02-10 10:00:00',
        ]);
        $correctionsForm->assignedEmployees()->attach($employee->id);

        $acceptedForm = Form::create([
            'form_template_id' => $template->id,
            'user_id' => $requester->id,
            'title' => 'Accepted Form',
            'status' => 'accepted',
            'submitted_at' => '2026-03-01 10:00:00',
            'completed_at' => '2026-03-05 10:00:00',
        ]);
        $acceptedForm->assignedEmployees()->attach($employee->id);

        $draftForm = Form::create([
            'form_template_id' => $template->id,
            'user_id' => $requester->id,
            'title' => 'Draft Form',
            'status' => 'draft',
        ]);
        $draftForm->assignedEmployees()->attach($employee->id);

        return compact('manager', 'employee', 'requester', 'template', 'template2', 'submittedForm', 'correctionsForm', 'acceptedForm', 'draftForm');
    }

    public function test_employee_can_access_unified_forms_page(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['employee'])
            ->get('/employee/forms')
            ->assertOk();
    }

    public function test_manager_can_access_unified_forms_page(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['manager'])
            ->get('/employee/forms')
            ->assertOk();
    }

    public function test_requester_cannot_access_unified_forms_page(): void
    {
        $setup = $this->createSetup();
        $this->actingAs($setup['requester'])
            ->get('/employee/forms')
            ->assertForbidden();
    }

    public function test_unified_page_shows_all_non_draft_forms(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms');

        $response->assertOk();
        $response->assertSeeText('Submitted Form');
        $response->assertSeeText('Corrections Form');
        $response->assertSeeText('Accepted Form');
        $response->assertDontSeeText('Draft Form');
    }

    public function test_filter_by_status(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?status=submitted');

        $response->assertOk();
        $response->assertSeeText('Submitted Form');
        $response->assertDontSeeText('Corrections Form');
        $response->assertDontSeeText('Accepted Form');
    }

    public function test_filter_by_template(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?template=' . $setup['template2']->id);

        $response->assertOk();
        $response->assertSeeText('Corrections Form');
        $response->assertDontSeeText('Submitted Form');
        $response->assertDontSeeText('Accepted Form');
    }

    public function test_filter_by_creator(): void
    {
        $setup = $this->createSetup();

        $otherRequester = User::factory()->create(['role' => 'requester']);
        $otherForm = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $otherRequester->id,
            'title' => 'Other Requester Form',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $otherForm->assignedEmployees()->attach($setup['employee']->id);

        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?creator=' . $otherRequester->id);

        $response->assertOk();
        $response->assertSeeText('Other Requester Form');
        $response->assertDontSeeText('Submitted Form');
    }

    public function test_filter_by_assigned_employee(): void
    {
        $setup = $this->createSetup();

        $response = $this->actingAs($setup['manager'])
            ->get('/employee/forms?assigned=' . $setup['employee']->id);

        $response->assertOk();
        $response->assertSeeText('Submitted Form');
        $response->assertSeeText('Corrections Form');
        $response->assertSeeText('Accepted Form');
    }

    public function test_filter_by_submitted_date_range(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?submitted_from=2026-02-01&submitted_to=2026-02-28');

        $response->assertOk();
        $response->assertSeeText('Corrections Form');
        $response->assertDontSeeText('Submitted Form');
        $response->assertDontSeeText('Accepted Form');
    }

    public function test_sort_by_submitted_at_ascending(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?sort=submitted_at&direction=asc');

        $response->assertOk();
        $content = $response->getContent();
        $submittedPos = strpos($content, 'Submitted Form');
        $correctionsPos = strpos($content, 'Corrections Form');
        $acceptedPos = strpos($content, 'Accepted Form');

        $this->assertLessThan($correctionsPos, $submittedPos);
        $this->assertLessThan($acceptedPos, $correctionsPos);
    }

    public function test_sort_by_submitted_at_descending(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?sort=submitted_at&direction=desc');

        $response->assertOk();
        $content = $response->getContent();
        $submittedPos = strpos($content, 'Submitted Form');
        $correctionsPos = strpos($content, 'Corrections Form');
        $acceptedPos = strpos($content, 'Accepted Form');

        $this->assertLessThan($correctionsPos, $acceptedPos);
        $this->assertLessThan($submittedPos, $correctionsPos);
    }

    public function test_base_employee_only_sees_assigned_forms(): void
    {
        $setup = $this->createSetup();

        $unassignedForm = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['requester']->id,
            'title' => 'Unassigned Form',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms');

        $response->assertOk();
        $response->assertDontSeeText('Unassigned Form');
    }

    public function test_manager_sees_all_forms(): void
    {
        $setup = $this->createSetup();

        $unassignedForm = Form::create([
            'form_template_id' => $setup['template']->id,
            'user_id' => $setup['requester']->id,
            'title' => 'Unassigned Form',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($setup['manager'])
            ->get('/employee/forms');

        $response->assertOk();
        $response->assertSeeText('Unassigned Form');
    }

    public function test_invalid_status_filter_is_ignored(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?status=invalid');

        $response->assertOk();
        $response->assertSeeText('Submitted Form');
        $response->assertSeeText('Corrections Form');
        $response->assertSeeText('Accepted Form');
    }

    public function test_invalid_sort_field_uses_default(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?sort=invalid_field');

        $response->assertOk();
    }

    public function test_filter_by_updated_date_range(): void
    {
        $setup = $this->createSetup();

        // Use query builder to set specific updated_at values
        Form::where('id', $setup['submittedForm']->id)->update(['updated_at' => '2026-01-20 10:00:00']);
        Form::where('id', $setup['correctionsForm']->id)->update(['updated_at' => '2026-03-15 10:00:00']);
        Form::where('id', $setup['acceptedForm']->id)->update(['updated_at' => '2026-03-15 10:00:00']);

        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?updated_from=2026-03-01&updated_to=2026-03-31');

        $response->assertOk();
        $response->assertDontSeeText('Submitted Form');
        $response->assertSeeText('Corrections Form');
        $response->assertSeeText('Accepted Form');
    }

    public function test_combined_filters(): void
    {
        $setup = $this->createSetup();
        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?status=submitted&template=' . $setup['template']->id);

        $response->assertOk();
        $response->assertSeeText('Submitted Form');
        $response->assertDontSeeText('Corrections Form');
        $response->assertDontSeeText('Accepted Form');
    }

    public function test_pagination_preserves_query_string(): void
    {
        $setup = $this->createSetup();

        // Create enough forms to trigger pagination
        for ($i = 0; $i < 20; $i++) {
            $form = Form::create([
                'form_template_id' => $setup['template']->id,
                'user_id' => $setup['requester']->id,
                'title' => "Paginated Form $i",
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
            $form->assignedEmployees()->attach($setup['employee']->id);
        }

        $response = $this->actingAs($setup['employee'])
            ->get('/employee/forms?status=submitted&page=2');

        $response->assertOk();
    }
}
