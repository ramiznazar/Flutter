<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ShopViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index(Request $request)
    {
        $editId = $request->get('edit_id');
        $editItem = null;
        
        if ($editId) {
            $editItem = Shop::where('ID', $editId)->first();
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

        $shopItems = Shop::orderBy('ID', 'desc')
            ->select('ID', 'Title', 'Image', 'Link', 'Status', 'CreatedAt', 'Description', 'Price')
            ->get();

        return view('admin.shop.index', compact('shopItems', 'editItem'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'redirect_link' => 'required|url',
            'item_image' => 'nullable|url',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $itemId = $request->input('item_id');
        // Convert empty string to 0 for proper comparison
        $itemId = $itemId === '' || $itemId === null ? 0 : (int)$itemId;

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

        if ($itemId > 0) {
            $item = Shop::where('ID', $itemId)->firstOrFail();
            $item->update([
                'Title' => $request->item_name,
                'Link' => $request->redirect_link,
                'Image' => $request->item_image ?? 'https://via.placeholder.com/300',
                'Description' => $request->description ?? '',
                'Price' => $request->price ?? 0,
                'Status' => $request->status === 'active' ? 1 : 0,
            ]);
            $message = 'Shop item updated successfully.';
        } else {
            Shop::create([
                'Title' => $request->item_name,
                'Link' => $request->redirect_link,
                'Image' => $request->item_image ?? 'https://via.placeholder.com/300',
                'Description' => $request->description ?? '',
                'Price' => $request->price ?? 0,
                'Status' => $request->status === 'active' ? 1 : 0,
                'CreatedAt' => now()->format('Y-m-d'),
                'Likes' => 0,
                'isliked' => 0,
            ]);
            $message = 'Shop item created successfully.';
        }

        return redirect()->route('admin.shop.index')
            ->with('message', $message)
            ->with('messageType', 'success');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:shop,ID',
        ]);

        Shop::where('ID', $request->item_id)->firstOrFail()->delete();

        return redirect()->route('admin.shop.index')
            ->with('message', 'Shop item deleted successfully.')
            ->with('messageType', 'success');
    }
}















