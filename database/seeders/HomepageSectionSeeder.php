<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use App\Models\HomepageSectionTranslation;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'title' => 'Big Ass Sex Cams',
                'slug' => 'big-ass',
                'type' => 'tag_category',
                'tags' => ['big-ass', 'bigass', 'big ass', 'booty', 'pawg'],
                'sort_order' => 1,
                'translations' => [
                    'es' => 'Cámaras de Sexo Culo Grande',
                    'fr' => 'Cams Sexe Gros Cul',
                    'de' => 'Großer Po Sex Cams',
                    'pt' => 'Câmeras de Sexo Bundão',
                    'it' => 'Cam Sesso Culo Grande',
                    'nl' => 'Grote Kont Sex Cams',
                    'pl' => 'Kamery Erotyczne Duży Tyłek',
                    'ru' => 'Секс Камеры Большая Попа',
                    'ja' => '巨尻セックスカム',
                    'ko' => '빅 애스 섹스 캠',
                    'zh' => '大屁股直播',
                    'ar' => 'كام مؤخرة كبيرة',
                    'tr' => 'Büyük Kalça Seks Kameraları',
                    'pt-BR' => 'Câmeras de Sexo Bundão',
                    'es-MX' => 'Cámaras de Sexo Trasero Grande',
                ],
            ],
            [
                'title' => 'Asian Sex Cams',
                'slug' => 'asian',
                'type' => 'tag_category',
                'tags' => ['asian', 'japanese', 'korean', 'chinese', 'thai', 'filipina'],
                'sort_order' => 2,
                'translations' => [
                    'es' => 'Cámaras de Sexo Asiáticas',
                    'fr' => 'Cams Sexe Asiatiques',
                    'de' => 'Asiatische Sex Cams',
                    'pt' => 'Câmeras de Sexo Asiáticas',
                    'it' => 'Cam Sesso Asiatiche',
                    'nl' => 'Aziatische Sex Cams',
                    'pl' => 'Kamery Erotyczne Azjatyckie',
                    'ru' => 'Азиатские Секс Камеры',
                    'ja' => 'アジアンセックスカム',
                    'ko' => '아시안 섹스 캠',
                    'zh' => '亚洲直播',
                    'ar' => 'كام آسيوي',
                    'tr' => 'Asyalı Seks Kameraları',
                    'pt-BR' => 'Câmeras de Sexo Asiáticas',
                    'es-MX' => 'Cámaras de Sexo Asiáticas',
                ],
            ],
            [
                'title' => 'Latina Sex Cams',
                'slug' => 'latina',
                'type' => 'tag_category',
                'tags' => ['latina', 'latin', 'colombian', 'brazilian'],
                'sort_order' => 3,
                'translations' => [
                    'es' => 'Cámaras de Sexo Latinas',
                    'fr' => 'Cams Sexe Latinas',
                    'de' => 'Latina Sex Cams',
                    'pt' => 'Câmeras de Sexo Latinas',
                    'it' => 'Cam Sesso Latine',
                    'nl' => 'Latina Sex Cams',
                    'pl' => 'Kamery Erotyczne Latynoski',
                    'ru' => 'Латинские Секс Камеры',
                    'ja' => 'ラテンセックスカム',
                    'ko' => '라티나 섹스 캠',
                    'zh' => '拉丁直播',
                    'ar' => 'كام لاتيني',
                    'tr' => 'Latin Seks Kameraları',
                    'pt-BR' => 'Câmeras de Sexo Latinas',
                    'es-MX' => 'Cámaras de Sexo Latinas',
                ],
            ],
            [
                'title' => 'MILF Sex Cams',
                'slug' => 'milf',
                'type' => 'tag_category',
                'tags' => ['milf', 'mature', 'mom'],
                'sort_order' => 4,
                'translations' => [
                    'es' => 'Cámaras de Sexo MILF',
                    'fr' => 'Cams Sexe MILF',
                    'de' => 'MILF Sex Cams',
                    'pt' => 'Câmeras de Sexo MILF',
                    'it' => 'Cam Sesso MILF',
                    'nl' => 'MILF Sex Cams',
                    'pl' => 'Kamery Erotyczne MILF',
                    'ru' => 'MILF Секс Камеры',
                    'ja' => '熟女セックスカム',
                    'ko' => 'MILF 섹스 캠',
                    'zh' => '熟女直播',
                    'ar' => 'كام ميلف',
                    'tr' => 'MILF Seks Kameraları',
                    'pt-BR' => 'Câmeras de Sexo Coroa',
                    'es-MX' => 'Cámaras de Sexo MILF',
                ],
            ],
        ];

        foreach ($sections as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $section = HomepageSection::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // English translation
            HomepageSectionTranslation::updateOrCreate(
                ['homepage_section_id' => $section->id, 'locale' => 'en'],
                ['title' => $section->title]
            );

            // Other locale translations
            foreach ($translations as $locale => $title) {
                HomepageSectionTranslation::updateOrCreate(
                    ['homepage_section_id' => $section->id, 'locale' => $locale],
                    ['title' => $title]
                );
            }
        }
    }
}
