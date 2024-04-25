<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $fillable = ['uuid', 'company_id', 'api_key', 'addr', 'uuid_whatsapp', 'status'];

    public function isPaired(): bool
    {
        return $this->status === 'PAIRED';
    }
}
