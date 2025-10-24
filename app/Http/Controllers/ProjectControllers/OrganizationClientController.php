<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationClient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationClientController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationClient::with(['contacts', 'projects']);

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', '%' . $search . '%')
                        ->orWhere('client_short_name', 'like', '%' . $search . '%')
                        ->orWhere('client_code', 'like', '%' . $search . '%');
                });
            }

            if ($perPage == 'all') {
                $clients = $query->get();
            } else {
                $clients = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            }

            return response()->json([
                'message' => 'Clients fetched successfully',
                'data' => $clients,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching clients: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch clients'], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'client_name' => [
                    'required',
                    'string',
                    'max:150',
                    Rule::unique('organization_clients')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'client_short_name' => 'nullable|string|max:20',
                'client_code' => 'nullable|string|max:50',
                'website' => 'nullable|string',
                'client_since' => 'nullable|date',
                'country_id' => 'nullable|integer|exists:general_countries,general_country_id',
                'state_id' => 'nullable|integer|exists:general_states,general_state_id',
                'city_id' => 'nullable|integer|exists:general_cities,general_city_id',
                'address' => 'nullable|string',
                'pincode' => 'nullable|string|max:10',
                'industry_id' => 'nullable|integer|exists:general_industries,general_industry_id',
                'status' => 'nullable|string|in:"Active","Inactive"',
            ]);

            $client = OrganizationClient::create($data);
            return response()->json(['message' => 'Created successfully', 'data' => $client], 201);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating client: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create client'], 500);
        }

    }

    public function show($id)
    {
        try {
            $client = OrganizationClient::with(['contacts', 'projects'])->find($id);
            if (!$client)
                return response()->json(['message' => 'Not found'], 404);
            return response()->json([
                'message' => 'Client fetched successfully',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            //  return $e;
            return response()->json(['message' => 'Failed to fetch client'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $client = OrganizationClient::find($id);
            if (!$client)
                return response()->json(['message' => 'Not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'client_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_clients', 'client_name')
                        ->where(function ($query) use ($request, $client) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $client->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($client->organization_client_id, 'organization_client_id'),
                ],
                'client_short_name' => 'sometimes|nullable|string|max:20',
                'client_code' => 'sometimes|nullable|string|max:50',
                'website' => 'sometimes|nullable|string',
                'client_since' => 'sometimes|nullable|date',
                'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
                'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
                'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',
                'address' => 'sometimes|nullable|string',
                'pincode' => 'sometimes|nullable|string|max:10',
                'industry_id' => 'sometimes|nullable|integer|exists:general_industries,general_industry_id',
                'status' => 'sometimes|nullable|string|in:"Active","Inactive"',
            ]);

            $client->update($data);

            return response()->json([
                'message' => 'Client updated successfully',
                'data' => $client
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating client: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update client'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $client = OrganizationClient::find($id);
            if (!$client)
                return response()->json(['message' => 'Not found'], 404);
            $client->delete();
            return response()->json(['message' => 'Client deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting client: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete client'], 500);
        }
    }
}
