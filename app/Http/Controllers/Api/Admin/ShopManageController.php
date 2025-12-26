<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ShopManageController extends Controller
{
    public function index()
    {
        // Check if Description and Price columns exist, if not add them (matching PHP behavior)
        if (!Schema::hasColumn('shop', 'Description')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->text('Description')->nullable()->after('Title');
            });
        }
        if (!Schema::hasColumn('shop', 'Price')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->decimal('Price', 10, 2)->default(0)->nullable()->after('Description');
            });
        }

        $items = Shop::orderBy('ID', 'desc')
            ->select('ID', 'Title', 'Image', 'Link', 'Status', 'CreatedAt', 'Description', 'Price')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->ID ?? $item->id,
                    'item_name' => $item->Title,
                    'description' => $item->Description ?? '',
                    'price' => $item->Price ?? 0,
                    'redirect_link' => $item->Link,
                    'item_image' => $item->Image,
                    'status' => $item->Status == 1 ? 'active' : 'inactive',
                    'created_at' => $item->CreatedAt
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string',
            'redirect_link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Item name and redirect link are required.'
            ], 400);
        }

        // Check if Description and Price columns exist, if not add them (matching PHP behavior)
        if (!Schema::hasColumn('shop', 'Description')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->text('Description')->nullable()->after('Title');
            });
        }
        if (!Schema::hasColumn('shop', 'Price')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->decimal('Price', 10, 2)->default(0)->nullable()->after('Description');
            });
        }

        $shop = Shop::create([
            'Title' => $request->item_name,
            'Link' => $request->redirect_link,
            'Image' => $request->item_image ?? 'https://via.placeholder.com/300',
            'Description' => $request->description ?? '',
            'Price' => $request->price ?? 0,
            'Status' => ($request->status ?? 'active') === 'active' ? 1 : 0,
            'CreatedAt' => now()->format('Y-m-d'),
            'Likes' => 0,
            'isliked' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop item created successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string',
            'redirect_link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID, item name, and redirect link are required.'
            ], 400);
        }

        $shop = Shop::where('ID', $id)->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop item not found'
            ], 404);
        }

        // Check if Description and Price columns exist, if not add them (matching PHP behavior)
        if (!Schema::hasColumn('shop', 'Description')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->text('Description')->nullable()->after('Title');
            });
        }
        if (!Schema::hasColumn('shop', 'Price')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->decimal('Price', 10, 2)->default(0)->nullable()->after('Description');
            });
        }

        $updateData = [
            'Title' => $request->item_name,
            'Link' => $request->redirect_link,
            'Image' => $request->item_image ?? $shop->Image,
            'Status' => ($request->status ?? 'active') === 'active' ? 1 : 0
        ];

        // Only update Description and Price if columns exist
        if (Schema::hasColumn('shop', 'Description')) {
            $updateData['Description'] = $request->description ?? $shop->Description;
        }
        if (Schema::hasColumn('shop', 'Price')) {
            $updateData['Price'] = $request->price ?? $shop->Price;
        }

        $shop->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Shop item updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $shop = Shop::where('ID', $id)->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop item not found'
            ], 404);
        }

        $shop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shop item deleted successfully.'
        ]);
    }
}
