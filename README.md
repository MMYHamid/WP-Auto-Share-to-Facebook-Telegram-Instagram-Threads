=== Auto Share to Facebook, Telegram, Instagram & Threads ===
Contributors: GENIUS Plugins
Tags: auto post, facebook, instagram, telegram, threads, scheduler, social media
Requires at least: 5.5
Tested up to: 6.6
Stable tag: 4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically share WordPress posts sequentially to Facebook, Telegram, Instagram, and Threads (placeholder). 
Supports 1–10 posts/day with fixed time slots starting at 6:00 AM. Includes admin log page.

== Description ==

This plugin lets you automatically share your WordPress blog posts to social media platforms.  
It supports **Facebook Pages, Telegram Channels/Groups, Instagram (via Graph API), and Threads (placeholder)**.  

Key Features:
* Share posts **sequentially from oldest to newest**
* Supports **1–10 posts per day**, evenly spaced from **6:00 AM to midnight**
* Share to:
  - Facebook Page (via Graph API)
  - Telegram Channel/Group (via Bot API)
  - Instagram Business Account (via Graph API)
  - Threads (placeholder until official API is released)
* Full **Admin Settings Page** for tokens, IDs, and post frequency
* **Log Page** to track which posts were shared and when
* Automatically re-schedules cron jobs if you change the daily post count
* Keeps up to **50 recent logs** for review

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/auto-share/`
2. Activate the plugin from **Plugins → Installed Plugins**
3. Go to **Dashboard → Auto Share → Settings**
4. Fill in:
   - **Facebook Page ID** & **Access Token**
   - **Telegram Bot Token** & **Channel/Group ID**
   - **Instagram Business User ID** & **Access Token**
   - (Optional) Threads placeholders
5. Set how many posts you want to share per day (1–10)
6. Save settings — cron jobs will be scheduled automatically
7. Check logs under **Dashboard → Auto Share → Shared Log**

== Frequently Asked Questions ==

= How does it decide which post to share? =
It shares **sequentially**, starting from the oldest published post, one by one until all are shared.

= What time are posts shared? =
The first share happens at **6:00 AM** server time. If you set multiple posts/day, they are evenly distributed (e.g. 4 posts/day = 6 AM, 12 PM, 6 PM, 12 AM).

= Can I change the daily post count? =
Yes — go to **Auto Share → Settings** and update "Daily Share Count". The plugin will automatically re-schedule cron jobs.

= Is Threads supported? =
Currently only as a **placeholder** (for when API becomes public). Your settings will be stored.

= Can I see which posts were shared? =
Yes — go to **Auto Share → Shared Log**. It shows time, post title (with link), and platforms.

== Screenshots ==
1. Settings page with platform credentials
2. Daily share count input
3. Shared posts log table

== Changelog ==

= 4.0 =
* Added Instagram Graph API support
* Added Threads placeholder
* Added support for up to 10 posts/day
* Added shared posts log page
* Smarter cron scheduling with reschedule on save

= 3.0 =
* Added sequential post sharing logic
* Added daily post count setting

= 2.0 =
* Added Telegram support

= 1.0 =
* Initial release (Facebook auto-share)

== Upgrade Notice ==
Always back up your database before updating, as logs and schedule counts are stored in WP options.

