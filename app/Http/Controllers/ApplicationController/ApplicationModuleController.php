<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\ApplicationModules;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationModuleController extends Controller
{

    public function index(Request $request)
    {
        try {
           $query = ApplicationModules::query();
            $per = $request->input('per_page', 10);
            $search = $request->input('search');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('module_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            $modules = ApplicationModules::with('ModuleAction')->get();
            return response()->json([
                'message' => 'OK',
                'modules' => $modules,

            ]);
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
                'module_name' => 'nullable|string|max:100|unique:application_modules,module_name',
                'description' => 'sometimes|nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $modules = ApplicationModules::create($data);
            return response()->json([
                'message' => 'Application Module Added SuccessFully.',
                'modules' => $modules
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $module_id)
    {
        try {
            $request->merge(['application_module_id' => $module_id]);
            $validator = Validator::make($request->all(), [
                'application_module_id' => 'required|integer|exists:application_modules,application_module_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $modules = ApplicationModules::find($module_id);
            return response()->json([
                'message' => "Application Module Found",
                'modules' => $modules
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $module_id)
    {
        try {
            $request->merge([
                'application_module_id' => $module_id
            ]);
            $rules = [
                'application_module_id' => 'required|integer|exists:application_modules,application_module_id',
                'module_name' => [
                    'sometimes',
                    'string',
                    'max:30',
                    Rule::unique('application_modules', 'module_name')->ignore($module_id, 'application_module_id')
                ],
                'description' => 'sometimes|nullable|string|max:255',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $module = ApplicationModules::find($module_id);
            $module->update($request->only([
                'module_name',
                'description',

            ]));

            return response()->json([
                'message' => 'Application module updated successfully.',
                'module' => $module
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $module_id)
    {
        try {
            $request->merge([
                'application_module_id' => $module_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_module_id' => 'required|integer|exists:application_modules,application_module_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $modules = ApplicationModules::find($module_id);
            $modules->delete();
            return response()->json([
                'message' => 'Application Module Deleted Successfully'
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
