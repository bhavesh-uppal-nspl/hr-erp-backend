<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\Applicationuserloginlogs;
use App\Models\ApplicationModels\ApplicationUsers;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserloginController extends Controller
{

    public function index(Request $request)
    {
        try {
            $userlogins = Applicationuserloginlogs::all();
            return response()->json([
                'message' => 'Application User Login Logs',
                'userlogins' => $userlogins
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
                'application_user_id' => 'required|integer|exists:application_users,application_user_id',
                'login_at' => 'required|date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $userlogin = Applicationuserloginlogs::create($data);
            return response()->json([
                'message' => 'Application User Login Logs Added SuccessFully.',
                'userlogin' => $userlogin
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $login_log_id)
    {
        try {
            $request->merge(['application_user_login_log_id ' => $login_log_id]);
            $validator = Validator::make($request->all(), rules: [
                'application_user_login_log_id' => 'required|integer|exists:application_user_login_logs,application_user_login_log_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $loginlogs = Applicationuserloginlogs::find($login_log_id);
            return response()->json([
                'message' => "Application User Login Logs Found",
                'loginlogs' => $loginlogs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $login_log_id)
    {
        try {
            $request->merge([
                'application_user_login_log_id' => $login_log_id
            ]);
            $rules = [
                'application_user_id' => 'sometimes|integer|exists:application_users,application_user_id',
                'application_user_login_log_id' => 'required|integer|exists:application_user_login_logs,application_user_login_log_id',
                'login_at' => 'sometimes|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $users = ApplicationUsers::find($login_log_id);
            $users->update($request->only([
                'application_user_id',
                'login_at'
            ]));

            return response()->json([
                'message' => 'Application User updated successfully.',
                'users' => $users
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $login_log_id)
    {
        try {
            $request->merge([
                'application_user_login_log_id' => $login_log_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_login_log_id' => 'required|integer|exists:application_user_login_logs,application_user_login_log_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $loginlogs = Applicationuserloginlogs::find($login_log_id);
            $loginlogs->delete();
            return response()->json([
                'message' => 'Application User Login Logs Deleted Successfully'
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
}
