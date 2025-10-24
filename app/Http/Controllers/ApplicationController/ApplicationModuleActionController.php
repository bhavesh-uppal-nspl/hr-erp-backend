<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\ApplicationModuleAction;
use App\Models\ApplicationModels\ApplicationModules;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationModuleActionController extends Controller
{

    public function index(Request $request)
    {
        try {
            $modules = ApplicationModuleAction::all();
            $modules->load('Modules');
            return response()->json([
                'message' => 'Application Modules Actions',
                'moduleaction' => $modules
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
                'module_action_name' => 'nullable|string|max:100|unique:application_module_actions,module_action_name',
                'description' => 'sometimes|nullable|string|max:255',
                'module_action_code' => 'nullable|string|max:100|unique:application_module_actions,module_action_code',
                'application_module_id' => 'required|integer|exists:application_modules,application_module_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $modules = ApplicationModuleAction::create($data);
            return response()->json([
                'message' => 'Application Module Action Added SuccessFully.',
                'moduleaction' => $modules
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $module_action_id)
    {
        try {
            $request->merge(['application_module_action_id' => $module_action_id]);
            $validator = Validator::make($request->all(), [
                'application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $modules = ApplicationModuleAction::find($module_action_id);
            return response()->json([
                'message' => "Application Module Action Found",
                'modulesaction' => $modules
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $module_action_id)
    {
        try {
            $request->merge([
                'application_module_action_id' => $module_action_id
            ]);
            $rules = [
                'application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',
                'application_module_id' => 'required|integer|exists:application_modules,application_module_id',
                'module_action_name' => [
                    'sometimes',
                    'string',
                    'max:30',
                    Rule::unique('application_module_actions', 'module_action_name')->ignore($module_action_id, 'application_module_action_id')
                ],
                'module_action_code' => [
                    'sometimes',
                    'string',
                    'max:30',
                    Rule::unique('application_module_actions', 'module_action_code')->ignore($module_action_id, 'application_module_action_id')
                ],
                'description ' => 'sometimes|nullable|string|max:255',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $module = ApplicationModuleAction::find($module_action_id);
            $module->update($request->only([
                'module_action_name',
                'description',
                'module_action_code',
                'application_module_id'

            ]));

            return response()->json([
                'message' => 'Application module Action updated successfully.',
                'moduleaction' => $module
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $module_action_id)
    {
        try {
            $request->merge([
                'application_module_action_id' => $module_action_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $modules = ApplicationModuleAction::find($module_action_id);
            $modules->delete();
            return response()->json([
                'message' => 'Application Module Action Deleted Successfully'
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
