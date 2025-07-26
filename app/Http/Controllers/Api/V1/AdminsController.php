<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminsController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::handle(function () {
            
            if (!Auth::user()->is_admin) {
                throw new Exception('You do not have permission to perform this action');
            }

            $admins = User::where('is_admin', true)->get();

            return $admins;
        });
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($request, $id) {

            $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
                'is_admin' => ['bail', 'required', 'boolean']
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if (!Auth::user()->is_admin) {
                throw new Exception('You do not have permission to perform this action');
            }

            $admin = User::findOrFail($request->id);
            $admin->is_admin = $request->is_admin;
            $admin->save();

            return 'Admin status updated successfully';
        });
    }
}
