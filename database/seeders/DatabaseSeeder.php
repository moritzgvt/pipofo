<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users for each role
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pipofo.test',
            'password' => bcrypt('password'),
            'role' => 'employee_admin',
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@pipofo.test',
            'password' => bcrypt('password'),
            'role' => 'employee_manager',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@pipofo.test',
            'password' => bcrypt('password'),
            'role' => 'employee_base',
        ]);

        $requester = User::create([
            'name' => 'Requester User',
            'email' => 'requester@pipofo.test',
            'password' => bcrypt('password'),
            'role' => 'requester',
        ]);

        // Create a form template
        $template = FormTemplate::create([
            'name' => 'Contact Information',
            'description' => 'Basic contact information form for new employees.',
            'created_by' => $manager->id,
            'is_active' => true,
        ]);

        $fields = [
            ['label' => 'Full Name', 'type' => 'text', 'required' => true, 'order' => 0, 'placeholder' => 'John Doe'],
            ['label' => 'Email Address', 'type' => 'text', 'required' => true, 'order' => 1, 'placeholder' => 'john@example.com'],
            ['label' => 'Phone Number', 'type' => 'text', 'required' => false, 'order' => 2, 'placeholder' => '+49 123 456789'],
            ['label' => 'Date of Birth', 'type' => 'date', 'required' => true, 'order' => 3],
            ['label' => 'Department', 'type' => 'select', 'required' => true, 'order' => 4, 'options' => ['Engineering', 'Marketing', 'Sales', 'HR', 'Finance']],
            ['label' => 'Bio', 'type' => 'textarea', 'required' => false, 'order' => 5, 'placeholder' => 'Tell us about yourself...'],
            ['label' => 'Agree to Terms', 'type' => 'checkbox', 'required' => true, 'order' => 6],
        ];

        foreach ($fields as $fieldData) {
            InputFieldTemplate::create(array_merge($fieldData, ['form_template_id' => $template->id]));
        }

        // Create a second template
        $template2 = FormTemplate::create([
            'name' => 'Travel Request',
            'description' => 'Submit a travel request for business trips.',
            'created_by' => $manager->id,
            'is_active' => true,
        ]);

        $travelFields = [
            ['label' => 'Destination', 'type' => 'text', 'required' => true, 'order' => 0],
            ['label' => 'Purpose', 'type' => 'textarea', 'required' => true, 'order' => 1],
            ['label' => 'Start Date', 'type' => 'date', 'required' => true, 'order' => 2],
            ['label' => 'End Date', 'type' => 'date', 'required' => true, 'order' => 3],
            ['label' => 'Estimated Budget', 'type' => 'number', 'required' => true, 'order' => 4],
            ['label' => 'Transport', 'type' => 'radio', 'required' => true, 'order' => 5, 'options' => ['Flight', 'Train', 'Car', 'Other']],
        ];

        foreach ($travelFields as $fieldData) {
            InputFieldTemplate::create(array_merge($fieldData, ['form_template_id' => $template2->id]));
        }

        // Create a sample form in draft status
        $form = Form::create([
            'form_template_id' => $template->id,
            'user_id' => $requester->id,
            'title' => 'Contact Information - Mar 25, 2026',
            'status' => 'draft',
        ]);

        foreach ($template->inputFieldTemplates as $fieldTemplate) {
            FormField::create([
                'form_id' => $form->id,
                'input_field_template_id' => $fieldTemplate->id,
                'value' => null,
            ]);
        }

        // Assign the base employee to the form
        $form->assignedEmployees()->attach($employee->id);
    }
}
