<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NewsViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index(Request $request)
    {
        $editId = $request->get('edit_id');
        $editNews = null;
        
        if ($editId) {
            $editNews = News::where('ID', $editId)->first();
        }

        // Check if Link column exists (matching PHP behavior)
        $hasLink = \Illuminate\Support\Facades\Schema::hasColumn('news', 'Link');
        $selectFields = $hasLink 
            ? ['ID', 'Title', 'Description', 'Image', 'Link', 'Status', 'CreatedAt']
            : ['ID', 'Title', 'Description', 'Image', 'Status', 'CreatedAt'];
        
        $newsItems = News::orderBy('ID', 'desc')
            ->select($selectFields)
            ->get();

        return view('admin.news.index', compact('newsItems', 'editNews'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'news_title' => 'required|string|max:255',
            'news_content' => 'required|string',
            'redirect_link' => 'required|url',
            'image' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        $newsId = $request->input('news_id');
        // Convert empty string to 0 for proper comparison
        $newsId = $newsId === '' || $newsId === null ? 0 : (int)$newsId;

        // Check if Link column exists, if not add it (matching PHP behavior)
        if (!\Illuminate\Support\Facades\Schema::hasColumn('news', 'Link')) {
            \Illuminate\Support\Facades\Schema::table('news', function (Blueprint $table) {
                $table->text('Link')->nullable()->default(null)->after('Description');
            });
        }

        if ($newsId > 0) {
            $news = News::where('ID', $newsId)->firstOrFail();
            $news->update([
                'Title' => $request->news_title,
                'Description' => $request->news_content,
                'Link' => $request->redirect_link,
                'Image' => $request->image ?? '',
                'Status' => $request->status === 'active' ? 1 : 0,
            ]);
            $message = 'News updated successfully.';
        } else {
            News::create([
                'Title' => $request->news_title,
                'Description' => $request->news_content,
                'Link' => $request->redirect_link,
                'Image' => $request->image ?? '',
                'Status' => $request->status === 'active' ? 1 : 0,
                'CreatedAt' => now()->format('Y-m-d'),
                'AdShow' => 0,
                'RAdShow' => 0,
                'Likes' => 0,
                'isliked' => 0,
            ]);
            $message = 'News created successfully.';
        }

        return redirect()->route('admin.news.index')
            ->with('message', $message)
            ->with('messageType', 'success');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'news_id' => 'required|integer|exists:news,ID',
        ]);

        News::where('ID', $request->news_id)->firstOrFail()->delete();

        return redirect()->route('admin.news.index')
            ->with('message', 'News deleted successfully.')
            ->with('messageType', 'success');
    }
}















