<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\ApplicationModels\ApplicationUserRoleAssignment;
use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\ApplicationOrganizationAcive;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationUserRoleAssignment;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\OrganizationModel\OrganizationUser;
use Illuminate\Http\Request;
use Auth;

use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;


class OrganizationUserController extends Controller
{


    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'search' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Start building the query
            $query = OrganizationUser::with('Applicationuser', 'RoleAssignment.ApplicationUserRole', 'UserTypes')->where('organization_id', $org_id);

            // Apply search by name
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('user_name', 'like', '%' . $search . '%');
            }

            // Apply status filter
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
            }

            // Get results
            $users = $query->get();

            return response()->json([
                'status' => 'success',
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function createuserV3(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'general_system_role_id' => 'required|integer|exists:general_system_roles,general_system_role_id',  // role_id from request input
                'password_hash' => 'required|string|min:6',
                'user_name' => 'required|string|max:50|unique:organization_users,user_name',
                'phone' => 'required|nullable|string|max:10|unique:organization_users,phone',
                'phone_verified' => 'required|boolean',
                'last_login' => 'nullable|date',
                'is_active' => 'required|nullable|boolean',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $existingUser = OrganizationUser::where('email', $request->email)->first();
            if ($existingUser) {
                if (!$existingUser->email_verified) {
                    return response()->json([
                        'error' => 'Email is not verified. Please verify your email before proceeding.'
                    ], 400);
                }
            } else {
                return response()->json([
                    'error' => 'No user found with the provided email.'
                ], 404);
            }
            $data = $request->only(['user_name', 'phone', 'email', 'general_system_role_id', 'is_active', 'organization_id']);
            $data['password_hash'] = Hash::make($request->password_hash);  // Hash the password 
            $user = OrganizationUser::create($data);

            return response()->json([
                'message' => 'Organization User created successfully.',
                'data' => $user
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $user_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $validator = \Validator::make(
                ['organization_id' => $org_id, 'organization_user_id' => $user_id],
                [
                    'organization_id' => 'required|exists:organizations,organization_id',
                    'organization_user_id' => 'required|exists:organization_users,organization_user_id',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }
            $user = OrganizationUser::findOrFail($user_id);
            $user->load('Applicationuser', 'RoleAssignment.ApplicationUserRole', 'UserTypes');
            // $user->load('role');
            return response()->json([
                'message' => 'User Found',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $user_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id, 'organization_user_id' => $user_id]);
            $request->validate([
                'organization_id' => 'required|exists:organizations,organization_id',
                'organization_user_id' => 'required|exists:organization_users,organization_user_id',
            ]);

            $orgUser = OrganizationUser::findOrFail($user_id);

            // Delete related role assignments
            OrganizationUserRoleAssignment::where('organization_user_id', $user_id)->delete();

            // Delete the user
            $orgUser->delete();

            return response()->json([
                'message' => 'Organization User deleted successfully.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateUser(Request $request, $org_id, $org_user_id)
    {
        try {
            // Authenticated user check
            $authUser = Auth::guard('applicationusers')->user();
            $organizationIds = $authUser->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Fetch organization user entry
            $orgUser = OrganizationUser::where('organization_id', $org_id)
                ->where('organization_user_id', $org_user_id)
                ->firstOrFail();

            // Get linked application user
            $appUser = ApplicationUsers::findOrFail($orgUser->application_user_id);

            // Merge org_id for validation
            $request->merge(['organization_id' => $org_id]);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'email' => [
                    'sometimes',
                    'email',
                    Rule::unique('application_users', 'email')->ignore($appUser->application_user_id, 'application_user_id')
                ],
                'full_name' => 'sometimes|string|max:255',
                'password_hash' => 'sometimes|string|min:8',
                'phone_code' => 'sometimes|string|max:10',
              
                'phone_number' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('application_users', 'phone_number')->ignore($appUser->application_user_id, 'application_user_id')
                ],
                'organization_user_type_id' => 'sometimes|integer|exists:organization_user_types,organization_user_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }



            // Update Application User
            $appUser->update([
                'full_name' => $request->input('full_name', $appUser->full_name),
                'phone_number' => $request->input('phone_number', $appUser->phone_number),
                'country_phone_code' => $request->input('phone_code', $appUser->phone_code),
                'email' => $request->input('email', $appUser->email),


            ]);



            // Update OrganizationUser type if needed
            if ($request->filled('organization_user_type_id')) {
                $orgUser->update([
                    'organization_user_type_id' => $request->organization_user_type_id,
                ]);
            }

            if ($request->filled('application_user_role_id')) {

                OrganizationUserRoleAssignment::updateOrCreate(
                    [
                        'application_user_role_id' => $request->application_user_role_id,
                        'organization_id' => $org_id,
                        'assigned_at' => now(),
                        'organization_user_id' => $orgUser->organization_user_id
                    ],

                );

            }

            // hr    rahul1234   ///rahul@gmail.com

            //shakham@gmail.com
            // shakham@gmail.com


            //uppal12345@gmail.com   //bhavesh12345

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => [
                    'organization_user' => $orgUser,
                    'application_user' => $appUser,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }







    public function createuserV1(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',
                'email' => 'required|email|unique:organization_users,email',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Generate a random 6-digit OTP
            $otp = rand(100000, 999999);
            $user = OrganizationUser::create([
                'oragnization_id' => $org_id,
                'email' => $request->email,
                'otp' => $otp,
                'email_verified' => 0
            ]);

            // Send the OTP via email
            Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Your OTP Code');
            });

            return response()->json([
                'message' => 'OTP sent to email.',

            ], 201);

        } catch (QueryException $qe) {
            // Log and return database-related errors
            Log::error('Database error: ' . $qe->getMessage());
            return response()->json(['error' => 'Database error'], 500);

        } catch (ValidationException $ve) {
            // Log and return validation-related errors
            Log::error('Validation error: ' . $ve->getMessage());
            return response()->json(['error' => 'Validation error'], 422);

        } catch (Exception $e) {
            // Log and return a generic error
            Log::error('General error: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function createuserV2(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id
            ]);

            // Validation rules
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',
                'email' => 'required|email|exists:organization_users,email',  // Ensure email exists in DB
                'otp' => 'required|integer|min:6', // Ensure OTP is valid (integer and min 6 digits)
            ]);

            // If validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Retrieve the user by email
            $user = OrganizationUser::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Check if OTP matches
            if ($user->otp != $request->otp) {
                return response()->json(['error' => 'Invalid OTP'], 400);
            }

            // Update the user's email_verified field to 1 and otp to null
            $user->email_verified = 1;
            $user->otp = null;
            $user->save();

            return response()->json(['message' => 'Email verified successfully'], 200);

        } catch (QueryException $qe) {
            // Log and return database-related errors
            Log::error('Database error: ' . $qe->getMessage());
            return response()->json(['error' => 'Database error'], 500);

        } catch (ValidationException $ve) {
            // Log and return validation-related errors
            Log::error('Validation error: ' . $ve->getMessage());
            return response()->json(['error' => 'Validation error'], 422);

        } catch (Exception $e) {
            // Log and return a generic error
            Log::error('General error: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function updateStatus(Request $request, $org_id, $user_id)
    {

        $user = Auth::guard('applicationusers')->user();
        $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
        if (!in_array($org_id, $organizationIds)) {
            return response()->json([
                'messages' => 'unauthorized'
            ], 401);

        }

        $User = OrganizationUser::where('organization_id', $org_id)
            ->where('organization_user_id', $user_id)
            ->first();

        $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);

        $User->is_active = $isActive ? 1 : 0;
        $User->save();

        return response()->json([
            'message' => 'User status updated successfully.'
        ]);





    }


    public function createUsernew(Request $request, $org_id)
    {

        $user = Auth::guard('applicationusers')->user();
        $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
        if (!in_array($org_id, $organizationIds)) {
            return response()->json([
                'messages' => 'unauthorized'
            ], 401);

        }

        $request->merge(['organization_id' => $org_id]);

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'email' => 'required|string|max:255|unique:application_users,email',
            'user_name' => 'required|string|max:255',
            'password_hash' => 'required|string|min:8',
            'phone_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20|unique:application_users,phone_number',
            'organization_user_type_id' => 'required|integer|exists:organization_user_types,organization_user_type_id',
            'application_user_role_id' => 'nullable|integer|exists:application_user_roles,application_user_role_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        //   check if organization    user  client
        $data = Organization::findOrFail($org_id);
        $client_id = $data->client_id;

        $storedUser = ApplicationUsers::create([
            'full_name' => $request->input('user_name'),
            'password_hash' => Hash::make($request->input('password_hash')),
            'phone_number' => $request->input('phone'),
            'country_phone_code' => $request->input('phone_code'),
            'email' => $request->input('email'),
            'client_id' => $client_id,
            'is_active' => 1,
            'account_created' => 1,
            'otp_verified' => 1

        ]);

        //Password123
        //john.doe@example.com


        // store that user in organization id
        $storedata = OrganizationUser::create([
            'organization_id' => $org_id,
            'organization_user_type_id' => $request->organization_user_type_id,
            'application_user_id' => $storedUser->application_user_id,

        ]);


        // if the user is employee ad that organization_user id in the employee 

        if ($request->employee_id) {
    Employees::where('employee_id', $request->employee_id)
        ->update([
            'organization_user_id' => $storedata->organization_user_id,
        ]);
}



        if ($request->filled('application_user_role_id')) {

            OrganizationUserRoleAssignment::updateOrCreate(
                [
                    'application_user_role_id' => $request->application_user_role_id,
                    'organization_id' => $org_id,
                    'assigned_at' => now(),
                    'organization_user_id' => $storedata->organization_user_id
                ],

            );

        }


        ApplicationOrganizationAcive::create([
            'application_user_id' => $storedUser->application_user_id,
            'organization_id' => $org_id,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'User Added successfully.',
            'data' => [
                'user' => $storedUser,
            ],
        ], 201);
    }








}



