<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title must not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either pending, in progress or completed.',
            'priority.required' => 'The priority is required.',
            'priority.in' => 'The priority must be either low, medium or high.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.nullable' => 'The due date may be null.',
        ];
    }
}
