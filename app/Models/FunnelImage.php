<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;

class FunnelImage extends Model
{
    protected $fillable = ['domain_id', 'image_path', 'original_name', 'link_target', 'video_url', 'sort_order'];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function getImageUrlAttribute()
    {
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        $domainBunnyHost = trim((string) ($this->domain?->bunny_hostname ?? ''));
        $forcedBaseUrl = $domainBunnyHost !== '' ? 'https://'.$domainBunnyHost : null;
        $legacyPath = public_path('uploads/funnels/'.$this->image_path);
        $relativePath = is_file($legacyPath)
            ? 'uploads/funnels/'.$this->image_path
            : 'storage/uploads/funnels/'.$this->image_path;

        return Setting::mediaUrl($relativePath, null, $forcedBaseUrl);
    }
}
