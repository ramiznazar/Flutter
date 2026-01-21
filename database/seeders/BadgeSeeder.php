<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing badges
        Badge::truncate();

        $badges = [
            // Account Creation Badge
            [
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
                'badge_name' => 'Mining Novice: Once User Starts Their First Mining Session',
                'mining_sessions_required' => 1,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135789.png', // Pickaxe icon
            ],
            [
                'badge_name' => 'Bronze Digger: Mine for 30 Sessions',
                'mining_sessions_required' => 30,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583912.png', // Bronze medal
            ],
            [
                'badge_name' => 'Silver Seeker: Mine for 90 Sessions',
                'mining_sessions_required' => 90,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583907.png', // Silver medal
            ],
            [
                'badge_name' => 'Gold Gleaner: Mine for 200 Sessions',
                'mining_sessions_required' => 200,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583905.png', // Gold medal
            ],
            [
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
                'badge_name' => 'Social Apprentice: Once User Invites 5 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 5,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135807.png', // Users icon
            ],
            [
                'badge_name' => 'Friendship Forger: Once User Invites 10 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 10,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135810.png', // Friends icon
            ],
            [
                'badge_name' => 'Community Architect: Once User Invites 20 Friends',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => 20,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135813.png', // Community icon
            ],
            [
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
                'badge_name' => 'Wheel Apprentice: Spin the Wheel 60 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 60,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135793.png', // Wheel icon
            ],
            [
                'badge_name' => 'Wheel Enthusiast: Spin the Wheel 180 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 180,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135794.png', // Casino chip
            ],
            [
                'badge_name' => 'Wheel Master: Spin the Wheel 500 Times',
                'mining_sessions_required' => null,
                'spin_wheel_required' => 500,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => null,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583903.png', // Trophy
            ],
            [
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
                'badge_name' => 'Bronze Collector: Have 10 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 10,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135790.png', // Wallet icon
            ],
            [
                'badge_name' => 'Silver Stasher: Have 50 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 50,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135791.png', // Money bag
            ],
            [
                'badge_name' => 'Gold Hoarder: Have 100 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 100,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/3135/3135792.png', // Gold coins
            ],
            [
                'badge_name' => 'Diamond Tycoon: Have 500 Crutox in Wallet',
                'mining_sessions_required' => null,
                'spin_wheel_required' => null,
                'invite_friends_required' => null,
                'crutox_in_wallet_required' => 500,
                'social_media_task_completed' => null,
                'badges_icon' => 'https://cdn-icons-png.flaticon.com/512/2583/2583913.png', // Diamond gem
            ],
            [
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
            Badge::create($badge);
        }

        $this->command->info('✅ Badges seeded successfully!');
        $this->command->info('Total badges created: ' . count($badges));
        $this->command->info('');
        $this->command->info('📝 Badge icons are using Flaticon CDN (free icons).');
        $this->command->info('💡 You can manage badge icons from: Admin Panel → Badges Management');
        $this->command->info('📁 Or upload custom images to: /public/badges/');
    }
}
