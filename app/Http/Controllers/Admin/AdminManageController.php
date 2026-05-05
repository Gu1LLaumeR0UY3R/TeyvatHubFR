<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminManageController extends Controller
{
    private const MAX_IMAGE_BYTES = 500 * 1024;

    public function index(): View
    {
        $admins = Admin::orderBy('pseudo_admin')->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.admins.create', [
            'allPermissions' => Admin::ALL_PERMISSIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pseudo_admin'        => ['required', 'string', 'max:100', 'unique:admin,pseudo_admin'],
            'email_admin'         => ['required', 'email', 'max:255', 'unique:admin,email_admin'],
            'mot_de_passe_admin'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'                => ['required', 'in:super_admin,moderateur'],
            'permissions'         => ['nullable', 'array'],
            'permissions.*'       => ['in:' . implode(',', Admin::ALL_PERMISSIONS)],
            'photo_profil'        => ['nullable', 'string'],
            'photo_profil_url'    => ['nullable', 'url', 'max:1000'],
            'banniere_admin'      => ['nullable', 'string'],
            'banniere_admin_url'  => ['nullable', 'url', 'max:1000'],
        ]);

        $data['mot_de_passe_admin'] = Hash::make($data['mot_de_passe_admin']);
        $data['legacy_permissions'] = $data['role'] === 'super_admin'
            ? Admin::ALL_PERMISSIONS
            : ($data['permissions'] ?? []);
        unset($data['permissions']);

        $data['photo_profil'] = $this->resolveImageInput(
            $data['photo_profil_url'] ?? null,
            $data['photo_profil'] ?? null,
            'admins/profile',
            'photo_profil'
        );
        $data['banniere_admin'] = $this->resolveImageInput(
            $data['banniere_admin_url'] ?? null,
            $data['banniere_admin'] ?? null,
            'admins/banners',
            'banniere_admin'
        );

        unset($data['photo_profil_url'], $data['banniere_admin_url']);

        $admin = Admin::create($data);
        $this->syncAdminRolePermissions($admin, $data['role'], $data['permissions']);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin créé avec succès.');
    }

    public function edit(Admin $admin): View
    {
        return view('admin.admins.edit', [
            'adminUser'      => $admin,
            'allPermissions' => Admin::ALL_PERMISSIONS,
        ]);
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $data = $request->validate([
            'pseudo_admin'       => ['required', 'string', 'max:100', 'unique:admin,pseudo_admin,' . $admin->id_admin . ',id_admin'],
            'email_admin'        => ['required', 'email', 'max:255', 'unique:admin,email_admin,' . $admin->id_admin . ',id_admin'],
            'mot_de_passe_admin' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'               => ['required', 'in:super_admin,moderateur'],
            'permissions'        => ['nullable', 'array'],
            'permissions.*'      => ['in:' . implode(',', Admin::ALL_PERMISSIONS)],
            'photo_profil'       => ['nullable', 'string'],
            'photo_profil_url'   => ['nullable', 'url', 'max:1000'],
            'banniere_admin'     => ['nullable', 'string'],
            'banniere_admin_url' => ['nullable', 'url', 'max:1000'],
        ]);

        if (filled($data['mot_de_passe_admin'] ?? null)) {
            $data['mot_de_passe_admin'] = Hash::make($data['mot_de_passe_admin']);
        } else {
            unset($data['mot_de_passe_admin']);
        }

        $data['legacy_permissions'] = $data['role'] === 'super_admin'
            ? Admin::ALL_PERMISSIONS
            : ($data['permissions'] ?? []);
        unset($data['permissions']);

        $newPhoto = $this->resolveImageInput(
            $data['photo_profil_url'] ?? null,
            $data['photo_profil'] ?? null,
            'admins/profile',
            'photo_profil'
        );
        if ($newPhoto !== null && $newPhoto !== $admin->photo_profil) {
            $this->deleteStoredImageIfLocal($admin->photo_profil);
            $data['photo_profil'] = $newPhoto;
        } else {
            unset($data['photo_profil']);
        }

        $newBanner = $this->resolveImageInput(
            $data['banniere_admin_url'] ?? null,
            $data['banniere_admin'] ?? null,
            'admins/banners',
            'banniere_admin'
        );
        if ($newBanner !== null && $newBanner !== $admin->banniere_admin) {
            $this->deleteStoredImageIfLocal($admin->banniere_admin);
            $data['banniere_admin'] = $newBanner;
        } else {
            unset($data['banniere_admin']);
        }

        unset($data['photo_profil_url'], $data['banniere_admin_url']);

        $admin->update($data);
        $this->syncAdminRolePermissions($admin, $data['role'], $data['permissions']);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin mis à jour.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        // Prevent self-deletion.
        if ($admin->id_admin === session('admin_id')) {
            return back()->with('error', 'Tu ne peux pas supprimer ton propre compte.');
        }

        // Supprimer les images
        if ($admin->photo_profil && Storage::exists("public/{$admin->photo_profil}")) {
            Storage::delete("public/{$admin->photo_profil}");
        }
        if ($admin->banniere_admin && Storage::exists("public/{$admin->banniere_admin}")) {
            Storage::delete("public/{$admin->banniere_admin}");
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin supprimé.');
    }

    private function resolveImageInput(?string $urlInput, ?string $payloadInput, string $directory, string $fieldName): ?string
    {
        $url = trim((string) $urlInput);
        if ($url !== '') {
            return $url;
        }

        $payload = trim((string) $payloadInput);
        if ($payload === '') {
            return null;
        }

        return $this->storeDataUrlImage($payload, $directory, $fieldName);
    }

    private function storeDataUrlImage(string $dataUrl, string $directory, string $fieldName): string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,(.+)$/i', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                $fieldName => 'Le format de l\'image est invalide.',
            ]);
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            throw ValidationException::withMessages([
                $fieldName => 'Impossible de lire l\'image envoyee.',
            ]);
        }

        if (strlen($binary) > self::MAX_IMAGE_BYTES) {
            throw ValidationException::withMessages([
                $fieldName => 'Image trop lourde: maximum 500 Ko.',
            ]);
        }

        $extension = strtolower($matches[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $path = $directory . '/' . Str::uuid()->toString() . '.' . $extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function syncAdminRolePermissions(Admin $admin, string $role, array $permissions): void
    {
        $guardName = 'web';
        $roleRecord = Role::findOrCreate($role, $guardName);

        $permissionRecords = collect($permissions)
            ->filter()
            ->map(fn(string $permission) => Permission::findOrCreate($permission, $guardName))
            ->all();

        if ($role === 'super_admin' || $role === 'superadmin') {
            $permissionRecords = Permission::where('guard_name', $guardName)->get()->all();
        }

        $admin->syncRoles([$roleRecord]);
        $admin->syncPermissions($permissionRecords);
    }

    private function deleteStoredImageIfLocal(?string $imagePath): void
    {
        if (!$imagePath || filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
