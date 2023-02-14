<?php

namespace App\Http\Controllers;

use App\Http\Services\GoogleDriveService;
use App\Models\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;

class DriveController extends Controller
{
    /**
     * Show cloud integration page
     */
    public function index(){
        $drive = Drive::first();

        return view('drives.index', compact('drive'));
    }

    /**
     * Connect to Google drive (redirects to provider)
     */
    public function connect(){
        $scopes = [
            'https://www.googleapis.com/auth/drive.file',
        ];
        $redirectUrl = url('drives/google/callback');
        $parameters = ["access_type" => "offline", "prompt" => "consent select_account"];

        return Socialite::driver('google')
            ->scopes($scopes)
            ->redirectUrl($redirectUrl)
            ->with($parameters)
            ->redirect();
    }

    /**
     *
     * Handle provider callback
     */
    public function handleProviderGoogleCallback(Request $request){
        $appUrl = URL::to('/');
        if (!$request->has('code')){
            return redirect()->to($appUrl . '/drives/index?success=false');
        }

        $driveTypes = Drive::availableTypes();

        $currentDrive = Drive::whereIn('drive_type', $driveTypes)
            ->first();

        $authUser = Socialite::driver('google')->stateless()->user();
        if($currentDrive && $authUser->id == $currentDrive->account_id){
            // refresh token
            $currentDrive->access_token = $authUser->token;
            $currentDrive->refresh_token = $authUser->refreshToken;
            $currentDrive->expires_in = $authUser->expiresIn;
            $currentDrive->status = 1;
            $currentDrive->save();
        }
        else{
            // create new drive and delete the current one if exists
            $googleDrive = Drive::create([
                'access_token' => $authUser->token,
                'refresh_token' => $authUser->refreshToken,
                'drive_type' => 'google',
                'expires_in' => $authUser->expiresIn,
                'status' => 1,
                'account' => $authUser->email,
                'account_id' => $authUser->id,
            ]);

            $google_drive_service = new GoogleDriveService();
            $result = $google_drive_service->checkExistsFolder($googleDrive);

            $googleDrive->folder_id = $result;
            $googleDrive->save();

            if($currentDrive){
                $currentDrive->delete();
            }
        }

        return redirect()->to($appUrl . '/drives/index?success=true');

    }

    /**
     * Destroy connection
     */
    public function delete($provider){
        if(!in_array($provider, Drive::availableTypes())){
            abort(404);
        }

        Drive::where('drive_type', $provider)->delete();

        return Redirect::back()->with(['success' => 'true']);
    }
}
