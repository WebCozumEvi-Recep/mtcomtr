<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('users.view'), 403, 'Kullanıcıları görüntüleme yetkiniz yok.');
        $query = User::with('customRole');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermission('users.edit'), 403, 'Yeni kullanıcı ekleme yetkiniz yok.');
        $user = new User();
        $roles = Role::all();
        return view('admin.users.form', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('users.edit'), 403, 'Yeni kullanıcı ekleme yetkiniz yok.');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'account_type' => 'required|in:global,custom',
            'custom_role_id' => 'required_if:account_type,custom|nullable|exists:roles,id'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->account_type === 'global' ? \App\Enums\UserRole::Admin : \App\Enums\UserRole::Editor;
        $user->custom_role_id = $request->account_type === 'custom' ? $request->custom_role_id : null;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Kullanıcı oluşturuldu.');
    }

    public function edit(User $user)
    {
        abort_if(!auth()->user()->hasPermission('users.edit'), 403, 'Kullanıcı düzenleme yetkiniz yok.');
        $roles = Role::all();
        return view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        abort_if(!auth()->user()->hasPermission('users.edit'), 403, 'Kullanıcı düzenleme yetkiniz yok.');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'account_type' => 'required|in:global,custom',
            'custom_role_id' => 'required_if:account_type,custom|nullable|exists:roles,id'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->account_type === 'global' ? \App\Enums\UserRole::Admin : \App\Enums\UserRole::Editor;
        $user->custom_role_id = $request->account_type === 'custom' ? $request->custom_role_id : null;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Kullanıcı güncellendi.');
    }

    public function destroy(User $user)
    {
        abort_if(!auth()->user()->hasPermission('users.delete'), 403, 'Kullanıcı silme yetkiniz yok.');
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendinizi silemezsiniz.');
        }

        $userName = $user->name;

        // Create System Alert
        \App\Models\SystemAlert::create([
            'type' => 'danger',
            'title' => 'Kullanıcı Silindi',
            'message' => "'<strong>" . $userName . "</strong>' isimli kullanıcı sistemden kalıcı olarak silindi.",
            'causer_id' => auth()->id(),
            'data' => [
                'type' => 'user',
                'id' => $user->id,
                'name' => $userName
            ]
        ]);

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Kullanıcı silindi.');
    }

    public function toggleFavorite(Request $request)
    {
        $user = auth()->user();
        $favorites = $user->favorites ?? [];
        $newFavorite = [
            'id' => $request->id,
            'title' => $request->title,
            'url' => $request->url,
            'icon' => $request->icon ?? 'star'
        ];

        $foundIndex = -1;
        foreach($favorites as $index => $fav) {
            if($fav['id'] === $newFavorite['id']) {
                $foundIndex = $index;
                break;
            }
        }

        if($foundIndex > -1) {
            array_splice($favorites, $foundIndex, 1);
            $status = 'removed';
        } else {
            $favorites[] = $newFavorite;
            $status = 'added';
        }

        $user->favorites = $favorites;
        $user->save();

        return response()->json([
            'status' => 'success', 
            'favorite_status' => $status, 
            'id' => $newFavorite['id'],
            'data' => $newFavorite
        ]);
    }
}
