<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectUser;
use App\Models\User;
use Illuminate\Support\Str;


class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        "name",
        "slug",
        "description",
        "status",
        "start_date",
        "end_date",
        "settings",
    ];


    protected $casts = [
        "settings" => "array",
    ];

    // boot method to automatically generate slug
    public static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });

        static::updating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_users', 'project_id', 'user_id')
            ->withPivot('role', 'assigned_at')
            ->withTimestamps();
    }


    public function owner()
    {
        return $this->hasOneThrough(
            User::class,
            ProjectUser::class,
            'project_id',
            'id',
            'id',
            'user_id'
        )->where('project_users.role', 'owner');
    }
}
