<?php

namespace App\Modules\Employees\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Modules\Projects\Models\Project;
use App\Modules\Holidays\Models\Holiday;
use App\Modules\Holidays\Models\HolidayWorklog;
use App\Models\User;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'email',
        'title',
        'status',
        'type',
        'department',
        'work_phone',
        'private_phone',
        'birthday',
        'start_date',
        'end_date',
        'avatar',
        'notes',
        'external_id',
        'external_url',
        'external_source',
        'external_group',
        'jira_account_id',
        'user_id',
        'active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'birthday' => 'date',
        'deleted_at' => 'datetime'
    ];

    /**
     * Fields that are managed locally and should not be overwritten by external sync
     */
    protected $localFields = [
        'title',
        'work_phone',
        'private_phone',
        'birthday',
        'start_date',
    ];

    /**
     * Get the employee's full formatted name.
     *
     * @return string
     */
    public function getFormattedNameAttribute()
    {
        return $this->name;
    }

    /**
     * Determine if the employee is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Scope a query to only include active employees.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include Jira employees.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromJira($query)
    {
        return $query->where('external_source', 'jira');
    }

    /**
     * Scope a query to only include internal employees.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInternalEmployees($query)
    {
        return $query->where('type', 'employee');
    }

    /**
     * Scope a query to only include external resources.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExternalResources($query)
    {
        return $query->where('type', 'external');
    }

    /**
     * Calculate tenure of the employee in years.
     *
     * @return float
     */
    public function getTenureAttribute()
    {
        if (!$this->start_date) {
            return 0;
        }

        $end = $this->end_date ?? Carbon::now();
        return $this->start_date->diffInDays($end) / 365;
    }

    /**
     * Determine if the employee is an external resource.
     *
     * @return bool
     */
    public function isExternal()
    {
        return $this->type === 'external';
    }

    /**
     * Get the projects associated with the employee through project hours.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_hours')
            ->withPivot(['date', 'hours'])
            ->distinct()
            ->orderBy('projects.created_at', 'desc');
    }

    public function holiday(): HasOne
    {
        return $this->hasOne(Holiday::class)->where('year', date('Y'));
    }

    public function holidayWorklogs(): HasMany
    {
        return $this->hasMany(HolidayWorklog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}