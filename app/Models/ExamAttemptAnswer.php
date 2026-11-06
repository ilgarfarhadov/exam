<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'exam_attempt_question_id',
        'selected_answer_ids',
    ];

    protected $casts = [
        'selected_answer_ids' => 'array',
    ];

    public function examAttempt()
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function examAttemptQuestion()
    {
        return $this->belongsTo(ExamAttemptQuestion::class);
    }
}
