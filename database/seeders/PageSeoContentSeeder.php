<?php

namespace Database\Seeders;

use App\Models\PageSeoContent;
use Illuminate\Database\Seeder;

class PageSeoContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedEnglishContent();
        $this->seedSpanishContent();
        $this->seedFrenchContent();
        $this->seedGermanContent();
        $this->seedArabicContent();
        $this->seedPortugueseContent();
    }

    protected function seedEnglishContent(): void
    {
        // Homepage
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'en'],
            [
                'title' => 'Watch Free Live Sex Cams',
                'content' => "Welcome to PornGuru Cam, your ultimate destination for free live sex cams and adult webcam entertainment. Our platform brings together thousands of live cam models from the world's leading adult streaming platforms, making it easy to discover and enjoy live shows in one convenient location.

Whether you're looking for amateur performers, professional cam girls, or exotic models from around the world, PornGuru Cam has something for everyone. Our extensive directory features models from XLoveCam, StripChat, and other premium platforms, giving you access to the best live cam content available online.

Browse our categories to find exactly what you're looking for. From blonde bombshells to exotic brunettes, petite teens to curvy MILFs, we have thousands of live performers ready to entertain you. All streams are free to watch, and you can interact with models through chat, tipping, and private shows.

Our platform is updated in real-time, showing you which models are currently live and how many viewers they have. Discover new favorites, filter by tags, language, or country, and enjoy the best in live adult entertainment without any subscriptions or hidden fees.",
                'keywords' => 'live sex cams, free webcam shows, adult live streaming, cam girls, live porn, free cam sites, adult webcams',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Tags Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'tags_index', 'locale' => 'en'],
            [
                'title' => 'Browse Cam Categories',
                'content' => "Explore our extensive collection of cam categories to find your perfect match. PornGuru Cam organizes thousands of live models into easy-to-browse tags, making it simple to discover new performers based on your preferences.

Our category system includes everything from physical attributes like body type, hair color, and ethnicity, to performance styles and show types. Looking for a specific niche? We've got you covered with categories ranging from mainstream to specialty interests.

Each category page shows you how many models are currently streaming in that niche, along with viewer counts to help you find the most popular shows. You can combine multiple tags to narrow down your search, or browse our featured categories to see what's trending right now.

Whether you prefer young and playful performers or experienced, sophisticated cam models, our tagging system helps you navigate directly to the content you enjoy most. Start exploring our categories and discover your next favorite cam girl today.",
                'keywords' => 'cam categories, live cam tags, webcam categories, find cam girls, adult webcam categories, cam girl types',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Countries Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'countries_index', 'locale' => 'en'],
            [
                'title' => 'Cam Girls by Country',
                'content' => "Discover live cam models from around the world on PornGuru Cam. Our international directory features performers from over 50 countries, offering an incredible variety of cultures, languages, and personalities to explore.

Latin American models from Colombia, Venezuela, and Mexico are famous for their passionate performances and fiery personalities. European cam girls from Romania, Ukraine, and Poland combine elegance with sensuality. Asian performers from the Philippines, Japan, and Thailand bring exotic beauty and unique entertainment styles to their shows.

Each country page on our platform shows you all the models currently online from that region, complete with profile information and viewer counts. You can filter by additional tags to find the perfect combination of nationality and performance style that matches your preferences.

Browsing by country is also a great way to find models who speak your language or share your cultural background. Many of our international performers are multilingual and love connecting with viewers from around the world. Explore our global directory and experience the diversity of live cam entertainment.",
                'keywords' => 'cam girls by country, international webcam models, latina cam girls, european cam models, asian cams, colombian cam girls',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );
    }

    protected function seedSpanishContent(): void
    {
        // Homepage
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'es'],
            [
                'title' => 'Mira Cams de Sexo en Vivo Gratis',
                'content' => "Bienvenido a PornGuru Cam, tu destino definitivo para cams de sexo en vivo gratis y entretenimiento de webcam para adultos. Nuestra plataforma reúne miles de modelos de cámaras en vivo de las principales plataformas de streaming para adultos del mundo, facilitando descubrir y disfrutar shows en vivo en un solo lugar conveniente.

Ya sea que busques performers amateurs, cam girls profesionales, o modelos exóticas de todo el mundo, PornGuru Cam tiene algo para todos. Nuestro extenso directorio presenta modelos de XLoveCam, StripChat y otras plataformas premium, dándote acceso al mejor contenido de cams en vivo disponible en línea.

Navega por nuestras categorías para encontrar exactamente lo que buscas. Desde rubias despampanantes hasta morenas exóticas, chicas petite hasta MILFs voluptuosas, tenemos miles de performers en vivo listas para entretenerte. Todos los streams son gratis para ver, y puedes interactuar con las modelos a través de chat, propinas y shows privados.

Nuestra plataforma se actualiza en tiempo real, mostrándote qué modelos están actualmente en vivo y cuántos espectadores tienen. Descubre nuevas favoritas, filtra por etiquetas, idioma o país, y disfruta lo mejor del entretenimiento para adultos en vivo sin suscripciones ni tarifas ocultas.",
                'keywords' => 'cams de sexo en vivo, webcam gratis, streaming adultos, cam girls, porno en vivo, cams gratis',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Tags Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'tags_index', 'locale' => 'es'],
            [
                'title' => 'Explorar Categorías de Cams',
                'content' => "Explora nuestra extensa colección de categorías de cams para encontrar tu pareja perfecta. PornGuru Cam organiza miles de modelos en vivo en etiquetas fáciles de navegar, facilitando descubrir nuevas performers basándote en tus preferencias.

Nuestro sistema de categorías incluye todo, desde atributos físicos como tipo de cuerpo, color de cabello y etnia, hasta estilos de performance y tipos de shows. ¿Buscas un nicho específico? Te tenemos cubierto con categorías que van desde lo mainstream hasta intereses especializados.

Cada página de categoría te muestra cuántas modelos están actualmente transmitiendo en ese nicho, junto con conteos de espectadores para ayudarte a encontrar los shows más populares. Puedes combinar múltiples etiquetas para reducir tu búsqueda, o navegar por nuestras categorías destacadas para ver qué está en tendencia ahora mismo.

Ya sea que prefieras performers jóvenes y juguetonas o modelos de cams experimentadas y sofisticadas, nuestro sistema de etiquetado te ayuda a navegar directamente al contenido que más disfrutas.",
                'keywords' => 'categorías de cams, etiquetas de webcam, categorías adultos, tipos de cam girls, buscar modelos',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Countries Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'countries_index', 'locale' => 'es'],
            [
                'title' => 'Cam Girls por País',
                'content' => "Descubre modelos de cams en vivo de todo el mundo en PornGuru Cam. Nuestro directorio internacional presenta performers de más de 50 países, ofreciendo una increíble variedad de culturas, idiomas y personalidades para explorar.

Las modelos latinoamericanas de Colombia, Venezuela y México son famosas por sus performances apasionados y personalidades ardientes. Las cam girls europeas de Rumania, Ucrania y Polonia combinan elegancia con sensualidad. Las performers asiáticas de Filipinas, Japón y Tailandia traen belleza exótica y estilos únicos de entretenimiento a sus shows.

Cada página de país en nuestra plataforma te muestra todas las modelos actualmente en línea de esa región, completa con información de perfil y conteos de espectadores. Puedes filtrar por etiquetas adicionales para encontrar la combinación perfecta de nacionalidad y estilo de performance que coincida con tus preferencias.

Navegar por país también es una excelente manera de encontrar modelos que hablen tu idioma o compartan tu trasfondo cultural. Muchas de nuestras performers internacionales son multilingües y les encanta conectar con espectadores de todo el mundo.",
                'keywords' => 'cam girls por país, modelos internacionales, latinas webcam, modelos europeas, cams asiáticas, colombianas cam',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );
    }

    protected function seedFrenchContent(): void
    {
        // Homepage
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'fr'],
            [
                'title' => 'Regardez des Cams Sexe en Direct Gratuites',
                'content' => "Bienvenue sur PornGuru Cam, votre destination ultime pour les cams sexe en direct gratuites et le divertissement webcam pour adultes. Notre plateforme rassemble des milliers de modèles de cams en direct des principales plateformes de streaming pour adultes au monde, facilitant la découverte et le plaisir des shows en direct en un seul endroit pratique.

Que vous recherchiez des performers amateurs, des cam girls professionnelles ou des modèles exotiques du monde entier, PornGuru Cam a quelque chose pour tout le monde. Notre répertoire complet présente des modèles de XLoveCam, StripChat et d'autres plateformes premium, vous donnant accès au meilleur contenu de cams en direct disponible en ligne.

Parcourez nos catégories pour trouver exactement ce que vous cherchez. Des blondes bombes aux brunes exotiques, des filles petites aux MILFs voluptueuses, nous avons des milliers de performers en direct prêtes à vous divertir. Tous les streams sont gratuits à regarder, et vous pouvez interagir avec les modèles par chat, pourboires et shows privés.

Notre plateforme est mise à jour en temps réel, vous montrant quels modèles sont actuellement en direct et combien de spectateurs ils ont. Découvrez de nouvelles favorites, filtrez par tags, langue ou pays, et profitez du meilleur du divertissement adulte en direct sans abonnements ni frais cachés.",
                'keywords' => 'cams sexe en direct, webcam gratuit, streaming adulte, cam girls, porno en direct, cams gratuites',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Tags Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'tags_index', 'locale' => 'fr'],
            [
                'title' => 'Parcourir les Catégories de Cams',
                'content' => "Explorez notre vaste collection de catégories de cams pour trouver votre match parfait. PornGuru Cam organise des milliers de modèles en direct en tags faciles à parcourir, facilitant la découverte de nouvelles performers selon vos préférences.

Notre système de catégories inclut tout, des attributs physiques comme le type de corps, la couleur des cheveux et l'ethnicité, aux styles de performance et types de shows. Vous cherchez une niche spécifique? Nous avons ce qu'il vous faut avec des catégories allant du mainstream aux intérêts spécialisés.

Chaque page de catégorie vous montre combien de modèles diffusent actuellement dans cette niche, avec le nombre de spectateurs pour vous aider à trouver les shows les plus populaires. Vous pouvez combiner plusieurs tags pour affiner votre recherche, ou parcourir nos catégories en vedette pour voir ce qui est tendance en ce moment.

Que vous préfériez les performers jeunes et joueuses ou les modèles de cams expérimentées et sophistiquées, notre système de tags vous aide à naviguer directement vers le contenu que vous appréciez le plus.",
                'keywords' => 'catégories de cams, tags webcam, catégories adultes, types de cam girls, rechercher modèles',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );
    }

    protected function seedGermanContent(): void
    {
        // Homepage
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'de'],
            [
                'title' => 'Kostenlose Live Sex Cams Ansehen',
                'content' => "Willkommen bei PornGuru Cam, Ihrem ultimativen Ziel für kostenlose Live Sex Cams und Erwachsenen-Webcam-Unterhaltung. Unsere Plattform vereint tausende Live-Cam-Models von den weltweit führenden Erwachsenen-Streaming-Plattformen und macht es einfach, Live-Shows an einem praktischen Ort zu entdecken und zu genießen.

Ob Sie nach Amateur-Performern, professionellen Cam Girls oder exotischen Models aus aller Welt suchen, PornGuru Cam hat für jeden etwas zu bieten. Unser umfangreiches Verzeichnis präsentiert Models von XLoveCam, StripChat und anderen Premium-Plattformen und gibt Ihnen Zugang zu den besten verfügbaren Live-Cam-Inhalten im Internet.

Durchstöbern Sie unsere Kategorien, um genau das zu finden, wonach Sie suchen. Von blonden Bomben bis zu exotischen Brünetten, von zierlichen Teens bis zu kurvigen MILFs - wir haben tausende Live-Performer, die bereit sind, Sie zu unterhalten. Alle Streams sind kostenlos anzusehen, und Sie können mit den Models über Chat, Trinkgelder und private Shows interagieren.

Unsere Plattform wird in Echtzeit aktualisiert und zeigt Ihnen, welche Models gerade live sind und wie viele Zuschauer sie haben. Entdecken Sie neue Favoriten, filtern Sie nach Tags, Sprache oder Land und genießen Sie das Beste der Live-Erwachsenenunterhaltung ohne Abonnements oder versteckte Gebühren.",
                'keywords' => 'live sex cams, kostenlose webcams, erwachsenen streaming, cam girls, live porno, gratis cams',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Tags Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'tags_index', 'locale' => 'de'],
            [
                'title' => 'Cam-Kategorien Durchsuchen',
                'content' => "Erkunden Sie unsere umfangreiche Sammlung von Cam-Kategorien, um Ihren perfekten Match zu finden. PornGuru Cam organisiert tausende Live-Models in einfach zu durchsuchende Tags, was es einfach macht, neue Performer basierend auf Ihren Vorlieben zu entdecken.

Unser Kategoriesystem umfasst alles von physischen Attributen wie Körpertyp, Haarfarbe und Ethnizität bis hin zu Performance-Stilen und Show-Typen. Suchen Sie eine bestimmte Nische? Wir haben Sie mit Kategorien abgedeckt, die von Mainstream bis zu Spezialinteressen reichen.

Jede Kategorieseite zeigt Ihnen, wie viele Models derzeit in dieser Nische streamen, zusammen mit Zuschauerzahlen, um Ihnen zu helfen, die beliebtesten Shows zu finden. Sie können mehrere Tags kombinieren, um Ihre Suche einzugrenzen, oder unsere vorgestellten Kategorien durchstöbern, um zu sehen, was gerade im Trend liegt.

Ob Sie junge und verspielte Performer oder erfahrene, anspruchsvolle Cam-Models bevorzugen, unser Tagging-System hilft Ihnen, direkt zu den Inhalten zu navigieren, die Sie am meisten genießen.",
                'keywords' => 'cam kategorien, webcam tags, erwachsenen kategorien, cam girl typen, models suchen',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );
    }

    protected function seedArabicContent(): void
    {
        // Homepage
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'ar'],
            [
                'title' => 'شاهد كاميرات الجنس الحية مجانًا',
                'content' => "مرحبًا بك في PornGuru Cam، وجهتك النهائية لكاميرات الجنس الحية المجانية وترفيه الكاميرا للبالغين. تجمع منصتنا آلاف عارضات الكاميرا الحية من منصات البث للبالغين الرائدة في العالم، مما يسهل اكتشاف العروض الحية والاستمتاع بها في مكان واحد مريح.

سواء كنت تبحث عن عارضات هواة أو محترفات أو عارضات غريبات من جميع أنحاء العالم، لدى PornGuru Cam شيء للجميع. يضم دليلنا الشامل عارضات من XLoveCam وStripChat ومنصات متميزة أخرى، مما يمنحك وصولاً إلى أفضل محتوى كاميرا حية متاح عبر الإنترنت.

تصفح فئاتنا للعثور على ما تبحث عنه بالضبط. من الشقراوات الجذابات إلى السمراوات الغريبات، من الفتيات النحيلات إلى الأمهات المثيرات، لدينا آلاف العارضات الحية الجاهزات للترفيه عنك. جميع البثوث مجانية للمشاهدة، ويمكنك التفاعل مع العارضات عبر الدردشة والإكراميات والعروض الخاصة.

يتم تحديث منصتنا في الوقت الفعلي، مما يظهر لك العارضات المتصلات حاليًا وعدد المشاهدين لديهن. اكتشف مفضلات جديدة، وفلتر حسب الوسوم أو اللغة أو البلد، واستمتع بأفضل الترفيه الحي للبالغين بدون اشتراكات أو رسوم خفية.",
                'keywords' => 'كاميرات جنس حية, كاميرا ويب مجانية, بث للبالغين, فتيات كاميرا, بورن حي, كاميرات مجانية',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Tags Index
        PageSeoContent::updateOrCreate(
            ['page_key' => 'tags_index', 'locale' => 'ar'],
            [
                'title' => 'تصفح فئات الكاميرا',
                'content' => "استكشف مجموعتنا الواسعة من فئات الكاميرا للعثور على المطابقة المثالية لك. ينظم PornGuru Cam آلاف العارضات الحية في وسوم سهلة التصفح، مما يسهل اكتشاف عارضات جدد بناءً على تفضيلاتك.

يشمل نظام الفئات لدينا كل شيء من السمات الجسدية مثل نوع الجسم ولون الشعر والعرق، إلى أساليب الأداء وأنواع العروض. هل تبحث عن تخصص معين؟ لدينا ما يناسبك مع فئات تتراوح من الأساسية إلى الاهتمامات المتخصصة.

تُظهر لك كل صفحة فئة عدد العارضات اللاتي يبثن حاليًا في هذا التخصص، مع أعداد المشاهدين لمساعدتك في العثور على أشهر العروض. يمكنك دمج عدة وسوم لتضييق بحثك، أو تصفح فئاتنا المميزة لمعرفة ما هو رائج الآن.

سواء كنت تفضل العارضات الشابات المرحات أو عارضات الكاميرا ذوات الخبرة والأناقة، يساعدك نظام الوسوم لدينا في التنقل مباشرة إلى المحتوى الذي تستمتع به أكثر.",
                'keywords' => 'فئات الكاميرا, وسوم كاميرا الويب, فئات البالغين, أنواع فتيات الكاميرا, البحث عن عارضات',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );
    }

    protected function seedPortugueseContent(): void
    {
        // Homepage - Brazilian Portuguese
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'pt'],
            [
                'title' => 'Assista Câmeras de Sexo Ao Vivo Grátis',
                'content' => "Bem-vindo ao PornGuru Cam, seu destino definitivo para câmeras de sexo ao vivo grátis e entretenimento de webcam adulto. Nossa plataforma reúne milhares de modelos de câmera ao vivo das principais plataformas de streaming adulto do mundo, facilitando descobrir e curtir shows ao vivo em um só lugar conveniente.

Seja você procurando performers amadoras, cam girls profissionais ou modelos exóticas de todo o mundo, PornGuru Cam tem algo para todos. Nosso extenso diretório apresenta modelos do XLoveCam, StripChat e outras plataformas premium, dando-lhe acesso ao melhor conteúdo de câmera ao vivo disponível online.

Navegue por nossas categorias para encontrar exatamente o que você está procurando. De loiras deslumbrantes a morenas exóticas, garotas petite a MILFs curvilíneas, temos milhares de performers ao vivo prontas para te entreter. Todas as transmissões são gratuitas para assistir, e você pode interagir com as modelos através de chat, gorjetas e shows privados.

Nossa plataforma é atualizada em tempo real, mostrando quais modelos estão atualmente online e quantos espectadores elas têm. Descubra novas favoritas, filtre por tags, idioma ou país, e aproveite o melhor do entretenimento adulto ao vivo sem assinaturas ou taxas ocultas.",
                'keywords' => 'câmeras de sexo ao vivo, webcam grátis, streaming adulto, cam girls, pornô ao vivo, câmeras grátis',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );

        // Also add Brazilian Portuguese variant
        PageSeoContent::updateOrCreate(
            ['page_key' => 'home', 'locale' => 'pt-BR'],
            [
                'title' => 'Assista Câmeras de Sexo Ao Vivo Grátis',
                'content' => "Bem-vindo ao PornGuru Cam, seu destino definitivo para câmeras de sexo ao vivo grátis e entretenimento de webcam adulto. Nossa plataforma reúne milhares de modelos de câmera ao vivo das principais plataformas de streaming adulto do mundo, facilitando descobrir e curtir shows ao vivo em um só lugar conveniente.

Seja você procurando performers amadoras, cam girls profissionais ou modelos exóticas de todo o mundo, PornGuru Cam tem algo para todos. Nosso extenso diretório apresenta modelos do XLoveCam, StripChat e outras plataformas premium, dando-lhe acesso ao melhor conteúdo de câmera ao vivo disponível online.

Navegue por nossas categorias para encontrar exatamente o que você está procurando. De loiras deslumbrantes a morenas exóticas, garotas petite a MILFs curvilíneas, temos milhares de performers ao vivo prontas para te entreter. Todas as transmissões são gratuitas para assistir, e você pode interagir com as modelos através de chat, gorjetas e shows privados.

Nossa plataforma é atualizada em tempo real, mostrando quais modelos estão atualmente online e quantos espectadores elas têm. Descubra novas favoritas, filtre por tags, idioma ou país, e aproveite o melhor do entretenimento adulto ao vivo sem assinaturas ou taxas ocultas.",
                'keywords' => 'câmeras de sexo ao vivo, webcam grátis, streaming adulto, cam girls, pornô ao vivo, câmeras grátis, brasileiras',
                'position' => 'bottom',
                'is_active' => true,
            ]
        );
    }
}
