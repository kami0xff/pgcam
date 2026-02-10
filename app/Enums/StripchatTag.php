<?php

namespace App\Enums;

/**
 * Stripchat model tags organized by category.
 * Values are the URL slugs used on stripchat.com/girls/{slug}
 */
enum StripchatTag: string
{
    // ============== AGE ==============
    case TEEN = 'teens';
    case YOUNG = 'young';
    case MILF = 'milfs';
    case MATURE = 'mature';
    case GRANNY = 'grannies';

    // ============== ETHNICITY ==============
    case ARAB = 'arab';
    case ASIAN = 'asian';
    case EBONY = 'ebony';
    case INDIAN = 'indian';
    case LATINA = 'latin';
    case MIXED = 'mixed';
    case WHITE = 'white';

    // ============== BODY TYPE ==============
    case SKINNY = 'petite';
    case ATHLETIC = 'athletic';
    case MEDIUM = 'medium';
    case CURVY = 'curvy';
    case BBW = 'bbw';

    // ============== HAIR ==============
    case BLONDE = 'blondes';
    case BLACK_HAIR = 'black-hair';
    case BRUNETTE = 'brunettes';
    case REDHEAD = 'redheads';
    case COLORFUL_HAIR = 'colorful';

    // ============== BODY TRAITS ==============
    case BALD = 'balds';
    case BIG_ASS = 'big-ass';
    case BIG_CLIT = 'big-clit';
    case BIG_NIPPLES = 'big-nipples';
    case BIG_TITS = 'big-tits';
    case HAIRY_ARMPITS = 'hairy-armpits';
    case HAIRY_PUSSY = 'hairy';
    case SHAVEN = 'shaven';
    case SMALL_TITS = 'small-tits';
    case TRIMMED = 'trimmed';

    // ============== PRIVATE SHOW PRICING ==============
    case CHEAPEST_PRIVATES = 'cheapest-privates';
    case CHEAP_PRIVATES = 'cheap-privates';
    case MIDDLE_PRICED_PRIVATES = 'middle-priced-privates';
    case LUXURIOUS_PRIVATES = 'luxurious-privates';
    case CAM2CAM = 'cam2cam';
    case RECORDABLE_PRIVATES = 'recordable-privates';
    case SPY = 'spy';

    // ============== ACTIVITIES ==============
    case POSITION_69 = '69-position';
    case AHEGAO = 'ahegao';
    case ANAL = 'anal';
    case ANAL_TOYS = 'anal-toys';
    case ASS_TO_MOUTH = 'ass-to-mouth';
    case BLOWJOB = 'blowjob';
    case BUKKAKE = 'bukkake';
    case CAMEL_TOE = 'camel-toe';
    case COCK_RATING = 'cock-rating';
    case COSPLAY = 'cosplay';
    case COWGIRL = 'cowgirl';
    case CREAMPIE = 'creampie';
    case CUMSHOT = 'cumshot';
    case DEEPTHROAT = 'deepthroat';
    case DILDO_OR_VIBRATOR = 'dildo-or-vibrator';
    case DIRTY_TALK = 'dirty-talk';
    case DOGGY_STYLE = 'doggy-style';
    case DOUBLE_PENETRATION = 'double-penetration';
    case EROTIC_DANCE = 'erotic-dance';
    case FACESITTING = 'facesitting';
    case FACIAL = 'facial';
    case FINGERING = 'fingering';
    case FISTING = 'fisting';
    case FLASHING = 'flashing';
    case FOOTJOB = 'footjob';
    case FOURSOME = 'foursome';
    case FUCK_MACHINE = 'fuck-machine';
    case GAGGING = 'gagging';
    case GANGBANG = 'gang-bang';
    case GAPE = 'gape';
    case GLORY_HOLE = 'glory-hole';
    case HANDJOB = 'handjob';
    case HARDCORE = 'hardcore';
    case HUMILIATION = 'humiliation';
    case JOI = 'jerk-off-instruction';
    case MASSAGE = 'massage';
    case MASTURBATION = 'masturbation';
    case NIPPLE_TOYS = 'nipple-toys';
    case OIL_SHOW = 'oil-show';
    case ORGASM = 'orgasm';
    case PEGGING = 'pegging';
    case PUSSY_LICKING = 'pussy-licking';
    case ROLE_PLAY = 'role-play';
    case SEX_TOYS = 'sex-toys';
    case SEXTING = 'sexting';
    case SHOWER = 'shower';
    case SPANKING = 'spanking';
    case SQUIRT = 'squirt';
    case STRAPON = 'strapon';
    case STRIPTEASE = 'striptease';
    case SWING = 'swingers';
    case THREESOME = 'threesome';
    case TITTYFUCK = 'titty-fuck';
    case TOPLESS = 'topless';
    case TWERK = 'twerk';
    case UPSKIRT = 'upskirt';
    case YOGA = 'yoga';

