<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientScreeningAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_intake_id',
        'question_key',
        'question_text',
        'answer_score',
        'answer_value',
        'answer_notes',
    ];

    protected $casts = [
        'answer_score' => 'integer',
    ];

    public function clientIntake(): BelongsTo
    {
        return $this->belongsTo(ClientIntake::class);
    }
}
