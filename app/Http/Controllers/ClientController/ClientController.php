<?php

namespace App\Http\Controllers\ClientController;
use App\Models\ClientModels\Client;
use Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{

    public function index(Request $request)
    {
        try {
            $clients = Client::all();
            $clients->load('Organization');
            return response()->json([
                'message' => 'Clients',
                'clients' => $clients
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
                'client_name' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $client = Client::create($data);
            return response()->json([
                'message' => 'Clients Added SuccessFully.',
                'client' => $client
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $client_id)
    {
        try {
            $request->merge(['client_id ' => $client_id]);
            $validator = Validator::make($request->all(), [
                'client_id ' => 'required|integer|exists:clients,client_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $clients = Client::find($client_id);
          $clients->load('Organization');
            return response()->json([
                'message' => "client Found",
                'clients' => $clients
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $client_id)
    {
        try {
            $request->merge([
                'client_id' => $client_id
            ]);

            $rules = [
                'client_id ' => 'required|integer|exists:clients,client_id',
                'client_name' => 'nullable|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $clients = Client::find($client_id);
            $clients->update($request->only([
                'client_name',
            ]));

            return response()->json([
                'message' => 'Client updated successfully.',
                'clients' => $clients
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $client_id)
    {
        try {
            $request->merge([
                'client_id' => $client_id
            ]);
            $validator = Validator::make($request->all(), [
                'client_id ' => 'required|integer|exists:clients,client_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $clients = Client::find($client_id);
            $clients->delete();
            return response()->json([
                'message' => 'client Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete client because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }





}