    // ============== DEVICES ==============
    case INTERACTIVE_TOY = 'interactive-toys';
    case KIIROO = 'kiiroo';
    case LOVENSE = 'lovense';

    // ============== SUBCULTURES ==============
    case ANIME = 'anime';
    case CLUB = 'club';
    case E_GIRL = 'e-girl';
    case EMO = 'emo';
    case GAMER = 'gamers';
    case GLAMOUR = 'glamour';
    case GOTH = 'goth';
    case GYM_BABE = 'gym-babe';
    case HOUSEWIFE = 'housewives';
    case K_POP = 'k-pop';
    case NERD = 'nerds';
    case PUNK = 'punks';
    case QUEER = 'queer';
    case ROMANTIC = 'romantic';
    case STUDENT = 'student';
    case TOMBOY = 'tomboy';

    // ============== BROADCAST ==============
    case HD = 'hd';
    case MOBILE = 'mobile';
    case RECORDABLE_PUBLICS = 'recordable-publics';
    case VR = 'vr';

    // ============== SHOW TYPE ==============
    case ASMR = 'asmr';
    case COOKING = 'cooking';
    case FLIRTING = 'flirting';
    case GROUP_SEX = 'group-sex';
    case INTERRACIAL = 'interracial';
    case NEW_MODELS = 'new';
    case OFFICE = 'office';
    case OLD_AND_YOUNG = 'old-young';
    case OUTDOOR = 'outdoor';
    case PORNSTAR = 'pornstars';
    case POV = 'pov';
    case TICKET_SHOWS = 'ticket-and-group-shows';
    case VIDEO_GAMES = 'video-games';
    case VTUBER = 'vtubers';

    // ============== GENDER IDENTITY ==============
    case NON_BINARY = 'non-binary';

    // ============== ORIENTATION ==============
    case BISEXUAL = 'bisexuals';
    case LESBIAN = 'lesbians';
    case STRAIGHT = 'straight';

    // ============== COUNTRIES - NORTH AMERICA ==============
    case AMERICAN = 'american';
    case CANADIAN = 'canadian';
    case MEXICAN = 'mexican';

    // ============== COUNTRIES - SOUTH AMERICA ==============
    case ARGENTINIAN = 'argentinian';
    case BRAZILIAN = 'brazilian';
    case CHILEAN = 'chilean';
    case COLOMBIAN = 'colombian';
    case ECUADORIAN = 'ecuadorian';
    case PERUVIAN = 'peruvian';
    case URUGUAYAN = 'uruguayan';
    case VENEZUELAN = 'venezuelan';

