<?php

namespace App\Http\Controllers\EmployeeController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeesModel\Employees;

class FaceRecognitionController extends Controller
{
    public function recognize(Request $request)
    {
        try {
            // 1. Validate uploaded image
            $request->validate([
                'live_face' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // 2. Store image temporarily
            $file = $request->file('live_face');
            $tempPath = $file->store('temp', 'public');
            $imagePath = Storage::path("public/" . $tempPath);

            // Convert uploaded image to base64
            $imageData = base64_encode(file_get_contents($imagePath));

            // 3. Fetch all employees with stored profile images
            $employees = Employees::whereNotNull('profile_image_url')->get();

            if ($employees->isEmpty()) {
                Storage::delete($tempPath);
                return response()->json([
                    'status' => 'error',
                    'message' => 'No employee profile images found in database.'
                ], 404);
            }

            // Prepare stored employee images for NodeJS API
            $employeeImages = [];
            foreach ($employees as $emp) {
                $employeeImages[] = [
                    'employee_id' => $emp->employee_id,
                    'image_url' => Storage::url($emp->profile_image_url), // Publicly accessible path
                ];
            }

            // // 4. Send request to NodeJS API for face matching
            $nodeResponse = Http::post("http://localhost:5000/face/match", [
                'live_face' => $imageData,
                'employee_images' => $employeeImages
            ]);

            // Delete temporary image after request
            Storage::delete($tempPath);

            // Check NodeJS response
            if ($nodeResponse->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Face recognition API request failed.'
                ], 500);
            }

            $result = $nodeResponse->json();

            // 5. If a match is found
            if (isset($result['match']) && $result['match'] === true) {
                $employee = Employees::find($result['employee_id']);
                return response()->json([
                    'status' => 'success',
                    'match' => true,
                    'employee' => $employee,
                ]);
            }

            // 6. No match found
            return response()->json([
                'status' => 'success',
                'match' => false,
            ]);

        } catch (\Exception $e) {
            // Catch unexpected errors
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
