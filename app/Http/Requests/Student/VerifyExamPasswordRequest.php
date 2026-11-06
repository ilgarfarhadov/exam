<?php



namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class VerifyExamPasswordRequest extends FormRequest
{
    /**
     * The admin panel is gated by AdminMiddleware and the student pages by
     * StudentMiddleware, so by the time a request class runs the caller is
     * already the right kind of user.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entry_password' => 'required|string',
        ];
    }
}
