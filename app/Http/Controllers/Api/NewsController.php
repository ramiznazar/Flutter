<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\News;
use App\Models\NewsLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function getAllNews(Request $request)
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

        // Optimize: Select only id field for user lookup
        $user = User::where('email', $request->email)
            ->select('id')
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('perPage', 10);
        $offset = max(0, ($page - 1) * $perPage);

        // Optimize: Select only needed columns from news
        $news = News::leftJoin('news_like', function($join) use ($user) {
                $join->on('news.id', '=', 'news_like.News_ID')
                     ->where('news_like.User_ID', '=', $user->id);
            })
            ->select('news.id', 'news.Image', 'news.Title', 'news.CreatedAt', 'news.Likes')
            ->selectRaw('IF(news_like.News_ID IS NOT NULL, 1, 0) AS isliked')
            ->orderBy('news.id', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Optimize: Get all news IDs in one batch
        $newsIds = $news->pluck('id')->toArray();
        
        // Optimize: Get top 3 likers for ALL news items in a single query (eliminates N+1)
        $allLikers = [];
        if (!empty($newsIds)) {
            // Get all likers for these news items, then group and take top 3 per news
            $likersData = \Illuminate\Support\Facades\DB::table('news_like')
                ->join('users', 'news_like.User_ID', '=', 'users.id')
                ->whereIn('news_like.News_ID', $newsIds)
                ->select('news_like.News_ID', 'users.ban_reason', 'news_like.CreatedAt')
                ->orderBy('news_like.News_ID')
                ->orderBy('news_like.CreatedAt', 'desc')
                ->get();
            
            // Group by News_ID and take top 3 per group
            $grouped = $likersData->groupBy('News_ID');
            foreach ($grouped as $newsId => $group) {
                $allLikers[$newsId] = $group->take(3)->pluck('ban_reason')->toArray();
            }
        }

        // Cache total count (static per PHP process; news table changes infrequently)
        static $cachedTotalCount = null;
        if ($cachedTotalCount === null) {
            $cachedTotalCount = News::count();
        }
        $totalCount = $cachedTotalCount;

        $newsData = [];
        foreach ($news as $item) {
            $newsData[] = [
                'id' => $item->id,
                'image' => $item->Image,
                'title' => $item->Title,
                'webLink' => "https://crutox.com/" . $item->id,
                'createdAt' => $item->CreatedAt,
                'views' => $item->Likes,
                'isViewed' => (bool) $item->isliked,
                'lastViewers' => $allLikers[$item->id] ?? []
            ];
        }

        $totalPages = ceil($totalCount / $perPage);

        return response()->json([
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'data' => $newsData,
        ]);
    }

    public function getNews(Request $request)
    {
        return $this->getAllNews($request);
    }

    public function addNews(Request $request)
    {
        // Admin only - will be handled by admin controller
        return response()->json([
            'success' => false,
            'message' => 'Use admin endpoint'
        ], 403);
    }

    public function deleteNews(Request $request)
    {
        // Admin only
        return response()->json([
            'success' => false,
            'message' => 'Use admin endpoint'
        ], 403);
    }

    public function likeNews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'news_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        // Optimize: Select only id field
        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->select('id')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $news = News::find($request->news_id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ], 404);
        }

        // Check if already liked
        $existing = NewsLike::where('News_ID', $request->news_id)
            ->where('User_ID', $user->id)
            ->first();

        if ($existing) {
            // Unlike
            $existing->delete();
            $news->decrement('Likes');
            
            return response()->json([
                'success' => true,
                'message' => 'News unliked',
                'liked' => false
            ]);
        }

        // Like
        NewsLike::create([
            'News_ID' => $request->news_id,
            'User_ID' => $user->id,
            'CreatedAt' => now()->format('Y-m-d H:i:s')
        ]);

        $news->increment('Likes');

        return response()->json([
            'success' => true,
            'message' => 'News liked',
            'liked' => true
        ]);
    }

    public function setNewsView(Request $request)
    {
        // Similar to likeNews but for tracking views
        return $this->likeNews($request);
    }
}
