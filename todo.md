## Completed Features

### Tip Menu & Model Descriptions (Feb 2026)
- [x] Created `tip_action_types` table with 21 predefined actions
- [x] Created `tip_action_translations` table for multilingual support
- [x] Created `model_tip_menus` table for model-specific pricing
- [x] Created `model_descriptions` table (AI, manual, or imported)
- [x] Created `<x-tip-menu>` Blade component with CTA
- [x] Created `TipActionType`, `TipActionTranslation`, `ModelTipMenu`, `ModelDescription` models
- [x] Created `php artisan seo:generate-model-descriptions` command
- [x] Created `php artisan seo:translate-tip-actions` command
- [x] Created `TipActionTypesSeeder` with all action categories
- [x] Added enhanced "About" section with traits and specialties

### Action Categories:
- **Tease**: Flash, Boobs Flash, Ass Flash, Pussy Flash
- **Dance**: Dance, Twerk, Striptease
- **Interactive**: Kiss, Lick Lips, Wink, Say My Name, Moan
- **Special**: Oil Show, Feet Show, Spanking, Fingering, Toy Play, Close Up
- **Outfit**: Change Outfit, Wear Stockings, High Heels

### SEO Content Blocks (Feb 2026)
- [x] Added SEO text sections to homepage, tags index, countries index
- [x] Added SEO text to individual tag and country pages  
- [x] Created PageSeoContent model with multilingual support
- [x] Created `<x-seo.content-block>` Blade component
- [x] Created `php artisan seo:generate-page-content` command
- [x] Created PageSeoContentSeeder with content in EN/ES/FR/DE
- [x] Styled SEO text blocks with CSS

### To generate content:
```bash
# Run migration first
php artisan migrate

# Seed tip actions (21 types)
php artisan db:seed --class=TipActionTypesSeeder

# Translate tip actions to all languages
php artisan seo:translate-tip-actions --locale=all

# Generate model descriptions (AI)
php artisan seo:generate-model-descriptions --limit=50
php artisan seo:generate-model-descriptions --model=SomeUsername --force
php artisan seo:generate-model-descriptions --translate --locale=fr

# Seed default SEO content (EN/ES/FR/DE)
php artisan db:seed --class=PageSeoContentSeeder

# Generate SEO page content via AI (requires ANTHROPIC_API_KEY)
php artisan seo:generate-page-content --pages=home,tags_index,countries_index --locale=en
php artisan seo:generate-page-content --tags --countries --limit=50
php artisan seo:generate-page-content --translate --locale=pt
```

---

## Old Notes

fix le css 
design system blade css
faire un truck clean au niveau de la landing home page 
preview des live sur le hover 
ou toggle d'activer tout les preview en meme temps 
faire la meme barre room goal que stripchat et la faire sous les stream en petit pour voir les cams 
proche d'un room goal 
et puis faire en sorte que tout les links sont bien des liens affiliate 
metre le interstitial si la modele est offline 
bien faire les cards pour les stream clean 
afficher le nombre de viewers en live 




Building Model Listings
Once you have model information in your database (including geobans, statuses, and CDNHosts config), you can build model listings by:

Selecting models considered online now (based on their last appearance in the API with online status)
Filtering out models with geobans matching the viewer's country
Sorting by rating or using your advanced sorting algorithm
Using the image URLs directly from the API response (not hosting the images on your servers)
Building Model Profile Pages
Page Templates
Create two separate templates:

Online view - for when a model is currently online.
Offline view - for when a model is offline.
Content for Both Views
In both online and offline views, you can include:

Model tags as specifications (with links to tag-based listings)
Model's country and other information from the API
Online View
For online models, you can show their stream by:

Using the provided script (recommended). Don’t forget to add your userId and model username to have your affiliate click counted properly and lead to a proper model room.
Or using the stream URL from the models API with your own player. In this case, you must respect the CDNConfig to ensure optimal stream delivery for viewers in different countries.
Offline View
When a model is offline:

Do not host any images of a model
Provide a link to the model's page (how to build a link)
Display the model's profile image. The stream images will expire quickly, so it’s not recommended to use stream snapshots.


lets build a nice aggregator build an online page offline page 
work on the descriptions and the contents fo the model pages
build tag pages 
category pages 
maybe a custom search results page for searching 

where to put all the porn search egine stuff ??

i would guruengine ?
build static pages on pornguru.com ??

