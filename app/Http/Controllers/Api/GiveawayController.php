<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Giveaway;
use Illuminate\Http\Request;

class GiveawayController extends Controller
{
    public function getGiveaway(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('perPage', 10);
        $offset = max(0, ($page - 1) * $perPage);

        $totalCount = Giveaway::count();
        $giveaways = Giveaway::orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $totalPages = $perPage > 0 ? (int) ceil($totalCount / $perPage) : 0;

        return response()->json([
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'data' => $giveaways
        ]);
    }
}
