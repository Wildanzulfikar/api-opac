<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'loan';
    protected $primaryKey = 'loan_id';
    public $timestamps = false;

    protected $fillable = [
        'item_code', 'member_id', 'loan_date', 'due_date', 'renewed',
        'loan_rules_id', 'actual', 'is_lent', 'is_return', 'return_date',
        'input_date', 'last_update', 'uid'
    ];

    protected $casts = [
        'is_lent' => 'boolean',
        'is_return' => 'boolean',
    ];

    // Relationship to biblio if item_code maps to biblio.biblio_id or call_number:
    // adjust as needed (here assume item_code stores biblio_id)
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }
}
