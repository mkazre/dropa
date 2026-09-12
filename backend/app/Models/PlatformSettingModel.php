<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PlatformSettingModel extends Model
{
    protected $table         = 'platform_settings';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['setting_key', 'setting_value'];
    protected $returnType    = 'array';

    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();

        return $row['setting_value'] ?? $default;
    }

    public function setValue(string $key, string $value): void
    {
        $existing = $this->where('setting_key', $key)->first();

        if ($existing) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->insert(['setting_key' => $key, 'setting_value' => $value]);
        }
    }
}
