<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store or update a push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);

        $endpoint = $request->endpoint;
        $key = $request->keys['p256dh'];
        $token = $request->keys['auth'];

        $request->user()->updatePushSubscription($endpoint, $key, $token);

        return response()->json([
            'success' => true,
            'message' => 'Subscription saved successfully.'
        ]);
    }

    /**
     * Delete a push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required'
        ]);

        $request->user()->deletePushSubscription($request->endpoint);

        return response()->json([
            'success' => true,
            'message' => 'Subscription deleted successfully.'
        ]);
    }

    /**
     * Display a listing of push subscriptions for Admin/Super Admin under Utilities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = \DB::table('push_subscriptions')
            ->join('users', 'push_subscriptions.subscribable_id', '=', 'users.id')
            ->leftJoin('users_karyawan', 'users.id', '=', 'users_karyawan.id_user')
            ->leftJoin('karyawan', 'users_karyawan.nik', '=', 'karyawan.nik')
            ->select(
                'push_subscriptions.*',
                'users.name as user_name',
                'users.username as username',
                'karyawan.nama_karyawan',
                'karyawan.nik_show'
            );

        if (!empty($request->cari)) {
            $query->where(function ($q) use ($request) {
                $q->where('users.name', 'like', '%' . $request->cari . '%')
                  ->orWhere('users.username', 'like', '%' . $request->cari . '%')
                  ->orWhere('karyawan.nama_karyawan', 'like', '%' . $request->cari . '%');
            });
        }

        $subscriptions = $query->orderBy('push_subscriptions.created_at', 'desc')->paginate(15);

        return view('utilities.push_subscription.index', compact('subscriptions'));
    }

    /**
     * Delete a push subscription by ID (from admin panel).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAdmin($id)
    {
        \DB::table('push_subscriptions')->where('id', $id)->delete();
        return redirect()->back()->with(messageSuccess('Subscription berhasil dihapus'));
    }

    /**
     * Send a test push notification to a subscription's owner.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendTestNotification($id)
    {
        $subscription = \DB::table('push_subscriptions')->where('id', $id)->first();
        if (!$subscription) {
            return redirect()->back()->with(messageError('Subscription tidak ditemukan'));
        }

        $user = \App\Models\User::find($subscription->subscribable_id);
        if (!$user) {
            return redirect()->back()->with(messageError('Pengguna tidak ditemukan'));
        }

        try {
            $user->notify(new \App\Notifications\TestPushNotification());
            return redirect()->back()->with(messageSuccess('Notifikasi uji coba berhasil dikirim'));
        } catch (\Exception $e) {
            return redirect()->back()->with(messageError('Gagal mengirim notifikasi: ' . $e->getMessage()));
        }
    }
}
