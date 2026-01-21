<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserBooster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GiveBoosterToAllUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $boosterType;
    public $expiresAt;
    public $timeout = 600; // 10 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct(string $boosterType, Carbon $expiresAt)
    {
        $this->boosterType = $boosterType;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failedCount = 0;
        $chunkSize = 500; // Increased chunk size for faster processing (500 users at a time)
        $now = Carbon::now();

        // Process users in reverse order (highest ID to lowest) in chunks to avoid memory issues
        User::where('account_status', 'active')
            ->orderBy('id', 'DESC') // Process from highest ID to lowest
            ->chunk($chunkSize, function ($users) use (&$successCount, &$failedCount, $now) {
                $userIds = [];
                foreach ($users as $user) {
                    $userIds[] = $user->id;
                }
                
                try {
                    DB::beginTransaction();
                    
                    // Bulk deactivate ALL active boosters for all users in this chunk
                    UserBooster::whereIn('user_id', $userIds)
                        ->where('is_active', 1)
                        ->update(['is_active' => 0]);
                    
                    // Prepare bulk insert data
                    $boosterData = [];
                    foreach ($users as $user) {
                        $boosterData[] = [
                            'user_id' => $user->id,
                            'booster_type' => $this->boosterType,
                            'started_at' => $now,
                            'expires_at' => $this->expiresAt,
                            'is_active' => 1,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                    
                    // Bulk insert new boosters
                    if (!empty($boosterData)) {
                        // Insert in batches to avoid query size limits
                        $insertChunks = array_chunk($boosterData, 100);
                        foreach ($insertChunks as $chunk) {
                            UserBooster::insert($chunk);
                        }
                        $successCount += count($boosterData);
                    }
                    
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Error in bulk booster update for chunk: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
                    $failedCount += count($userIds);
                }
            });

        Log::info("GiveBoosterToAllUsers completed: Success={$successCount}, BoosterType={$this->boosterType}, ExpiresAt={$this->expiresAt}");
        
        // Verify the job completed successfully
        $totalUsers = User::where('account_status', 'active')->count();
        if ($successCount < $totalUsers) {
            Log::warning("GiveBoosterToAllUsers: Only {$successCount} out of {$totalUsers} users were processed successfully.");
        }
    }
}
