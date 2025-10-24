<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTemplates;
use App\Models\ProjectModels\OrganizationProjectTemplateTasks;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationProjectTemplatesTasksController extends Controller
{
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTemplateTasks::with('projectTemplate', 'projectTemplateMileStone', 'projectTaskTemplate');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->whereHas('projectTemplate', function ($q) use ($search) {
                    $q->where('template_name', 'like', '%' . $search . '%')
                        ->orWhere('template_description', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projects = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Templates tasks fetched successfully',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Templates Tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Templates Tasks'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_template_id' => 'required|integer|exists:organization_project_templates,organization_project_template_id',
                'organization_project_template_milestone_id' => 'nullable|integer|exists:organization_project_template_milestones,organization_project_template_milestone_id',
                'organization_project_task_template_id' => [
                    'required',
                    'integer',
                    'exists:organization_project_task_templates,organization_project_task_template_id',
                    Rule::unique('organization_project_template_tasks')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_project_template_id', $request->organization_project_template_id);
                        }),
                ],
                'remarks' => 'nullable|string|max:255'
            ]);

            $maxOrder = OrganizationProjectTemplateTasks::where('organization_project_template_id', $request->organization_project_template_id)
                ->max('task_order');

            $data['task_order'] = $maxOrder ? $maxOrder + 1 : 1;

            $category = OrganizationProjectTemplateTasks::create($data);

            return response()->json([
                'message' => 'Project Template tasks created successfully',
                'data' => $category
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating template tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create template tasks'], 500);
        }

    }

    public function show($id)
    {
        try {
            $category = OrganizationProjectTemplateTasks::with('projectTemplate', 'projectTemplateMileStone', 'projectTaskTemplate')->find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            return response()->json([
                'message' => 'Proejct template tasks fetched successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching template tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch template tasks'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $template = OrganizationProjectTemplateTasks::find($id);

            if (!$template)
                return response()->json(['message' => 'Not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|nullable|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_template_id' => 'sometimes|nullable|integer|exists:organization_project_templates,organization_project_template_id',
                'organization_project_template_milestone_id' => 'sometimes|nullable|integer|exists:organization_project_template_milestones,organization_project_template_milestone_id',
                'organization_project_task_template_id' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'exists:organization_project_task_templates,organization_project_task_template_id',
                    Rule::unique('organization_project_template_tasks')
                        ->where(function ($query) use ($request, $template) {
                            return $query->where('organization_project_template_id', $request->organization_project_template_id ?? $template->organization_project_template_id);
                        })
                        ->ignore($id, 'organization_project_template_task_id'),
                ],

                'remarks' => 'sometimes|nullable|string|max:255'
            ]);

            $template->update($data);

            return response()->json([
                'message' => 'project template tasks updated successfully',
                'data' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating template tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update template tasks'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = OrganizationProjectTemplateTasks::find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            $category->delete();
            return response()->json(['message' => 'Project template tasks deleted successfully']);

        } catch (\Exception $e) {
            \Log::error('Error deleting projetc template tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete Project template tasks'], 500);
        }
    }
}
