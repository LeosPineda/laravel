<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\WelcomeVendorNotification;
use App\Notifications\VendorActivatedNotification;
use App\Notifications\VendorDeactivatedNotification;
use App\Notifications\VendorCredentialUpdatedNotification;
use App\Notifications\VendorDeletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index()
    {
        // TODO: Add withSum('orders as total_revenue', 'total') when Order model is created
        $vendors = Vendor::with('user:id,name,email,is_active')
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('superadmin/Vendors/Index', [
            'vendors' => $vendors,
        ]);
    }

    public function create()
    {
        return Inertia::render('superadmin/Vendors/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'brand_name' => 'required|string|max:255',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'vendor',
                'is_active' => true,
            ]);

            $vendorData = [
                'user_id' => $user->id,
                'brand_name' => $validated['brand_name'],
                'is_active' => true,
            ];

            // Handle logo upload
            if ($request->hasFile('brand_logo')) {
                $path = $request->file('brand_logo')->store('vendor', 'public');
                $vendorData['brand_logo'] = $path;
            }

            Vendor::create($vendorData);

            return $user;
        });

        // Send welcome notification after database work (fast)
        $user->notify(new WelcomeVendorNotification());

        return redirect()->route('superadmin.vendors.index')
            ->with('success', 'Vendor created successfully. Welcome notification sent.');
    }

    public function edit(Vendor $vendor)
    {
        return Inertia::render('superadmin/Vendors/Edit', [
            'vendor' => $vendor->load('user:id,name'),
        ]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'brand_name' => 'required|string|max:255',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check if password reuse is prevented (across all roles)
        if (! empty($validated['password'])) {
            // Check if new password is different from current user's password
            if (Hash::check($validated['password'], $vendor->user->password)) {
                return redirect()->back()
                    ->withErrors(['password' => 'New password must be different from current password.']);
            }

            // Check if password is used by any other user
            $otherUsers = User::where('id', '!=', $vendor->user->id)->get();
            foreach ($otherUsers as $otherUser) {
                if (Hash::check($validated['password'], $otherUser->password)) {
                    return redirect()->back()
                        ->withErrors(['password' => 'This password is already used by another user.']);
                }
            }
        }

        $updatedFields = [];

        DB::transaction(function () use ($validated, $request, $vendor, &$updatedFields) {
            // Update user
            $userData = ['name' => $validated['name']];

            // Check if name was updated
            if ($vendor->user->name !== $validated['name']) {
                $updatedFields[] = 'name';
            }

            // Only update password if provided and different from current
            if (! empty($validated['password'])) {
                // Check if new password is different from current
                if (! Hash::check($validated['password'], $vendor->user->password)) {
                    $userData['password'] = Hash::make($validated['password']);
                    $updatedFields[] = 'password';
                }
            }

            $vendor->user->update($userData);

            // Update vendor
            $vendorData = ['brand_name' => $validated['brand_name']];

            // Check if brand name was updated
            if ($vendor->brand_name !== $validated['brand_name']) {
                $updatedFields[] = 'brand_name';
            }

            // Handle logo upload
            if ($request->hasFile('brand_logo')) {
                // Delete old logo if exists
                if ($vendor->brand_logo) {
                    Storage::disk('public')->delete($vendor->brand_logo);
                }
                $path = $request->file('brand_logo')->store('vendor', 'public');
                $vendorData['brand_logo'] = $path;
                $updatedFields[] = 'brand_logo';
            }

            $vendor->update($vendorData);
        });

        // Send notification after database work (fast)
        if (! empty($updatedFields)) {
            $vendor->user->notify(new VendorCredentialUpdatedNotification($updatedFields, $validated['password'] ?? null));
        }

        return redirect()->route('superadmin.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function toggleActive(Vendor $vendor)
    {
        $newStatus = ! $vendor->is_active;

        // Update database first (fast)
        $vendor->update(['is_active' => $newStatus]);
        $vendor->user->update(['is_active' => $newStatus]);

        $status = $newStatus ? 'activated' : 'deactivated';

        // Send email notification after database update (fast)
        if ($newStatus) {
            $vendor->user->notify(new VendorActivatedNotification);
        } else {
            $vendor->user->notify(new VendorDeactivatedNotification);
        }

        return redirect()->route('superadmin.vendors.index')
            ->with('success', "Vendor {$status} successfully.");
    }

    public function destroy(Vendor $vendor)
    {
        $user = $vendor->user;
        $vendorName = $vendor->brand_name;
        $userId = $user->id;

        try {
            // Send deletion notification BEFORE user deletion (critical fix)
            $user->notify(new VendorDeletedNotification($vendorName));

            DB::transaction(function () use ($vendor, $user, $userId) {
                // Delete vendor first (due to foreign key constraints)
                $vendor->delete();

                // Delete user
                $user->delete();

                // Invalidate sessions
                DB::table('sessions')
                    ->where('user_id', $userId)
                    ->delete();
            });

            return redirect()->route('superadmin.vendors.index')
                ->with('success', 'Vendor deleted successfully. Deletion notification sent.');

        } catch (\Exception $e) {
            return redirect()->route('superadmin.vendors.index')
                ->with('error', 'Failed to delete vendor. Please try again.');
        }
    }
}
