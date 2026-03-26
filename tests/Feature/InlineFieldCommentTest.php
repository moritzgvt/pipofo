<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\FormView;
use App\Models\InputFieldTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InlineFieldCommentTest extends TestCase
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
            'value' => 'test value',
        ]);

        $form->assignedEmployees()->attach($employee->id);

        return compact('manager', 'employee', 'requester', 'template', 'form', 'fieldTemplate');
    }

    public function test_requester_can_add_field_specific_comment(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['requester'])
            ->post(route('forms.comments.store', $setup['form']), [
                'body' => 'Requester field comment',
                'input_field_template_id' => $setup['fieldTemplate']->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'form_id' => $setup['form']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Requester field comment',
            'is_employee_comment' => false,
        ]);
    }

    public function test_employee_can_add_field_specific_comment(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        $this->actingAs($setup['employee'])
            ->post(route('forms.comments.store', $setup['form']), [
                'body' => 'Employee field comment',
                'input_field_template_id' => $setup['fieldTemplate']->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'form_id' => $setup['form']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Employee field comment',
            'is_employee_comment' => true,
        ]);
    }

    public function test_field_specific_comment_shown_on_show_page(): void
    {
        $setup = $this->createSetup();

        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Inline field comment text',
            'is_employee_comment' => false,
        ]);

        $this->actingAs($setup['requester'])
            ->get(route('forms.show', $setup['form']))
            ->assertOk()
            ->assertSee('Inline field comment text');
    }

    public function test_field_specific_comment_shown_on_edit_page(): void
    {
        $setup = $this->createSetup();

        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Edit page field comment',
            'is_employee_comment' => false,
        ]);

        $this->actingAs($setup['requester'])
            ->get(route('forms.edit', $setup['form']))
            ->assertOk()
            ->assertSee('Edit page field comment');
    }

    public function test_employee_comment_shown_on_edit_page(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'submitted']);

        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['employee']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Employee edit page comment',
            'is_employee_comment' => true,
        ]);

        $this->actingAs($setup['employee'])
            ->get(route('forms.edit', $setup['form']))
            ->assertOk()
            ->assertSee('Employee edit page comment');
    }

    public function test_general_comment_shown_in_general_section_on_show_page(): void
    {
        $setup = $this->createSetup();

        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
            'input_field_template_id' => null,
            'body' => 'General comment text',
            'is_employee_comment' => false,
        ]);

        $this->actingAs($setup['requester'])
            ->get(route('forms.show', $setup['form']))
            ->assertOk()
            ->assertSee('General comment text')
            ->assertSee('General Comments');
    }

    public function test_field_comment_via_json_request(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['requester'])
            ->postJson(route('forms.comments.store', $setup['form']), [
                'body' => 'JSON field comment',
                'input_field_template_id' => $setup['fieldTemplate']->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('comments', [
            'form_id' => $setup['form']->id,
            'body' => 'JSON field comment',
            'input_field_template_id' => $setup['fieldTemplate']->id,
        ]);
    }

    public function test_show_page_displays_add_comment_link_for_each_field(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['requester'])
            ->get(route('forms.show', $setup['form']))
            ->assertOk()
            ->assertSee('Add comment');
    }

    public function test_comments_from_all_roles_shown_on_edit_page(): void
    {
        $setup = $this->createSetup();
        $setup['form']->update(['status' => 'corrections']);

        // Requester comment
        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Requester inline comment',
            'is_employee_comment' => false,
        ]);

        // Employee comment
        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['employee']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Employee inline comment',
            'is_employee_comment' => true,
        ]);

        $this->actingAs($setup['requester'])
            ->get(route('forms.edit', $setup['form']))
            ->assertOk()
            ->assertSee('Requester inline comment')
            ->assertSee('Employee inline comment');
    }

    public function test_new_comment_highlighted_on_show_page(): void
    {
        $setup = $this->createSetup();

        // Simulate a previous visit
        FormView::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
            'last_viewed_at' => now()->subMinutes(10),
        ]);

        // Old comment (before last visit)
        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['employee']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Old comment before visit',
            'is_employee_comment' => true,
            'created_at' => now()->subMinutes(20),
        ]);

        // New comment (after last visit)
        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['employee']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'New comment after visit',
            'is_employee_comment' => true,
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($setup['requester'])
            ->get(route('forms.show', $setup['form']));

        $response->assertOk()
            ->assertSee('Old comment before visit')
            ->assertSee('New comment after visit')
            ->assertSee('ring-yellow-400'); // field highlight
    }

    public function test_new_comment_highlighted_on_edit_page(): void
    {
        $setup = $this->createSetup();

        // Simulate a previous visit
        FormView::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
            'last_viewed_at' => now()->subMinutes(10),
        ]);

        // New comment (after last visit)
        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['employee']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'New edit page comment',
            'is_employee_comment' => true,
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($setup['requester'])
            ->get(route('forms.edit', $setup['form']));

        $response->assertOk()
            ->assertSee('New edit page comment')
            ->assertSee('ring-yellow-400'); // field highlight
    }

    public function test_no_highlight_on_first_visit(): void
    {
        $setup = $this->createSetup();

        Comment::create([
            'form_id' => $setup['form']->id,
            'user_id' => $setup['employee']->id,
            'input_field_template_id' => $setup['fieldTemplate']->id,
            'body' => 'Comment on first visit',
            'is_employee_comment' => true,
        ]);

        // First visit — no FormView record exists, so no highlights
        $response = $this->actingAs($setup['requester'])
            ->get(route('forms.show', $setup['form']));

        $response->assertOk()
            ->assertSee('Comment on first visit')
            ->assertDontSee('ring-yellow-400');
    }

    public function test_form_view_timestamp_updated_on_visit(): void
    {
        $setup = $this->createSetup();

        $this->actingAs($setup['requester'])
            ->get(route('forms.show', $setup['form']));

        $this->assertDatabaseHas('form_views', [
            'form_id' => $setup['form']->id,
            'user_id' => $setup['requester']->id,
        ]);

        $view = FormView::where('form_id', $setup['form']->id)
            ->where('user_id', $setup['requester']->id)
            ->first();

        $this->assertNotNull($view->last_viewed_at);
    }
}