    // ============== COUNTRIES - EUROPE ==============
    case AUSTRIAN = 'austrian';
    case BELGIAN = 'belgian';
    case BULGARIAN = 'bulgarian';
    case CROATIAN = 'croatian';
    case CZECH = 'czech';
    case DANISH = 'danish';
    case DUTCH = 'dutch';
    case ESTONIAN = 'estonian';
    case FINNISH = 'finnish';
    case FRENCH = 'french';
    case GEORGIAN = 'georgian';
    case GERMAN = 'german';
    case GREEK = 'greek';
    case HUNGARIAN = 'hungarian';
    case IRISH = 'irish';
    case ITALIAN = 'italian';
    case LATVIAN = 'latvian';
    case LITHUANIAN = 'lithuanian';
    case NORDIC = 'nordic';
    case NORWEGIAN = 'norwegian';
    case POLISH = 'polish';
    case PORTUGUESE = 'portuguese';
    case ROMANIAN = 'romanian';
    case SERBIAN = 'serbian';
    case SLOVAKIAN = 'slovakian';
    case SLOVENIAN = 'slovenian';
    case SPANISH = 'spanish';
    case SWEDISH = 'swedish';
    case SWISS = 'swiss';
    case UK = 'uk-models';
    case UKRAINIAN = 'ukrainian';

    // ============== COUNTRIES - ASIA & PACIFIC ==============
    case AUSTRALIAN = 'australian';
    case CHINESE = 'chinese';
    case JAPANESE = 'japanese';
    case KOREAN = 'korean';
    case MALAYSIAN = 'malaysian';
    case SRI_LANKAN = 'srilankan';
    case THAI = 'thai';
    case VIETNAMESE = 'vietnamese';

    // ============== COUNTRIES - AFRICA ==============
    case AFRICAN = 'african';
    case KENYAN = 'kenyan';
    case MALAGASY = 'malagasy';
    case NIGERIAN = 'nigerian';
    case SOUTH_AFRICAN = 'south-african';
    case UGANDAN = 'ugandan';
    case ZIMBABWEAN = 'zimbabwean';

    // ============== COUNTRIES - MIDDLE EAST ==============
    case ISRAELI = 'israeli';
    case TURKISH = 'turkish';

    // ============== LANGUAGES ==============
    case PORTUGUESE_SPEAKING = 'portuguese-speaking';
    case RUSSIAN_SPEAKING = 'russian';
    case SPANISH_SPEAKING = 'spanish-speaking';

    // ============== FETISHES & KINKS ==============
    case BDSM = 'bdsm';
    case CORSET = 'corset';
    case CUCKOLD = 'cuckold';
    case FOOT_FETISH = 'foot-fetish';
    case HEELS = 'heels';
    case JEANS = 'jeans';
    case LATEX = 'latex';
    case LEATHER = 'leather';
    case MISTRESS = 'mistresses';
    case NYLON = 'nylon';
    case PIERCING = 'piercings';
    case PREGNANT = 'pregnant';
    case SMOKING = 'smoking';
    case SPORT_GEAR = 'sport-gear';
    case TATTOOS = 'tattoos';

