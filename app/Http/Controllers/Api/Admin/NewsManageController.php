<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NewsManageController extends Controller
{
    public function index()
    {
        // Check if Link column exists (matching PHP behavior)
        $hasLink = Schema::hasColumn('news', 'Link');
        $selectFields = $hasLink 
            ? ['id', 'Title', 'Description', 'Image', 'Link', 'Status', 'CreatedAt']
            : ['id', 'Title', 'Description', 'Image', 'Status', 'CreatedAt'];
        
        $news = News::orderBy('id', 'desc')
            ->select($selectFields)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->Title,
                    'content' => $item->Description,
                    'redirect_link' => $item->Link ?? '',
                    'image' => $item->Image,
                    'status' => $item->Status == 1 ? 'active' : 'inactive',
                    'created_at' => $item->CreatedAt
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'news_title' => 'required|string',
            'news_content' => 'required|string',
            'redirect_link' => 'required|string',
            'image' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Title, content, and redirect link are required.'
            ], 400);
        }

        // Check if Link column exists, if not add it (matching PHP behavior)
        if (!Schema::hasColumn('news', 'Link')) {
            Schema::table('news', function (Blueprint $table) {
                $table->text('Link')->nullable()->default(null)->after('Description');
            });
        }

        $news = News::create([
            'Title' => $request->news_title,
            'Description' => $request->news_content,
            'Link' => $request->redirect_link,
            'Image' => $request->image ?? '',
            'Status' => ($request->status ?? 'active') === 'active' ? 1 : 0,
            'CreatedAt' => now()->format('Y-m-d'),
            'AdShow' => 0,
            'RAdShow' => 0,
            'Likes' => 0,
            'isliked' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'News created successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'news_title' => 'required|string',
            'news_content' => 'required|string',
            'redirect_link' => 'required|string',
            'image' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID, title, content, and redirect link are required.'
            ], 400);
        }

        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ], 404);
        }

        // Check if Link column exists, if not add it (matching PHP behavior)
        if (!Schema::hasColumn('news', 'Link')) {
            Schema::table('news', function (Blueprint $table) {
                $table->text('Link')->nullable()->default(null)->after('Description');
            });
        }

        $news->update([
            'Title' => $request->news_title,
            'Description' => $request->news_content,
            'Link' => $request->redirect_link,
            'Image' => $request->image ?? $news->Image,
            'Status' => ($request->status ?? 'active') === 'active' ? 1 : 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'News updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ], 404);
        }

        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'News deleted successfully.'
        ]);
    }
}
