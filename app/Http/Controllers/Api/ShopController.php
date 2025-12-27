<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\ShopView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function getAllShops(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'page' => 'sometimes|integer|min:1',
            'perPage' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        $offset = ($page - 1) * $perPage;

        // Get shops with view status
        $shops = Shop::leftJoin('shop_views', function($join) use ($user) {
                $join->on('shop.id', '=', 'shop_views.Shop_ID')
                     ->where('shop_views.User_ID', '=', $user->id);
            })
            ->select('shop.*')
            ->selectRaw('IF(shop_views.Shop_ID IS NOT NULL, 1, 0) AS isliked')
            ->orderBy('shop.id', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $shopData = [];
        foreach ($shops as $item) {
            // Get top 3 viewers
            $viewers = ShopView::where('Shop_ID', $item->id)
                ->join('users', 'shop_views.User_ID', '=', 'users.id')
                ->select('users.ban_reason')
                ->orderBy('shop_views.CreatedAt', 'desc')
                ->limit(3)
                ->pluck('ban_reason')
                ->toArray();

            $shopData[] = [
                'id' => $item->id,
                'image' => $item->Image,
                'title' => $item->Title,
                'webLink' => $item->Link,
                'createdAt' => $item->CreatedAt,
                'views' => $item->Likes,
                'isViewed' => (bool) $item->isliked,
                'lastViewers' => $viewers
            ];
        }

        $totalPages = ceil(Shop::count() / $perPage);

        return response()->json([
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'data' => $shopData,
        ]);
    }

    public function setShopView(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'shop_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $shop = Shop::find($request->shop_id);

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop item not found'
            ], 404);
        }

        // Check if already viewed
        $existing = ShopView::where('Shop_ID', $request->shop_id)
            ->where('User_ID', $user->id)
            ->first();

        if (!$existing) {
            ShopView::create([
                'Shop_ID' => $request->shop_id,
                'User_ID' => $user->id,
                'CreatedAt' => now()->format('Y-m-d H:i:s')
            ]);

            $shop->increment('Likes');
        }

        return response()->json([
            'success' => true,
            'message' => 'Shop view recorded'
        ]);
    }

    public function trackGiftcard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'shop_id' => 'required|integer',
            'action' => 'sometimes|string', // e.g., 'viewed', 'clicked', 'purchased'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $shop = Shop::find($request->shop_id);

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Giftcard/Shop item not found'
            ], 404);
        }

        $action = $request->input('action', 'viewed');

        // Track giftcard interaction
        // Check if already viewed (to avoid duplicate tracking for views)
        $existing = ShopView::where('Shop_ID', $request->shop_id)
            ->where('User_ID', $user->id)
            ->first();

        if (!$existing && $action === 'viewed') {
            ShopView::create([
                'Shop_ID' => $request->shop_id,
                'User_ID' => $user->id,
                'CreatedAt' => now()->format('Y-m-d H:i:s')
            ]);

            $shop->increment('Likes');
        }

        return response()->json([
            'success' => true,
            'message' => 'Giftcard interaction tracked successfully',
            'action' => $action,
            'shop_id' => $request->shop_id
        ]);
    }
}
