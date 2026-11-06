<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (QuestionBank $bank) {
            if (empty($bank->uuid)) {
                $bank->uuid = (string) Str::uuid();
            }
        });
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question_bank')
            ->withPivot(['quota_easy', 'quota_medium', 'quota_hard', 'sort_order'])
            ->withTimestamps();
    }
}
