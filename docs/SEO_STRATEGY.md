# SEO Strategy: From Zero to Organic Traffic

Guide for getting pornguru.cam indexed, building organic traffic, and monetizing.

---

## Table of Contents

1. [Current SEO Setup](#current-seo-setup)
2. [Getting Google to Index Your Pages](#getting-google-to-index-your-pages)
3. [Content Strategy](#content-strategy)
4. [Link Building & Traffic Acquisition](#link-building--traffic-acquisition)
5. [Paid Traffic (Quick Wins)](#paid-traffic-quick-wins)
6. [Google Analytics Setup](#google-analytics-setup)
7. [Tracking Affiliate Clicks](#tracking-affiliate-clicks)
8. [Monthly SEO Checklist](#monthly-seo-checklist)

---

## Current SEO Setup

What's already implemented:

| Feature | Status | Notes |
|---------|--------|-------|
| Unique meta titles | Done | Per model, tag, country page |
| Meta descriptions | Done | AI-generated for models, translatable for tags/countries |
| Canonical URLs | Done | Self-referencing on all pages |
| Hreflang tags | Done | 16 priority locales on all pages |
| Schema.org JSON-LD | Done | ProfilePage, FAQPage, VideoObject, BreadcrumbList |
| Sitemap.xml | Done | 82 sitemaps (models, tags, countries x locales) |
| Translated URLs | Done | `/fr/model/X`, `/es/tag/asiatiques`, etc. |
| Translated slugs | Done | Tags translated to 10+ locales, countries in progress |
| FAQ sections | Done | AI-generated, localized, expandable accordion |
| robots.txt | Check | Make sure it exists and allows crawling |
| Page speed | Check | Run Lighthouse, optimize images |

---

## Getting Google to Index Your Pages

### Step 1: Google Search Console (Day 1)

1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add property `pornguru.cam`
3. Verify ownership (DNS TXT record is easiest)
4. Submit your sitemap: `https://pornguru.cam/sitemap.xml`
5. Request indexing of your homepage manually

### Step 2: Submit Key Pages for Indexing

Google Search Console lets you manually submit up to ~50 URLs/day:
- Submit homepage
- Submit top 10 model pages (highest viewership)
- Submit all niche pages (`/girls`, `/couples`, `/men`, `/trans`)
- Submit `/tags` and `/countries` index pages
- Submit top translated pages (`/fr/`, `/es/`, `/de/`)

### Step 3: Create a robots.txt

```
User-agent: *
Allow: /
Disallow: /api/
Disallow: /login
Disallow: /register
Disallow: /dashboard

Sitemap: https://pornguru.cam/sitemap.xml
```

### Step 4: Internal Linking

Google discovers pages by following links. Make sure:
- Homepage links to all niche pages
- Niche pages link to popular tags
- Tag pages link to models
- Model pages link to similar models
- Footer has links to countries, tags, niches

### Step 5: Ping Search Engines

```bash
# After each deploy, ping Google and Bing
curl "https://www.google.com/ping?sitemap=https://pornguru.cam/sitemap.xml"
curl "https://www.bing.com/ping?sitemap=https://pornguru.cam/sitemap.xml"
```

Add this to `deploy.sh` at the end.

---

## Content Strategy

### Why It Matters

Google ranks pages with unique, valuable content higher. Your advantage:
- AI-generated model descriptions (already done)
- Translated content in 16+ languages (already done)
- FAQ sections (already done)

### What to Add Next

1. **Blog posts** (highest impact for organic traffic)
   - "Best [niche] cam models of 2026"
   - "How to use [platform] features"
   - "Top cam sites compared"
   - Publish 2-4 posts/month, 1000+ words each
   - Internal link from blog posts to model/tag pages

2. **Tag page descriptions** (in progress)
   - Unique 200-300 word descriptions per tag
   - Already generating via `seo:generate-page-content`

3. **Country page descriptions** (in progress)
   - "Best cam models from [country]"
   - Already generating via `seo:generate-page-content`

4. **Model comparison pages** (future)
   - "NANA_7 vs [similar model]"
   - Auto-generate from model stats

---

## Link Building & Traffic Acquisition

### Free Methods

1. **Social media profiles** (Week 1)
   - Create Twitter/X account, post model highlights
   - Reddit: participate in relevant subreddits (don't spam)
   - Telegram channel for updates

2. **Directory submissions** (Week 1-2)
   - Submit to adult directory sites
   - Submit to DMOZ-style adult directories
   - Submit to review sites

3. **Forum participation** (Ongoing)
   - Adult webmaster forums (GFY, XBIZ)
   - Share insights, link naturally in signatures

4. **Guest posting** (Month 2+)
   - Write for adult industry blogs
   - Provide unique data/insights from your aggregator

### Backlink Strategy

Focus on getting links from:
- Adult directories (DA 30+)
- Adult blog networks
- Model fan communities
- Adult review sites

---

## Paid Traffic (Quick Wins)

### When You Have $0 Budget

1. **Google Search Console** — free, submit pages manually
2. **Social media** — free, post consistently
3. **Forum signatures** — free, organic traffic from discussions

### When You Have $50-200/month

1. **TrafficJunky** — the biggest adult ad network (Pornhub/MindGeek)
   - CPM-based, start with $0.10-0.30 CPM
   - Target by country, device, category
   - Best for initial traffic spike that helps indexing

2. **ExoClick** — second-largest adult network
   - More affordable, good geo-targeting
   - Banner ads, native ads, push notifications

3. **JuicyAds** — smaller but good ROI
   - Good for niche targeting

### When You Have $500+/month

1. **Content marketing** — hire writers for blog posts
2. **Social media management** — consistent posting
3. **Link building services** — outreach for backlinks
4. **Combination of ad networks** above

### How Paid Traffic Helps SEO

Google's algorithm considers user signals:
- If people click your site in search results → higher CTR → better rankings
- If people stay on your site → lower bounce rate → better rankings
- Paid traffic brings initial users who create these signals
- More traffic → more data → better optimization decisions

---

## Google Analytics Setup

### Step 1: Create GA4 Property

1. Go to [Google Analytics](https://analytics.google.com)
2. Create a new GA4 property for `pornguru.cam`
3. Get your Measurement ID (starts with `G-`)

### Step 2: Add to Your .env

```bash
# In .env.production
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

### Step 3: Verify

The tracking code is already in the layout (`layouts/pornguru.blade.php`).
It loads conditionally when `GOOGLE_ANALYTICS_ID` is set.

### What's Already Tracked

- **Page views** — every page load
- **Affiliate clicks** — clicks on stripguru/stripchat links (custom event `affiliate_click`)
- **Model interactions** — which models get clicked

### Custom Events to Add Later

Consider tracking:
- Language selector usage
- Favorite button clicks
- Filter/search usage
- Time spent on model pages
- Stream play events

---

## Tracking Affiliate Clicks

The analytics code already tracks clicks on affiliate links:

```javascript
// Already in pornguru.blade.php
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*="stripguru"], a[href*="stripchat"], a[data-affiliate]');
    if (link) {
        gtag('event', 'affiliate_click', {
            'event_category': 'outbound',
            'event_label': link.href,
            'model_name': link.dataset.model || ''
        });
    }
});
```

To see which models drive the most affiliate clicks:
1. GA4 → Reports → Engagement → Events
2. Filter by event name `affiliate_click`
3. Look at `event_label` for URL breakdown and `model_name` for model breakdown

### Tip: Add data-model to affiliate links

For better tracking, add `data-model="{{ $model->username }}"` to affiliate links:

```blade
<a href="https://stripguru.com/model/{{ $model->username }}"
   data-affiliate
   data-model="{{ $model->username }}">
    Watch on Stripchat
</a>
```

---

## Monthly SEO Checklist

### Week 1
- [ ] Check Google Search Console for errors/warnings
- [ ] Submit any new pages for indexing
- [ ] Review crawl stats (are pages being discovered?)
- [ ] Check Core Web Vitals

### Week 2
- [ ] Publish 1 blog post (if blog is set up)
- [ ] Generate AI descriptions for new models
- [ ] Check translation coverage for new tags

### Week 3
- [ ] Review analytics: top pages, traffic sources
- [ ] Review affiliate click data
- [ ] Optimize underperforming pages (update descriptions)

### Week 4
- [ ] Check competitor rankings
- [ ] Review keyword opportunities in Search Console
- [ ] Plan next month's content

### Ongoing
- [ ] Monitor site speed (target < 3s load time)
- [ ] Ensure sitemap is up to date (auto-generated on deploy)
- [ ] Check for broken links/404s in Search Console
- [ ] Monitor index coverage (how many pages are indexed vs. submitted)

---

## Key Metrics to Track (Semrush / GA4)

| Metric | Target (Month 1) | Target (Month 6) |
|--------|-------------------|-------------------|
| Indexed pages | 500+ | 5,000+ |
| Organic traffic | 100/day | 1,000/day |
| Avg. position (Search Console) | Top 50 | Top 20 |
| Affiliate clicks | 10/day | 100/day |
| Bounce rate | < 70% | < 60% |
| Pages per session | > 1.5 | > 2.5 |

---

## Quick Start Summary

1. **Today**: Set up Google Search Console, submit sitemap, add GA4 ID to `.env.production`
2. **This week**: Submit top 50 pages for indexing, create social media profiles
3. **This month**: Start a blog, sign up for TrafficJunky/ExoClick with small budget
4. **Ongoing**: Generate translations, publish content, build links, monitor analytics
