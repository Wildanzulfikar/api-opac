<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Biblio extends Model
{
    protected $table = 'biblio';
    protected $primaryKey = 'biblio_id';
    public $timestamps = false; // karena tabel pake input_date/last_update manual

    protected $fillable = [
        'gmd_id', 'title', 'sor', 'edition', 'isbn_issn', 'publisher_id',
        'publish_year', 'collation', 'series_title', 'call_number', 'language_id',
        'source', 'publish_place_id', 'classification', 'notes', 'image',
        'file_att', 'opac_hide', 'promoted', 'labels', 'frequency_id',
        'spec_detail_info', 'content_type_id', 'media_type_id', 'carrier_type_id',
        'input_date', 'last_update', 'uid'
    ];

    protected $casts = [
        'opac_hide' => 'boolean',
        'promoted' => 'boolean',
    ];
}