    /**
     * Get the display label for a tag
     */
    public function label(): string
    {
        return match ($this) {
            self::TEEN => 'Teen 18+',
            self::YOUNG => 'Young 22+',
            self::MILF => 'MILF',
            self::MATURE => 'Mature',
            self::GRANNY => 'Granny',
            self::ARAB => 'Arab',
            self::ASIAN => 'Asian',
            self::EBONY => 'Ebony',
            self::INDIAN => 'Indian',
            self::LATINA => 'Latina',
            self::MIXED => 'Mixed',
            self::WHITE => 'White',
            self::SKINNY => 'Skinny',
            self::ATHLETIC => 'Athletic',
            self::MEDIUM => 'Medium',
            self::CURVY => 'Curvy',
            self::BBW => 'BBW',
            self::BLONDE => 'Blonde',
            self::BLACK_HAIR => 'Black Hair',
            self::BRUNETTE => 'Brunette',
            self::REDHEAD => 'Redhead',
            self::COLORFUL_HAIR => 'Colorful Hair',
            self::BALD => 'Bald',
            self::BIG_ASS => 'Big Ass',
            self::BIG_CLIT => 'Big Clit',
            self::BIG_NIPPLES => 'Big Nipples',
            self::BIG_TITS => 'Big Tits',
            self::HAIRY_ARMPITS => 'Hairy Armpits',
            self::HAIRY_PUSSY => 'Hairy Pussy',
            self::SHAVEN => 'Shaven',
            self::SMALL_TITS => 'Small Tits',
            self::TRIMMED => 'Trimmed',
            self::CHEAPEST_PRIVATES => '8-12 tk/min',
            self::CHEAP_PRIVATES => '16-24 tk/min',
            self::MIDDLE_PRICED_PRIVATES => '32-60 tk/min',
            self::LUXURIOUS_PRIVATES => '90+ tk/min',
            self::CAM2CAM => 'Cam2Cam',
            self::RECORDABLE_PRIVATES => 'Recordable Privates',
            self::SPY => 'Spy on Shows',
            self::POSITION_69 => '69 Position',
            self::AHEGAO => 'Ahegao',
            self::ANAL => 'Anal',
            self::ANAL_TOYS => 'Anal Toys',
            self::ASS_TO_MOUTH => 'Ass to Mouth',
            self::BLOWJOB => 'Blowjob',
            self::BUKKAKE => 'Bukkake',
            self::CAMEL_TOE => 'Camel Toe',
            self::COCK_RATING => 'Cock Rating',
            self::COSPLAY => 'Cosplay',
            self::COWGIRL => 'Cowgirl',
            self::CREAMPIE => 'Creampie',
            self::CUMSHOT => 'Cumshot',
            self::DEEPTHROAT => 'Deepthroat',
            self::DILDO_OR_VIBRATOR => 'Dildo or Vibrator',
            self::DIRTY_TALK => 'Dirty Talk',
            self::DOGGY_STYLE => 'Doggy Style',
            self::DOUBLE_PENETRATION => 'Double Penetration',
            self::EROTIC_DANCE => 'Erotic Dance',
            self::FACESITTING => 'Facesitting',
            self::FACIAL => 'Facial',
            self::FINGERING => 'Fingering',
            self::FISTING => 'Fisting',
            self::FLASHING => 'Flashing',
            self::FOOTJOB => 'Footjob',
            self::FOURSOME => 'Foursome',
            self::FUCK_MACHINE => 'Fuck Machine',
            self::GAGGING => 'Gagging',
            self::GANGBANG => 'Gangbang',
            self::GAPE => 'Gape',
            self::GLORY_HOLE => 'Glory Hole',
            self::HANDJOB => 'Handjob',
            self::HARDCORE => 'Hardcore',
            self::HUMILIATION => 'Humiliation',
            self::JOI => 'Jerk-off Instruction',
            self::MASSAGE => 'Massage',
            self::MASTURBATION => 'Masturbation',
            self::NIPPLE_TOYS => 'Nipple Toys',
            self::OIL_SHOW => 'Oil Show',
            self::ORGASM => 'Orgasm',
            self::PEGGING => 'Pegging',
            self::PUSSY_LICKING => 'Pussy Licking',
            self::ROLE_PLAY => 'Role Play',
            self::SEX_TOYS => 'Sex Toys',
            self::SEXTING => 'Sexting',
            self::SHOWER => 'Shower',
            self::SPANKING => 'Spanking',
            self::SQUIRT => 'Squirt',
            self::STRAPON => 'Strapon',
            self::STRIPTEASE => 'Striptease',
            self::SWING => 'Swing',
            self::THREESOME => 'Threesome',
            self::TITTYFUCK => 'Tittyfuck',
            self::TOPLESS => 'Topless',
            self::TWERK => 'Twerk',
            self::UPSKIRT => 'Upskirt',
            self::YOGA => 'Yoga',
            self::INTERACTIVE_TOY => 'Interactive Toy',
            self::KIIROO => 'Kiiroo',
            self::LOVENSE => 'Lovense',
            self::ANIME => 'Anime',
            self::CLUB => 'Club',
            self::E_GIRL => 'E-girl',
            self::EMO => 'Emo',
            self::GAMER => 'Gamer',
            self::GLAMOUR => 'Glamour',
            self::GOTH => 'Goth',
            self::GYM_BABE => 'Gym Babe',
            self::HOUSEWIFE => 'Housewife',
            self::K_POP => 'K-pop',
            self::NERD => 'Nerd',
            self::PUNK => 'Punk',
            self::QUEER => 'Queer',
            self::ROMANTIC => 'Romantic',
            self::STUDENT => 'Student',
            self::TOMBOY => 'Tomboy',
            self::HD => 'HD',
            self::MOBILE => 'Mobile',
            self::RECORDABLE_PUBLICS => 'Recordable',
            self::VR => 'VR',
            self::ASMR => 'ASMR',
            self::COOKING => 'Cooking',
            self::FLIRTING => 'Flirting',
            self::GROUP_SEX => 'Group Sex',
            self::INTERRACIAL => 'Interracial',
            self::NEW_MODELS => 'New Models',
            self::OFFICE => 'Office',
            self::OLD_AND_YOUNG => 'Old & Young',
            self::OUTDOOR => 'Outdoor',
            self::PORNSTAR => 'Pornstar',
            self::POV => 'POV',
            self::TICKET_SHOWS => 'Ticket Shows',
            self::VIDEO_GAMES => 'Video Games',
            self::VTUBER => 'VTuber',
            self::NON_BINARY => 'Non-binary',
            self::BISEXUAL => 'Bisexual',
            self::LESBIAN => 'Lesbian',
            self::STRAIGHT => 'Straight',
            self::AMERICAN => 'American',
            self::CANADIAN => 'Canadian',
            self::MEXICAN => 'Mexican',
            self::ARGENTINIAN => 'Argentinian',
            self::BRAZILIAN => 'Brazilian',
            self::CHILEAN => 'Chilean',
            self::COLOMBIAN => 'Colombian',
            self::ECUADORIAN => 'Ecuadorian',
            self::PERUVIAN => 'Peruvian',
            self::URUGUAYAN => 'Uruguayan',
            self::VENEZUELAN => 'Venezuelan',
            self::AUSTRIAN => 'Austrian',
            self::BELGIAN => 'Belgian',
            self::BULGARIAN => 'Bulgarian',
            self::CROATIAN => 'Croatian',
            self::CZECH => 'Czech',
            self::DANISH => 'Danish',
            self::DUTCH => 'Dutch',
            self::ESTONIAN => 'Estonian',
            self::FINNISH => 'Finnish',
            self::FRENCH => 'French',
            self::GEORGIAN => 'Georgian',
            self::GERMAN => 'German',
            self::GREEK => 'Greek',
            self::HUNGARIAN => 'Hungarian',
            self::IRISH => 'Irish',
            self::ITALIAN => 'Italian',
            self::LATVIAN => 'Latvian',
            self::LITHUANIAN => 'Lithuanian',
            self::NORDIC => 'Nordic',
            self::NORWEGIAN => 'Norwegian',
            self::POLISH => 'Polish',
            self::PORTUGUESE => 'Portuguese',
            self::ROMANIAN => 'Romanian',
            self::SERBIAN => 'Serbian',
            self::SLOVAKIAN => 'Slovakian',
            self::SLOVENIAN => 'Slovenian',
            self::SPANISH => 'Spanish',
            self::SWEDISH => 'Swedish',
            self::SWISS => 'Swiss',
            self::UK => 'UK',
            self::UKRAINIAN => 'Ukrainian',
            self::AUSTRALIAN => 'Australian',
            self::CHINESE => 'Chinese',
            self::JAPANESE => 'Japanese',
            self::KOREAN => 'Korean',
            self::MALAYSIAN => 'Malaysian',
            self::SRI_LANKAN => 'Sri Lankan',
            self::THAI => 'Thai',
            self::VIETNAMESE => 'Vietnamese',
            self::AFRICAN => 'African',
            self::KENYAN => 'Kenyan',
            self::MALAGASY => 'Malagasy',
            self::NIGERIAN => 'Nigerian',
            self::SOUTH_AFRICAN => 'South African',
            self::UGANDAN => 'Ugandan',
            self::ZIMBABWEAN => 'Zimbabwean',
            self::ISRAELI => 'Israeli',
            self::TURKISH => 'Turkish',
            self::PORTUGUESE_SPEAKING => 'Portuguese Speaking',
            self::RUSSIAN_SPEAKING => 'Russian Speaking',
            self::SPANISH_SPEAKING => 'Spanish Speaking',
            self::BDSM => 'BDSM',
            self::CORSET => 'Corset',
            self::CUCKOLD => 'Cuckold',
            self::FOOT_FETISH => 'Foot Fetish',
            self::HEELS => 'Heels',
            self::JEANS => 'Jeans',
            self::LATEX => 'Latex',
            self::LEATHER => 'Leather',
            self::MISTRESS => 'Mistress',
            self::NYLON => 'Nylon',
            self::PIERCING => 'Piercing',
            self::PREGNANT => 'Pregnant',
            self::SMOKING => 'Smoking',
            self::SPORT_GEAR => 'Sport Gear',
            self::TATTOOS => 'Tattoos',
        };
    }

