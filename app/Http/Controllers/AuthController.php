<?php

namespace App\Http\Controllers;
use App\Http\Controllers\ApplicationController\ApplicationUserController;
use App\Mail\SendOtpMail;
use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\OrganizationModel\ApplicationOrganizationAcive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Exception;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use App\Models\OrganizationModel\OrganizationUser;


class AuthController extends Controller
{

    public function loginWithEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:organization_users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = OrganizationUser::where('email', $request->email)->first();
        $passdec = $user->password_hash; // No need to decrypt if you're using a hashing algorithm
        if ($user) {
            if ($user->email_verified == 0) {
                return response()->json(['message' => 'Your email is not verified. Verify it first.'], 401);
            }

            if ($user->is_active == 0) {
                return response()->json(['message' => 'Your account is blocked. Contact support.'], 401);
            }

            // Use Hash::check to verify the password against the stored hash
            if (!Hash::check($request->password, $user->password_hash)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        }
        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $user->load('role');
        if ($user->role->system_role_name !== 'Admin') {
            return response()->json(['message' => 'Access denied. Admins only.'], 403);
        }
        $token = JWTAuth::fromUser($user);
        $user->load(['role', 'organization']);
        return response()->json([
            'message' => 'Login nghgh successful.',
            'user' => $user,
            'token' => $token,
        ], 200);

    }

    public function logout()
    {

        // ✅ Get the currently authenticated user
        $user = Auth::guard('applicationusers')->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // ✅ Logout authenticated user
        Auth::guard('users')->logout();
        JWTAuth::invalidate(JWTAuth::getToken());


        return response()->json(['message' => 'Logout Successfully.'], 200);
    }
    public function changePassword(Request $request)
    {
        try {
            // Validation rules for input fields
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => 'Invalid structure. Contact support.'], 422);
            }


            $user = Auth::guard('applicationusers')->user();
            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found.'], 401);
            }

            // Check if the old password matches using Hash::check
            if (!Hash::check($request->old_password, $user->password_hash)) {
                return response()->json(['error' => 'Old password is incorrect.'], 403);
            }

            // Check if the new password is the same as the old one
            if ($request->old_password === $request->new_password) {
                return response()->json(['error' => 'New password cannot be the same as the old one.'], 400);
            }

            // Update the password after hashing it
            $user->password_hash = Hash::make($request->new_password);
            $user->save();

            // Generate a new JWT token for the user after the password change
            $newToken = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Password changed successfully.',
                'user' => $user,
                'token' => $newToken,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function loginWithToken()
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            if (!$user) {
                return response()->json(['message' => 'Invalid token or user not found.'], 401);
            }

            // Check if the user is blocked
            if ($user->is_active == 0) {
                return response()->json(['message' => 'Your account is blocked. Please contact support.'], 403);
            }
            $user->load(['role', 'organization']);

            return response()->json([
                'message' => 'Loginhjjyh token successful.',
                'user' => $user,
            ], 200);

        } catch (JWTException $e) {
            // Handle invalid token errors
            return response()->json(['message' => 'Token is invalid or expired.'], 401);
        }
    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = OrganizationUser::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email not registered.'], 404);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP and timestamp to the database
        $user->otp = $otp;
        $user->otp_created_at = Carbon::now();
        $user->otp_verified = 0; // OTP is initially not verified
        $user->save(); // Save the updated data
        // Send email
        Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your Password Reset OTP');
        });


        return response()->json(['message' => 'OTP sent to your registered email.']);
    }

    public function verifyotp(Request $request)
    {
        // Manual validation using Validator
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:application_users,email',
            'otp' => ['required', 'regex:/^[0-9]{6}$/'],
        ]);
        // Handle validation failure
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = ApplicationUsers::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 404);
        }

        // Check OTP expiry
        if ($user->otp && $user->otp_created_at && now()->diffInMinutes($user->otp_created_at) > 10) {
            return response()->json([
                'message' => 'OTP expired.',
            ], 403);
        }

        // Check if OTP matches
        if ($user->otp !== $request->otp) {
            return response()->json([
                'message' => 'Wrong OTP.',
            ], 401);
        }
        // OTP is valid
        $user->otp = null;
        $user->otp_created_at = null;
        $user->otp_verified = 1;
        $user->save();

        return response()->json([
            'message' => 'OTP verified.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        try {
            // Validation rules for input fields
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:organization_users,email',
                'new_password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => 'Invalid structure. Contact support.'], 422);
            }

            $user = OrganizationUser::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 401);
            }

            if ($user->otp_verified == 0) {
                return response()->json(['message' => 'OTP is not verified.'], 401);
            }

            // Hash the new password instead of using encrypt
            $user->password_hash = Hash::make($request->new_password);
            $user->otp_verified = 0;
            $user->save();

            return response()->json([
                'message' => 'Password changed successfully.',
                'user' => $user,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


    public function updatestatus(Request $request)
    {
        try {
            //code...
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'application_user_id' => 'required|integer|exists:application_users,application_user_id',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $user = ApplicationUsers::find($request->application_user_id);
    
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
    
            $activeOrg = $user->UserActiveOrganization;
    
            if (!$activeOrg) {
                return response()->json(['message' => 'Active organization not found for this user.'], 404);
            }
            $activeOrg->organization_id = $request->organization_id;
            $activeOrg->save();
    
            $role = [
                "system_role_name" => "Admin"
            ];
            $user->load('Client.Organization.Entities');
            //  $user->load('Organization.businessOwnershipType');
            $user->organization = $user->Client->Organization;
           $user->activeOrganization = $user->UserActiveOrganization;
           $userr =OrganizationUser::where('application_user_id',$user->application_user_id)->get();
        // $user->organization=$user->Client-Organization->businessOwnershipType;
        $user->role = $role;
        $user->metadata=$userr;
    
    
            return response()->json([
                'message' => 'User active organization updated successfully.',
                'data' => $activeOrg,
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {
           return $th;
        }
    }

    public function createuserV1(Request $request)
    {
        try {
            // Validate Email Format
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Find if the user exists
            $user = ApplicationUsers::where('email', $request->email)->first();

            if ($user) {
                if ($user->otp_verified == 1) {
                    return response()->json(['message' => 'User already verified.'], 200);
                }
                // Generate new OTP for unverified user
                $otp = rand(100000, 999999);
                $user->otp = $otp;
                $user->otp_created_at = Carbon::now()->toDateTimeString();
                $user->save();
            } else {
                // Create new user with OTP
                $otp = rand(100000, 999999);
                $user = ApplicationUsers::create([
                    'email' => $request->email,
                    'otp' => $otp,
                    'otp_created_at' => Carbon::now()->toDateTimeString()
                ]);
            }

            // Send the OTP via email
            Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Your OTP Code');
            });

            return response()->json([
                'message' => 'OTP sent to email.',
                'email' => $request->email
            ], 201);

        } catch (QueryException $qe) {
            Log::error('Database error: ' . $qe->getMessage());
            return response()->json(['error' => 'Database error'], 500);

        } catch (ValidationException $ve) {
            Log::error('Validation error: ' . $ve->getMessage());
            return response()->json(['error' => 'Validation error'], 422);

        } catch (Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


}

