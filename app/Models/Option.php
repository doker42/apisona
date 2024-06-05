<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use HasFactory, SoftDeletes;

    public const OPTION_PEOPLE              = 'people';
    public const OPTION_PROJECTS            = 'projects';
    public const OPTION_STORAGE             = 'storage';
    public const OPTION_ANALYTICS           = 'start_analytics';
    public const OPTION_WHITE_LABEL         = 'white_label';
    public const OPTION_CONDITIONALLY_FREE  = 'conditionally_free';
    public const OPTION_QUICK_IMPORT        = 'quick_import';
    public const OPTION_ADMINS              = 'admins';
    public const OPTION_EARLY_ACCESS        = 'early_access';
    public const OPTION_ARCHITECT_SUPPORT   = 'architect_support';
    public const OPTION_SERVICE             = 'service';
    public const OPTION_SERVICE_CLIENT      = 'client';   // todo remove
    public const OPTION_SERVICE_PERSONAL    = 'personal'; // todo remove

    public const OPTION_UNLIM_VALUE         = 9999999;
    public const OPTION_UNLIM_NAME          = 'unlimited';

    public const OPTION_BITES_IN_GB         = 1073741824;

    public const TYPE_STRING = 'string';
    public const TYPE_BOOL   = 'bool';


    protected $fillable = ['name'];

    protected $table = 'options';


    /**
     * @param $value
     * @return int
     */
    public static function getConditionallyFreeOption($value): int
    {
        return (int)$value;
    }

}
