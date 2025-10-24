<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\ApplicationErrorLogs;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationErrorLogsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $errorlogs = ApplicationErrorLogs::all();
            return response()->json([
                'message' => 'Application Error Logs',
                'errorlogs' => $errorlogs
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
                'application_user_id' => 'nullable|integer|exists:application_users,application_user_id',
                'client_id' => 'nullable|integer',
                'module_name' => 'nullable|string|max:100|unique:application_error_logs,module_name',
                'error_message' => 'required|string',
                'stack_trace' => 'nullable|string',
                'url_or_endpoint' => 'nullable|string|max:255',
                'payload_data' => 'nullable|string',
                'error_type' => 'nullable|in:frontend,backend,api,db,unknown',
                'severity' => 'nullable|in:info,warning,error,critical',
                'logged_at' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $errorlogs = ApplicationErrorLogs::create($data);
            return response()->json([
                'message' => 'Application Error Logs Added SuccessFully.',
                'errorlogs' => $errorlogs
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $error_log_id)
    {
        try {
            $request->merge(['error_log_id' => $error_log_id]);
            $validator = Validator::make($request->all(), [
                'error_log_id' => 'required|integer|exists:application_error_logs,error_log_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $errorlogs = ApplicationErrorLogs::find($error_log_id);
            return response()->json([
                'message' => "Application Error Logs Found",
                'errorlogs' => $errorlogs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $error_log_id)
    {
        try {
            $request->merge([
                'error_log_id' => $error_log_id
            ]);
            $rules = [
                'error_log_id' => 'required|integer|exists:application_error_logs,error_log_id',
                'application_user_id' => 'nullable|integer|exists:application_users,application_user_id',
                'client_id' => 'nullable|integer',
                'module_name' => [
                    'sometimes',
                    'string',
                    'max:30',
                    Rule::unique('application_error_logs', 'module_name')->ignore($error_log_id, 'error_log_id')
                ],
                'error_message' => 'sometimes|string',
                'stack_trace' => 'sometimes|nullable|string',
                'url_or_endpoint' => 'sometimes|nullable|string|max:255',
                'payload_data' => 'sometimes|nullable|string',
                'error_type' => 'sometimes|nullable|in:frontend,backend,api,db,unknown',
                'severity' => 'sometimes|nullable|in:info,warning,error,critical',
                'logged_at' => 'sometimes|nullable|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $errorlogs = ApplicationErrorLogs::find($error_log_id);
            $errorlogs->update($request->only([
                'application_user_id',
                'client_id',
                'module_name',
                'error_message',
                'stack_trace',
                'url_or_endpoint',
                'payload_data',
                'error_type',
                'severity',
                'logged_at',
            ]));

            return response()->json([
                'message' => 'Application Error Logs updated successfully.',
                'errorlogs' => $errorlogs
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $error_log_id)
    {
        try {
            $request->merge([
                'error_log_id' => $error_log_id
            ]);
            $validator = Validator::make($request->all(), [
                 'error_log_id' => 'required|integer|exists:application_error_logs,error_log_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $errorlogs = ApplicationErrorLogs::find($error_log_id);
            $errorlogs->delete();
            return response()->json([
                'message' => 'Application Error Logs Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Application modules  because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
