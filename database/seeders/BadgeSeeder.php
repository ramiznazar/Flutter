<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uses insert() with explicit id so it works when badges.id has no AUTO_INCREMENT (e.g. after import from dump).
     */
    public function run(): void
    {
        // Clear existing badges
        Badge::truncate();

        $badges = [
            // Account Creation Badge
            [
                'id' => 1,
                'badge_name' => 'Newbie Explorer: Once User Creates Account',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png', // User icon
            ],
            
            // Mining Session Badges
            [
                'id' => 2,
                'badge_name' => 'Mining Novice: Once User Starts Their First Mining Session',
                'mining_sessions_required' => 1,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135789.png', // Pickaxe icon
            ],
            [
                'id' => 3,
                'badge_name' => 'Bronze Digger: Mine for 30 Sessions',
                'mining_sessions_required' => 30,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583912.png', // Bronze medal
            ],
            [
                'id' => 4,
                'badge_name' => 'Silver Seeker: Mine for 90 Sessions',
                'mining_sessions_required' => 90,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583907.png', // Silver medal
            ],
            [
                'id' => 5,
                'badge_name' => 'Gold Gleaner: Mine for 200 Sessions',
                'mining_sessions_required' => 200,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583905.png', // Gold medal
            ],
            [
                'id' => 6,
                'badge_name' => 'Diamond Delver: Mine for 500 Sessions',
                'mining_sessions_required' => 500,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583913.png', // Diamond
            ],
            
            // Invite Friends Badges
            [
                'id' => 7,
                'badge_name' => 'Social Apprentice: Once User Invites 5 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 5,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135807.png', // Users icon
            ],
            [
                'id' => 8,
                'badge_name' => 'Friendship Forger: Once User Invites 10 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 10,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135810.png', // Friends icon
            ],
            [
                'id' => 9,
                'badge_name' => 'Community Architect: Once User Invites 20 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 20,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135813.png', // Community icon
            ],
            [
                'id' => 10,
                'badge_name' => 'Networking Prodigy: Once User Invites 50 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 50,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135815.png', // Network icon
            ],
            
            // Spin Wheel Badges
            [
                'id' => 11,
                'badge_name' => 'Wheel Apprentice: Spin the Wheel 60 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 60,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135793.png', // Wheel icon
            ],
            [
                'id' => 12,
                'badge_name' => 'Wheel Enthusiast: Spin the Wheel 180 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 180,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135794.png', // Casino chip
            ],
            [
                'id' => 13,
                'badge_name' => 'Wheel Master: Spin the Wheel 500 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 500,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583903.png', // Trophy
            ],
            [
                'id' => 14,
                'badge_name' => 'Wheel Grandmaster: Spin the Wheel 1000 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 1000,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583904.png', // Prize icon
            ],
            
            // Wallet Balance Badges
            [
                'id' => 15,
                'badge_name' => 'Bronze Collector: Have 10 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 10,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135790.png', // Wallet icon
            ],
            [
                'id' => 16,
                'badge_name' => 'Silver Stasher: Have 50 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 50,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135791.png', // Money bag
            ],
            [
                'id' => 17,
                'badge_name' => 'Gold Hoarder: Have 100 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 100,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135792.png', // Gold coins
            ],
            [
                'id' => 18,
                'badge_name' => 'Diamond Tycoon: Have 500 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 500,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583913.png', // Diamond gem
            ],
            [
                'id' => 19,
                'badge_name' => 'Platinum Mogul: Have 1000 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 1000,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135795.png', // Treasure chest
            ],
            
            // Social Media Tasks Badge
            [
                'id' => 20,
                'badge_name' => 'Social Sovereign: Complete All Social Media Tasks',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => 1,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135796.png', // Share icon
            ],
        ];

        foreach ($badges as $badge) {
            DB::table('badges')->insert($badge);
        }

        $this->command->info('✅ Badges seeded successfully!');
        $this->command->info('Total badges created: ' . count($badges));
        $this->command->info('');
        $this->command->info('📝 Badge icons are using Flaticon CDN (free icons).');
        $this->command->info('💡 You can manage badge icons from: Admin Panel → Badges Management');
        $this->command->info('📁 Or upload custom images to: /public/badges/');
    }
}
