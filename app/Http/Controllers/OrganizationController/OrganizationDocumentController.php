<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\RegistrationDocument;
use Illuminate\Http\Request;    use Auth;

class OrganizationDocumentController extends Controller
{

     // List all documents for a registration
     public function index(Request $request, $org_id, $reg_id)
     {
       
         try {
            // Merge org_id from route into request for validation
            $request->merge(['org_id' => $org_id, 'reg_id'=>$reg_id]);

            // Validate the org_id
            $validator = Validator::make($request->all(), [
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'reg_id' => 'required|integer|exists:organization_registrations,registration_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

           // Fetch documents for the specified registration
           $documents = RegistrationDocument::where('registration_id', $reg_id)->get();

           if ($documents->isEmpty()) {
               return response()->json(['message' => 'No documents found for this registration.'], 404);
           }

           return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }


         



     }

    
    // Create a new document for a registration
    public function store(Request $request, $org_id, $reg_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge org_id and reg_id into the request for validation
            $request->merge(['org_id' => $org_id, 'reg_id' => $reg_id]);
    
            // Validate inputs
            $validator = Validator::make($request->all(), [
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'reg_id' => 'required|integer|exists:organization_registrations,registration_id',
                'type' => 'required|string|max:255',
                'applicable' => 'required|boolean',
                'document_link' => 'required|url',
                'document_number' => 'required|string|max:255',
                'registration_date' => 'required|date',
            ]);
    
            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }
    
            // Create a new document record
            $document = new RegistrationDocument($request->all());
            $document->registration_id = $reg_id;
            $document->save();
    
            // Return the newly created document
            return response()->json($document, 201);
    
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    

    // display specific organization 
// Get a specific document for a registration
    public function show($org_id, $reg_id, $doc_id)
    {
        $document = RegistrationDocument::where('registration_id', $reg_id)->find($doc_id);
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        return response()->json($document);
    }

    // update the orgaization
    public function update(Request $request, $org_id, $reg_id, $doc_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge org_id and reg_id into the request for validation
            $request->merge(['org_id' => $org_id, 'reg_id' => $reg_id,"doc_id"=> $doc_id]);
    
            // Validate inputs
            $validator = Validator::make($request->all(), [
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'reg_id' => 'required|integer|exists:organization_registrations,registration_id',
                'doc_id' => 'required|integer|exists:registration_document,id',
                'type' => 'sometimes|string|max:255',
                'applicable' => 'sometimes|boolean',
                'document_link' => 'sometimes|url',
                'document_number' => 'sometimes|string|max:255',
                'registration_date' => 'sometimes|date',
            ]);
    
            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }
    
            $document = RegistrationDocument::find($doc_id);

            $document->update($request->only([
                'type' ,
                'applicable' ,
                'document_link',
                'document_number' ,
                'registration_date',

            ]));
    
            // Return the newly created document
            return response()->json([
                'Message' => 'Document updated successfully.',
                 "updated-documet"=>$document
            ], 500);
    
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }
    
  
// Delete a specific document
    public function destroy($org_id, $reg_id, $doc_id)
    {
        $document = RegistrationDocument::where('registration_id', $reg_id)->find($doc_id);
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        $document->delete();
        return response()->json(['message' => 'Document deleted successfully']);
    }
}
