<?php

namespace App\Http\Controllers\ApplicationController;
use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\OrganizationModel\OrganizationUser;
use Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserController extends Controller
{

    public function index(Request $request)
    {
        try {    
            $users = ApplicationUsers::all();
            $users->load('Client');
            return response()->json([
                'message' => 'Application Users',
                'users' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'nullable|string|max:100',
                'email' => 'required|email|max:100|unique:application_users,email',
                'password_hash' => 'required|string|max:255',
                'is_active' => 'nullable|boolean',
                'country_phone_code' => ['required', 'regex:/^\+\d{1,4}$/'], // Example: +91, +1, +44, etc.
                'phone_number' => ['required', 'regex:/^\d{7,15}$/'],   

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $data['last_login_at'] = now(); // current timestamp

            // Hash password
            $data['password_hash'] = Hash::make($data['password_hash']);
            $data['password'] = encrypt($data['password_hash']);
            $users = ApplicationUsers::create($data);
            return response()->json([
                'message' => 'Application User Added SuccessFully.',
                'users' => $users
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $user_id)
    {
        try {
            $request->merge(['application_user_id ' => $user_id]);
            $validator = Validator::make($request->all(), [
                'application_user_id ' => 'required|integer|exists:application_users,application_user_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $users = ApplicationUsers::find($user_id);
            $users->password=decrypt($users->password);
            return response()->json([
                'message' => "Application User Found",
                'users' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // public function update(Request $request, $user_id)
    // {
    //     try {
    //         $request->merge([
    //             'application_user_id' => $user_id
    //         ]);
    //         $rules = [
    //             'application_user_id ' => 'required|integer|exists:application_users,application_user_id',
    //             'full_name' => 'nullable|string|max:100',
    //             'email' => 'required|email|max:100|unique:application_users,email',
    //             'password_hash' => 'required|string|max:255',
    //             'is_active' => 'nullable|boolean',
    //         ];
    //         $validator = Validator::make($request->all(), $rules);
    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }
    //         $users = ApplicationUsers::find($user_id);
    //         $users->update($request->only([
    //             'full_name',
    //             'email',
    //             'password_hash',
    //             'is_active',
    //         ]));

    //         return response()->json([
    //             'message' => 'Application User updated successfully.',
    //             'users' => $users
    //         ], 201);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Something went wrong. Please try again later.',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }

   
   
    public function update(Request $request, $user_id)
{
    try {
        $request->merge([
            'application_user_id' => $user_id
        ]);

        $rules = [
            'application_user_id' => 'required|integer|exists:application_users,application_user_id',
            'full_name' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|email|max:100|unique:application_users,email,' . $user_id . ',application_user_id',
            'password_hash' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|nullable|boolean',
             'country_phone_code' => ['sometimes', 'regex:/^\+\d{1,4}$/'], // Example: +91, +1, +44, etc.
             'phone_number' => ['sometimes', 'regex:/^\d{7,15}$/'],   
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = ApplicationUsers::find($user_id);

        // Hash the password if it's present
        if ($request->filled('password_hash')) {
            $request->merge([
                'password_hash' => Hash::make($request->password_hash)
            ]);
        }

        // Always update last_login_at
        $request->merge([
            'last_login_at' => now()
        ]);

        $user->update($request->only([
            'full_name',
            'email',
            'password_hash',
            'is_active',
            'last_login_at',
             'country_phone_code',
             'phone_number'

        ]));

        return response()->json([
            'message' => 'Application User updated successfully.',
            'users' => $user
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}

   
    public function destroy(Request $request, $user_id)
    {
        try {
            $request->merge([
                'application_user_id' => $user_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_id' => 'required|integer|exists:application_users,application_user_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $users = ApplicationUsers::find($user_id);
            $users->delete();
            return response()->json([
                'message' => 'Application User Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Application User  because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }


    public function createUser(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'full_name' => 'required|string|max:255',
            'hash_password' => 'required|string|min:8',
            'country_phone_code' => 'required|string|max:10',
            'phone_number' => 'required|string|max:20|unique:application_users,phone_number',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = ApplicationUsers::where('email', $request->email)->first();
         if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User with this email does not exist.',
        ], 404);
    }
        
       $user->update([
        'full_name' => $request->input('full_name'),
        'password_hash' => Hash::make($request->input('hash_password')),
        'phone_number' => $request->input('phone_number'),
        'country_phone_code' => $request->input('country_phone_code'),
    ]);
        return response()->json([
            'success' => true,
            'message' => 'User account created successfully.',
            'data' => [
                'application_users_id' => $user->application_user_id,
            ],
        ], 201);
    }


}
