<?php

namespace App\Models;

use App\Http\Services\GoogleDriveService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drive extends Model
{
    use HasFactory;

    protected $fillable = [
        'drive_type', // GOOGLE_DRIVE
        'access_token',
        'refresh_token',
        'expires_in',
        'folder_id',
        'status', // 0 - not connected, 1 - connected
        'account',
        'account_id'
    ];

    public static function availableTypes(){
        return ['google'];
    }

    public static function getService($driveType){
        switch ($driveType){
            case 'google':
                return new GoogleDriveService();
                break;
        }
    }
}
