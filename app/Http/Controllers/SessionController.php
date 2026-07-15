<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function __construct()
    {
        // Only the super admin can see who is logged in.
        $this->middleware('role:super_admin');
    }

    public function index(Request $request)
    {
        $lifetime = (int) config('session.lifetime', 120); // minutes
        $activeSince = Carbon::now()->subMinutes($lifetime)->getTimestamp();

        $rows = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $activeSince)
            ->orderByDesc('last_activity')
            ->get();

        $users = User::with(['roles', 'clinic'])
            ->whereIn('id', $rows->pluck('user_id')->unique())
            ->get()->keyBy('id');

        $sessions = $rows->map(function ($s) use ($users, $request) {
            $user = $users->get($s->user_id);

            return [
                'id' => $s->id,
                'user' => $user,
                'ip' => $s->ip_address,
                'device' => device_from_agent($s->user_agent),
                'user_agent' => $s->user_agent,
                'last_active' => Carbon::createFromTimestamp($s->last_activity),
                'is_current' => $s->id === $request->session()->getId(),
            ];
        })->filter(fn ($s) => $s['user']); // drop orphaned sessions

        return view('sessions.index', compact('sessions', 'lifetime'));
    }

    /** Force-log-out a device by removing its session. */
    public function destroy(Request $request, string $id)
    {
        // Never let the admin revoke their own current session here.
        abort_if($id === $request->session()->getId(), 403, 'You cannot revoke your own current session.');

        DB::table('sessions')->where('id', $id)->delete();

        return back()->with('status', 'Session revoked — that device will be signed out.');
    }
}
