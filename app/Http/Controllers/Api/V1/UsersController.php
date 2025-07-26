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

class UsersController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::handle(function () {
            
            return User::all();
        });
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            
            if ($id !== Auth::user()->id || Auth::user()->is_admin) {
                throw new Exception('You cannot view other users');
            }

            $user = User::findOrFail($id);

            return $user;
        });
    }

    public function update(Request $request): JsonResponse
    {
        return ApiResponse::handle(function () use ($request) {

            $validator = Validator::make($request->all(), [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
                'name' => ['bail', 'nullable', 'string', 'max:255'],
                'email' => ['bail', 'nullable', 'string', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($request->input('id') !== Auth::user()->id && !Auth::user()->is_admin) {
                throw new Exception('You cannot update other users');
            }

            if (!$request->has('name') && !$request->has('email')) {
                throw new Exception('No fields to update');
            }

            $user = User::find($request->input('id'));
            
            if ($request->has('name')) {
                $user->name = $request->input('name');
            }

            if ($request->has('email')) {
                $user->email = $request->input('email');
            }

            $user->save();

            return 'User updated successfully';
        });
    }

    public function softDestroy(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($id !== Auth::user()->id && !Auth::user()->is_admin) {
                throw new Exception('You cannot delete other users');
            }

            $user = User::findOrFail($id);
            $user->delete();

            return 'User soft-deleted successfully';
        });
    }

    public function restore(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($id !== Auth::user()->id) {
                throw new Exception('You cannot restore other users');
            }

            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            return 'User restored successfully';
        });
    }

    public function destroy(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($id !== Auth::user()->id && !Auth::user()->is_admin) {
                throw new Exception('You cannot delete other users');
            }

            $user = User::withTrashed()->findOrFail($id);
            $user->forceDelete();

            return 'User permanently deleted successfully';
        });
    }
}
