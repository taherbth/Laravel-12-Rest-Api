<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Customer extends Model
{
    use Searchable, SoftDeletes;
    protected $table = 'customers';

    protected $fillable = ['gender_id', 'country_id', 'customer_no', 'first_name', 'last_name', 'cell_phone',
        'work_phone', 'email', 'date_of_birth', 'address', 'address2', 'city', 'zip'];
    protected $dates = ['deleted_at'];


    /**
     * Get the indexable data array for the model.
     * Keep this lean! Only include fields you search/filter by.
     */
    public function toSearchableArray(): array
    {
        return [
            'id'          => (int) $this->id,
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'email'       => $this->email,
            'cell_phone'  => $this->cell_phone,
            'status'      => (string) $this->status
        ];
    }
}



