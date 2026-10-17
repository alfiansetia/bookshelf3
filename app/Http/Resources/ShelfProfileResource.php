<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Profil pemilik rak untuk halaman publik.
 *
 * @mixin User
 */
class ShelfProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $this->avatar_url,
            'tagline' => $this->tagline,
            'bio' => $this->bio,
            'theme' => $this->shelfTheme()->value,
            'is_public' => $this->is_public,
            'socials' => $this->socialProfiles(),
            'member_since' => $this->created_at?->translatedFormat('F Y'),
            'joined_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
