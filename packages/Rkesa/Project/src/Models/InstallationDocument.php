<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InstallationDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_name', 'exist', 'document_date', 'document_file', 'document_file_name'
    ];

    protected $casts = [
        'exist' => 'boolean'
    ];

    protected $auditExclude = [
        'id', 'project_id', 'document_file'
    ];

    public function generateTags(): array
    {
        return ['project:'.$this->project_id];
    }
}
