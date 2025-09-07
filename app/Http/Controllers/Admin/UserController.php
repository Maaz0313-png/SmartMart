<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display users list.
     */
    public function index(Request $request): Response
    {
        $query = User::with('roles:name')
            ->withCount(['orders', 'subscriptions'])
            ->latest();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::all(['id', 'name']),
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show user details.
     */
    public function show(User $user): Response
    {
        $user->load([
            'roles:name',
            'orders' => fn($query) => $query->latest()->take(10),
            'subscriptions.plan',
            'products' => fn($query) => $query->take(10),
        ]);

        $userStats = [
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'orders_count' => $user->orders()->count(),
            'subscriptions_count' => $user->subscriptions()->count(),
            'products_sold' => $user->products()->count(),
            'last_login' => $user->last_login_at?->format('M j, Y g:i A'),
            'member_since' => $user->created_at->format('M j, Y'),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'userStats' => $userStats,
        ]);
    }

    /**
     * Show create user form.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Store new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? true,
            'email_verified_at' => $validated['email_verified'] ? now() : null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully!');
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user): Response
    {
        $user->load('roles:name');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
            'password' => 'nullable|confirmed|min:8',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if ($validated['password']) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($validated['email_verified'] && !$user->hasVerifiedEmail()) {
            $updateData['email_verified_at'] = now();
        } elseif (!$validated['email_verified'] && $user->hasVerifiedEmail()) {
            $updateData['email_verified_at'] = null;
        }

        $user->update($updateData);

        // Update role
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Bulk actions for users.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,verify_email,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $request->user_ids);

        // Prevent bulk actions on current user
        if (in_array($request->action, ['deactivate', 'delete']) && in_array(auth()->id(), $request->user_ids)) {
            return back()->withErrors(['action' => 'You cannot perform this action on your own account.']);
        }

        switch ($request->action) {
            case 'activate':
                $users->update(['is_active' => true]);
                $message = 'Users activated successfully!';
                break;
            case 'deactivate':
                $users->update(['is_active' => false]);
                $message = 'Users deactivated successfully!';
                break;
            case 'verify_email':
                $users->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
                $message = 'Users email verified successfully!';
                break;
            case 'delete':
                $users->delete();
                $message = 'Users deleted successfully!';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Impersonate user (for support purposes).
     */
    public function impersonate(User $user)
    {
        if ($user->hasRole('admin')) {
            return back()->withErrors(['user' => 'Cannot impersonate admin users.']);
        }

        session(['impersonating' => auth()->id()]);
        \Auth::login($user);

        return redirect()->route('dashboard')
            ->with('info', 'You are now impersonating ' . $user->name);
    }

    /**
     * Stop impersonating.
     */
    public function stopImpersonating()
    {
        if (!session()->has('impersonating')) {
            return redirect()->route('dashboard');
        }

        $adminId = session()->pull('impersonating');
        \Auth::loginUsingId($adminId);

        return redirect()->route('admin.dashboard')
            ->with('info', 'Stopped impersonating user.');
    }

    /**
     * Export users data.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'filters' => 'nullable|array',
        ]);

        // This would use Laravel Excel to export users
        // return Excel::download(new UsersExport($request->filters), 'users.' . $request->format);
        
        return back()->with('info', 'User export feature will be available soon.');
    }
}