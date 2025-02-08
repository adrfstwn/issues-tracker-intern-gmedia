<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
Use Illuminate\Http\JsonResponse;

use Exception;

class AuthController extends Controller
{

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Credentials',
                ], 401);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Credentials',
                ], 401);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is inactive. Please contact support.',
                ], 403);
            }

            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not create token',
                ], 500);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error JWT Token',
            ], 500);
        }

        $roles = $user->getRoleNames();
        $permission = $user->getPermissionNames();

        return response()->json([
            
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'statusUser' => $user->status,
                'email' => $user->email,
                'role' => $roles,
                'permissions' => $permission,
            ],
            'token' => $token,
        ], 200);
    }
    public function register(Request $request): JsonResponse 
    {

        $roles = ['admin', 'developer', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $dataValidation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'status' => 'nullable|strng|in:active,inactive',
            'permissions' => 'nullable|array', // Menambahkan validasi untuk permissions
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($dataValidation->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $dataValidation->errors(),
            ], 422);
        }

        try {
            $roles = ['admin', 'developer', 'user'];
            foreach ($roles as $roleName) {
                Role::firstOrCreate(['name' => $roleName]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status ?? 'active',
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            if($request->has('permissions') && !empty($request->permissions)){
                $permissions = Permission::whereIn('name', $request->permissions)->get();
                $user->syncPermissions($permissions);
            }

            $roles = $user->getRoleNames();
            $permissions = $user->getPermissionNames();

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $roles,
                'permissions' => $permissions,
                ],
            ], 201);
        } catch (Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while registering the user',
            ], 500);
        }
    }
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful',
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not log out',
            ], 500);
        }
    }
    public function userProfile(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status,
                'email' => $user->email,
                'role' => $user->getRoleNames(),
                'permissions' => $user->getPermissionNames(),
            ],
        ], 200);
    }
    public function updateProfile(Request $request): JsonResponse
    {
        // Ambil pengguna yang sedang login
        $user = auth()->user();
    
        // Cek apakah pengguna adalah Super Admin dan dilindungi
        if ($user->hasRole('Super Admin') && $user->is_protected) {
            return response()->json([
                'status' => 'error',
                'message' => 'Super Admin profile cannot be edited.',
            ], 403); // Status code 403 for forbidden
        }
    
        // Validasi input
        $dataValidation = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|string', // Required jika password tersedia
            'password' => 'sometimes|string|min:8|confirmed'
        ]);
    
        // Periksa apakah validasi gagal
        if ($dataValidation->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $dataValidation->errors(),
            ], 422); // Kode status 422 untuk unprocessable entity
        }
    
        try {
            // Verifikasi current password jika ingin update password
            if ($request->has('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'The current password is incorrect.',
                    ], 403); // Status code 403 for forbidden
                }
                // Update password
                $user->password = Hash::make($request->password);
            }
    
            // Update name jika tersedia
            if ($request->has('name')) {
                $user->name = $request->name;
            }
    
            // Update email jika tersedia
            if ($request->has('email')) {
                $user->email = $request->email;
            }
    
            // Simpan perubahan
            $user->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user, // Kembalikan data pengguna terbaru
            ], 200);
        } catch (Exception $e) {
            // Catat kesalahan untuk debugging
            Log::error('Profile Update Error: ' . $e->getMessage());
    
            // Kembalikan respons JSON dengan kode status 500
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the profile',
            ], 500);
        }
    }
    public function listAllUsers(): JsonResponse
    {

        $currentUserId = auth()->id();

        $users = User::where('id', '!=', $currentUserId)
        ->whereDoesntHave('roles', function ($query){
            $query->where('name', "Super Admin");
        })
        ->with('roles')
        ->get();

        return response()->json([
            'status' => 'success',
            'users' => $users,
        ], 200);
    }
    public function getUserById($id): JsonResponse
    {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User found',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->getRoleNames(), // Hanya nama role
                'permissions' => $user->getPermissionNames(), // Hanya nama permission
            ],
        ], 200);
    }
    public function editUserById(Request $request, $id): JsonResponse
    {

        $dataValidation = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'role' => 'nullable|string|max:50',
            'password' => 'sometimes|string|min:8|confirmed',
            'status' => 'sometimes|in:active,inactive',
            'permissions' => 'nullable|array', // Menambahkan validasi untuk permissions
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($dataValidation->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $dataValidation->errors(),
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            if ($user->is_protected && $user->hasRole('Super Admin')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Super Admin cannot be updated.',
                ], 403); // Status code 403 for forbidden
            }

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->has('status')) {
                $user->status = $request->status;
            }

            if ($request->has('role')) {
                $user->syncRoles([$request->role]); // Hapus semua role sebelumnya, tambahkan role baru
            }
            
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions); // Sinkronkan izin pengguna
            }

            $user->save();
            $roles = $user->getRoleNames();
            $permissions = $user->getPermissionNames();

            return response()->json([
                'status' => 'success',
                'message' => 'User profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'role' => $roles,
                    'permissions' => $permissions,

                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        } catch (Exception $e) {
            Log::error('User Profile Update Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the user profile',
            ], 500);
        }
    }
    public function deleteUserById($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        if ($user->is_protected && $user->hasRole('Super Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Super Admin cannot be deleted.',
            ], 403);
        }

        $user->delete();

        return response()->json(['status' => 'success', 'message' => 'User deleted successfully'], 200);
    }
}
