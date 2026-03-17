<?php

/**
 * SEO meta title & description templates.
 *
 * Variables use Laravel's :placeholder syntax.
 * Keep titles ≤60 chars (Latin) / ≤30 full-width chars (CJK).
 * Keep descriptions 150-160 chars.
 */

return [

    // ─── Model page ─────────────────────────────────────────
    // Adaptive title tiers (controller picks the longest that fits)
    'model_title_full'   => ':username :platform: Free Live Sex Show & Chat | PornGuru',
    'model_title_medium' => ':username :platform: Free Live Cam Show & Chat',
    'model_title_short'  => ':username - Free Live Cam & Chat | :platform',
    'model_title_mini'   => ':username: Free Live Cam & Chat | :platform',

    'model_desc'         => "Watch :username's live cam show free! :demo 🔥 Free chat, HD stream & full profile on :platform. Join :username now on PornGuru.cam ❤️ :tags",
    'model_desc_ai'      => ':description 🔥 Watch free on :platform via PornGuru.cam ❤️ :tags',

    // ─── Homepage ───────────────────────────────────────────
    'home_title'         => 'Free Live Sex Cams - Watch :count+ Models Live Now | PornGuru',
    'home_desc'          => 'Watch :count+ live cam models streaming free right now! 🔥 HD sex cams from Stripchat, Chaturbate & more. Free chat, no signup. Browse girls, couples, men & trans live on PornGuru.cam ❤️',
    'home_og_title'      => 'Free Live Sex Cams - :count+ Models Live | PornGuru',

    // ─── Niche pages (girls, men, couples, trans) ───────────
    'niche_title'        => 'Free Live :niche Cams - Watch :count+ :niche Now | PornGuru',
    'niche_desc'         => 'Watch :count+ live :niche_lc cam models streaming free! 🔥 HD :niche_lc sex cams with free chat. No signup needed. Browse the hottest :niche_lc on PornGuru.cam now ❤️',

    // ─── Tag pages ──────────────────────────────────────────
    'tag_title'          => ':tag Cams: Free Live :tag Sex Chat | PornGuru',
    'tag_desc'           => 'Watch :count+ live :tag cam models free! 🔥 HD :tag sex cams with free chat on PornGuru.cam ❤️',

    // ─── Country pages ──────────────────────────────────────
    'country_title'      => ':country Cams: Free Live :country Sex Chat | PornGuru',
    'country_desc'       => 'Watch :count+ live cam models from :country free! 🔥 :country sex cams with free chat & HD streams on PornGuru.cam ❤️',

    // ─── Explore page ───────────────────────────────────────
    'explore_title'      => ':category - Swipe Through Live Sex Cams Free | PornGuru',
    'explore_desc'       => 'Swipe through :category_lc live cam streams free! 🔥 TikTok-style feed of live sex cams. Watch HD previews & join shows instantly on PornGuru.cam ❤️',

    // ─── Roulette page ──────────────────────────────────────
    'roulette_title'     => ':category Cam Roulette: Free Random Live Sex Chat | PornGuru',
    'roulette_desc'      => 'Free cam roulette - get matched with random live :category_lc instantly! 🔥 Chatroulette-style random video chat. Spin to discover new models streaming now on PornGuru.cam ❤️',

    // ─── Layout fallback ────────────────────────────────────
    'default_title'      => 'Free Live Sex Cams - Watch Live Cam Shows Free | PornGuru',
    'default_desc'       => 'Watch free live sex cam shows from the hottest models! 🔥 HD streams from Stripchat, Chaturbate & more. Free chat, no signup on PornGuru.cam ❤️',
];
