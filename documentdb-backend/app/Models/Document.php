<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['title', 'description', 'slug', 'document_key', 'user_id'])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Document $document): void {
            if ($document->isDirty('title')) {
                $document->slug = Str::slug($document->title);
            }
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->document_key) {
                return null;
            }

            $disk = Storage::disk('r2');

            if (filled(config('filesystems.disks.r2.url'))) {
                return $disk->url($this->document_key);
            }

            return $disk->temporaryUrl($this->document_key, now()->addHour());
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
