<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\IdeaStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdeaRequest extends FormRequest
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
                function (mixed $step): bool {
                    if (! is_array($step)) {
                        return false;
                    }
                    $description = $step['description'] ?? '';

                    return is_string($description) && trim($description) !== '';
                }
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
            'steps.*.description' => ['string', 'max:255'],
            'steps.*.completed' => ['boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
