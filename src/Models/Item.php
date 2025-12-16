<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
	protected $table = 'item';
	protected $primaryKey = 'item_id';
	public $timestamps = false;

	protected $fillable = [
		'item_code', 'call_number', 'location_id', 'item_status_id',
		'input_date', 'last_update', 'uid', 'biblio_id'
	];

	public function biblio()
	{
		return $this->belongsTo(Biblio::class, 'biblio_id', 'biblio_id');
	}
}