    /**
     * Get the category for a tag
     */
    public function category(): string
    {
        return match ($this) {
            self::TEEN, self::YOUNG, self::MILF, self::MATURE, self::GRANNY => 'Age',
            self::ARAB, self::ASIAN, self::EBONY, self::INDIAN, self::LATINA, self::MIXED, self::WHITE => 'Ethnicity',
            self::SKINNY, self::ATHLETIC, self::MEDIUM, self::CURVY, self::BBW => 'Body Type',
            self::BLONDE, self::BLACK_HAIR, self::BRUNETTE, self::REDHEAD, self::COLORFUL_HAIR => 'Hair',
            self::BALD, self::BIG_ASS, self::BIG_CLIT, self::BIG_NIPPLES, self::BIG_TITS,
            self::HAIRY_ARMPITS, self::HAIRY_PUSSY, self::SHAVEN, self::SMALL_TITS, self::TRIMMED => 'Body Traits',
            self::CHEAPEST_PRIVATES, self::CHEAP_PRIVATES, self::MIDDLE_PRICED_PRIVATES,
            self::LUXURIOUS_PRIVATES, self::CAM2CAM, self::RECORDABLE_PRIVATES, self::SPY => 'Private Show',
            self::POSITION_69, self::AHEGAO, self::ANAL, self::ANAL_TOYS, self::ASS_TO_MOUTH,
            self::BLOWJOB, self::BUKKAKE, self::CAMEL_TOE, self::COCK_RATING, self::COSPLAY,
            self::COWGIRL, self::CREAMPIE, self::CUMSHOT, self::DEEPTHROAT, self::DILDO_OR_VIBRATOR,
            self::DIRTY_TALK, self::DOGGY_STYLE, self::DOUBLE_PENETRATION, self::EROTIC_DANCE,
            self::FACESITTING, self::FACIAL, self::FINGERING, self::FISTING, self::FLASHING,
            self::FOOTJOB, self::FOURSOME, self::FUCK_MACHINE, self::GAGGING, self::GANGBANG,
            self::GAPE, self::GLORY_HOLE, self::HANDJOB, self::HARDCORE, self::HUMILIATION,
            self::JOI, self::MASSAGE, self::MASTURBATION, self::NIPPLE_TOYS, self::OIL_SHOW,
            self::ORGASM, self::PEGGING, self::PUSSY_LICKING, self::ROLE_PLAY, self::SEX_TOYS,
            self::SEXTING, self::SHOWER, self::SPANKING, self::SQUIRT, self::STRAPON, self::STRIPTEASE,
            self::SWING, self::THREESOME, self::TITTYFUCK, self::TOPLESS, self::TWERK, self::UPSKIRT, self::YOGA => 'Activities',
            self::INTERACTIVE_TOY, self::KIIROO, self::LOVENSE => 'Devices',
            self::ANIME, self::CLUB, self::E_GIRL, self::EMO, self::GAMER, self::GLAMOUR, self::GOTH,
            self::GYM_BABE, self::HOUSEWIFE, self::K_POP, self::NERD, self::PUNK, self::QUEER,
            self::ROMANTIC, self::STUDENT, self::TOMBOY => 'Subcultures',
            self::HD, self::MOBILE, self::RECORDABLE_PUBLICS, self::VR => 'Broadcast',
            self::ASMR, self::COOKING, self::FLIRTING, self::GROUP_SEX, self::INTERRACIAL,
            self::NEW_MODELS, self::OFFICE, self::OLD_AND_YOUNG, self::OUTDOOR, self::PORNSTAR,
            self::POV, self::TICKET_SHOWS, self::VIDEO_GAMES, self::VTUBER => 'Show Type',
            self::NON_BINARY => 'Gender Identity',
            self::BISEXUAL, self::LESBIAN, self::STRAIGHT => 'Orientation',
            self::AMERICAN, self::CANADIAN, self::MEXICAN, self::ARGENTINIAN, self::BRAZILIAN,
            self::CHILEAN, self::COLOMBIAN, self::ECUADORIAN, self::PERUVIAN, self::URUGUAYAN,
            self::VENEZUELAN, self::AUSTRIAN, self::BELGIAN, self::BULGARIAN, self::CROATIAN,
            self::CZECH, self::DANISH, self::DUTCH, self::ESTONIAN, self::FINNISH, self::FRENCH,
            self::GEORGIAN, self::GERMAN, self::GREEK, self::HUNGARIAN, self::IRISH, self::ITALIAN,
            self::LATVIAN, self::LITHUANIAN, self::NORDIC, self::NORWEGIAN, self::POLISH,
            self::PORTUGUESE, self::ROMANIAN, self::SERBIAN, self::SLOVAKIAN, self::SLOVENIAN,
            self::SPANISH, self::SWEDISH, self::SWISS, self::UK, self::UKRAINIAN, self::AUSTRALIAN,
            self::CHINESE, self::JAPANESE, self::KOREAN, self::MALAYSIAN, self::SRI_LANKAN,
            self::THAI, self::VIETNAMESE, self::AFRICAN, self::KENYAN, self::MALAGASY, self::NIGERIAN,
            self::SOUTH_AFRICAN, self::UGANDAN, self::ZIMBABWEAN, self::ISRAELI, self::TURKISH => 'Countries',
            self::PORTUGUESE_SPEAKING, self::RUSSIAN_SPEAKING, self::SPANISH_SPEAKING => 'Languages',
            self::BDSM, self::CORSET, self::CUCKOLD, self::FOOT_FETISH, self::HEELS, self::JEANS,
            self::LATEX, self::LEATHER, self::MISTRESS, self::NYLON, self::PIERCING, self::PREGNANT,
            self::SMOKING, self::SPORT_GEAR, self::TATTOOS => 'Fetishes & Kinks',
        };
    }

    /**
     * Get the Stripchat URL for this tag
     */
    public function url(): string
    {
        return "https://stripchat.com/girls/{$this->value}";
    }

    /**
     * Get all tags grouped by category
     */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::cases() as $tag) {
            $category = $tag->category();
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $tag;
        }
        return $grouped;
    }

    /**
     * Get tags by category
     */
    public static function byCategory(string $category): array
    {
        return array_filter(self::cases(), fn($tag) => $tag->category() === $category);
    }

    /**
     * Try to find a tag by its slug value
     */
    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $tag) {
            if ($tag->value === $slug) {
                return $tag;
            }
        }
        return null;
    }
}
