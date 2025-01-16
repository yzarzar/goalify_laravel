<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
            'status' => ['nullable', 'in:pending,in_progress,completed'],
            'priority' => ['required', 'in:low,medium,high'],
            'completion_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'due_date.after' => 'The due date must be a future date.',
            'completion_percentage.min' => 'The completion percentage must be at least 0.',
            'completion_percentage.max' => 'The completion percentage cannot be greater than 100.',
        ];
    }
}
