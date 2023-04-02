<?php

namespace LambdaDigamma\MMEvents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use LambdaDigamma\MMEvents\Database\Factories\TicketFactory;
use LambdaDigamma\MMEvents\Database\Factories\TicketOptionFactory;
use LaravelArchivable\Archivable;
use LaravelPublishable\Publishable;
use Spatie\Translatable\HasTranslations;


class TicketOption extends Model
{
    use HasFactory;

    protected $table = "mm_ticket_options";

    protected $fillable = [
        'name',
        'price',
        'ticket_id',
        'url',
        'extras'
    ];

    protected static function newFactory(): TicketOptionFactory
    {
        return TicketOptionFactory::new();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

}
