<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ProjectModels\OrganizationClientContact;

class OrganizationClientContactController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationClientContact::with('client');

            // Filter by organization ID
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('contact_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('designation', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }

            $contacts = ($perPage === 'all')
                ? $query->get()
                : $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Contacts fetched successfully',
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching contacts: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch contacts'], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_client_id' => 'required|integer|exists:organization_clients,organization_client_id',
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'contact_name' => 'required|string|max:100',
                'designation' => 'nullable|string|max:100',
                'stakeholder_role' => 'nullable|string|in:"Decision Maker","Project Manager","Technical Contact","Finance/Billing","Procurement","Legal","General Contact"',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'alternate_phone' => 'nullable|string|max:20',
                'is_primary_contact' => 'sometimes|boolean',
            ]);

            $contact = OrganizationClientContact::create($data);

            return response()->json([
                'message' => 'Contact created successfully',
                'data' => $contact
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating contact: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create contact'], 500);
        }
    }

    public function show($id)
    {
        try {
            $contact = OrganizationClientContact::with('client')->find($id);

            if (!$contact) {
                return response()->json(['message' => 'Contact not found'], 404);
            }

            return response()->json([
                'message' => 'Contact fetched successfully',
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching contact: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch contact'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $contact = OrganizationClientContact::find($id);
            if (!$contact) {
                return response()->json(['message' => 'Contact not found'], 404);
            }

            $data = $request->validate([
                'organization_client_id' => 'sometimes|integer|exists:organization_clients,organization_client_id',
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'contact_name' => 'sometimes|string|max:100',
                'designation' => 'nullable|string|max:100',
                'stakeholder_role' => 'nullable|string|in:"Decision Maker","Project Manager","Technical Contact","Finance/Billing","Procurement","Legal","General Contact"',
                'email' => 'sometimes|nullable|email',
                'phone' => 'sometimes|nullable|string|max:20',
                'alternate_phone' => 'nullable|string|max:20',
                'is_primary_contact' => 'boolean',
            ]);

            $contact->update($data);

            return response()->json([
                'message' => 'Contact updated successfully',
                'data' => $contact
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating contact: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update contact'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $contact = OrganizationClientContact::find($id);
            if (!$contact) {
                return response()->json(['message' => 'Contact not found'], 404);
            }

            $contact->delete();

            return response()->json(['message' => 'Contact deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting contact: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete contact'], 500);
        }
    }
}
