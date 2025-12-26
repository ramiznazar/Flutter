<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Giveaway;
use Illuminate\Http\Request;

class GiveawayController extends Controller
{
    public function getGiveaway(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);
        $offset = ($page - 1) * $perPage;

        $giveaways = Giveaway::orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $totalPages = ceil(Giveaway::count() / $perPage);

        return response()->json([
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'data' => $giveaways
        ]);
    }
}
