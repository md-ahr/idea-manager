<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\IdeaStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIdeaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $links = $this->input('links', []);
        if (is_array($links)) {
            $links = array_values(array_filter(
                $links,
                fn (mixed $link): bool => is_string($link) && $link !== ''
            ));
            $this->merge(['links' => $links]);
        }

        $steps = $this->input('steps', []);
        if (is_array($steps)) {
            $steps = array_values(array_filter(
                $steps,
                fn (mixed $step): bool => is_string($step) && $step !== ''
            ));
            $this->merge(['steps' => $steps]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(IdeaStatus::class)],
            'links' => ['nullable', 'array'],
            'links.*' => ['url', 'max:255'],
            'steps' => ['nullable', 'array'],
            'steps.*' => ['string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
