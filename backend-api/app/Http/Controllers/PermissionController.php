<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
Use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    // Mengambil semua roles
    public function getAllRole(): JsonResponse
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }

    // Mengambil semua permissions
    public function getAllPermission(): JsonResponse
    {
        $permissions = Permission::select('id', 'name')->get();
        return response()->json($permissions);
    }

    // Mengambil role berdasarkan ID
    public function getRoleById($id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        return response()->json(['role' => $role], 200);
    }

    // Mengambil permission berdasarkan ID
    public function getPermissionById($id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['status' => 'error', 'message' => 'Permission not found'], 404);
        }

        return response()->json(['permission' => $permission], 200);
    }

    // Membuat role baru
    public function createRole(Request $request): JsonResponse
    {
        // Validasi data input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        // Periksa apakah role sudah ada
        if (Role::where('name', $validated['name'])->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Role already exists'], 400);
        }

        // Membuat role baru
        $role = Role::create(['name' => $validated['name']]);

        Log::info("Role '{$validated['name']}' created successfully.");
        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

    // Mengedit role yang sudah ada
    public function editRole($id, Request $request): JsonResponse
    {
        // Validasi data input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
        ]);

        // Menemukan role berdasarkan ID
        $role = Role::findOrFail($id);

        if ($role->name == 'Super Admin') {
            return response()->json(['status' => 'error', 'message' => 'Super Admin role cannot be modified'], 400);
        }

        // Periksa apakah nama role baru sudah ada
        if (Role::where('name', $validated['name'])->where('id', '!=', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Role name already exists'], 400);
        }

        // Mengubah nama role jika diberikan
        if (isset($validated['name'])) {
            $role->name = $validated['name'];
        }

        $role->save();

        Log::info("Role '{$role->name}' updated.");
        return response()->json(['message' => 'Role updated successfully', 'role' => $role], 200);
    }

    // Menghapus role
    public function deleteRole($id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        if ($role->name == 'Super Admin') {
            return response()->json(['status' => 'error', 'message' => 'Super Admin role cannot be deleted'], 400);
        }

        // Hapus role
        $role->delete();

        Log::info("Role '{$role->name}' deleted.");
        return response()->json(['message' => 'Role deleted successfully'], 200);
    }

    // Membuat permission baru
    public function createPermission(Request $request): JsonResponse
    {
        // Validasi data input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        // Periksa apakah permission sudah ada
        if (Permission::where('name', $validated['name'])->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Permission already exists'], 400);
        }

        // Membuat permission baru
        $permission = Permission::create(['name' => $validated['name']]);

        Log::info("Permission '{$validated['name']}' created successfully.");
        return response()->json(['message' => 'Permission created successfully', 'permission' => $permission], 201);
    }

    // Mengedit permission yang sudah ada
    public function editPermission($id, Request $request): JsonResponse
    {
        // Validasi data input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
        ]);

        // Menemukan permission berdasarkan ID
        $permission = Permission::findOrFail($id);

        // Periksa apakah nama role baru sudah ada
        if (Permission::where('name', $validated['name'])->where('id', '!=', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Permission name already exists'], 400);
        }

        // Mengubah nama permission jika diberikan
        $permission->name = $validated['name'];
        $permission->save();

        Log::info("Permission '{$permission->name}' updated.");
        return response()->json(['message' => 'Permission updated successfully', 'permission' => $permission], 200);
    }

    // Menghapus permission
    public function deletePermission($id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['status' => 'error', 'message' => 'Permission not found'], 404);
        }

        // Hapus permission
        $permission->delete();

        Log::info("Permission '{$permission->name}' deleted.");
        return response()->json(['message' => 'Permission deleted successfully'], 200);
    }
}
