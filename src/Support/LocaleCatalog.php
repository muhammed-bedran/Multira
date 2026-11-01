<?php

namespace Melbedran\Multira\Support;

class LocaleCatalog
{
    /**
     * @return array<string, array{name: string, native: string, script: string, regional: string, dir: string}>
     */
    public static function all(): array
    {
        return [
            'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'script' => 'Arab', 'regional' => 'ar_AE', 'dir' => 'rtl'],
            'en' => ['name' => 'English', 'native' => 'English', 'script' => 'Latn', 'regional' => 'en_GB', 'dir' => 'ltr'],
            'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'script' => 'Latn', 'regional' => 'tr_TR', 'dir' => 'ltr'],
            'fr' => ['name' => 'French', 'native' => 'Français', 'script' => 'Latn', 'regional' => 'fr_FR', 'dir' => 'ltr'],
            'de' => ['name' => 'German', 'native' => 'Deutsch', 'script' => 'Latn', 'regional' => 'de_DE', 'dir' => 'ltr'],
            'es' => ['name' => 'Spanish', 'native' => 'Español', 'script' => 'Latn', 'regional' => 'es_ES', 'dir' => 'ltr'],
            'it' => ['name' => 'Italian', 'native' => 'Italiano', 'script' => 'Latn', 'regional' => 'it_IT', 'dir' => 'ltr'],
            'pt' => ['name' => 'Portuguese', 'native' => 'Português', 'script' => 'Latn', 'regional' => 'pt_PT', 'dir' => 'ltr'],
            'pt-BR' => ['name' => 'Brazilian Portuguese', 'native' => 'Português do Brasil', 'script' => 'Latn', 'regional' => 'pt_BR', 'dir' => 'ltr'],
            'nl' => ['name' => 'Dutch', 'native' => 'Nederlands', 'script' => 'Latn', 'regional' => 'nl_NL', 'dir' => 'ltr'],
            'ru' => ['name' => 'Russian', 'native' => 'Русский', 'script' => 'Cyrl', 'regional' => 'ru_RU', 'dir' => 'ltr'],
            'uk' => ['name' => 'Ukrainian', 'native' => 'Українська', 'script' => 'Cyrl', 'regional' => 'uk_UA', 'dir' => 'ltr'],
            'pl' => ['name' => 'Polish', 'native' => 'Polski', 'script' => 'Latn', 'regional' => 'pl_PL', 'dir' => 'ltr'],
            'ro' => ['name' => 'Romanian', 'native' => 'Română', 'script' => 'Latn', 'regional' => 'ro_RO', 'dir' => 'ltr'],
            'cs' => ['name' => 'Czech', 'native' => 'Čeština', 'script' => 'Latn', 'regional' => 'cs_CZ', 'dir' => 'ltr'],
            'sv' => ['name' => 'Swedish', 'native' => 'Svenska', 'script' => 'Latn', 'regional' => 'sv_SE', 'dir' => 'ltr'],
            'da' => ['name' => 'Danish', 'native' => 'Dansk', 'script' => 'Latn', 'regional' => 'da_DK', 'dir' => 'ltr'],
            'fi' => ['name' => 'Finnish', 'native' => 'Suomi', 'script' => 'Latn', 'regional' => 'fi_FI', 'dir' => 'ltr'],
            'no' => ['name' => 'Norwegian', 'native' => 'Norsk', 'script' => 'Latn', 'regional' => 'nb_NO', 'dir' => 'ltr'],
            'el' => ['name' => 'Greek', 'native' => 'Ελληνικά', 'script' => 'Grek', 'regional' => 'el_GR', 'dir' => 'ltr'],
            'hu' => ['name' => 'Hungarian', 'native' => 'Magyar', 'script' => 'Latn', 'regional' => 'hu_HU', 'dir' => 'ltr'],
            'bg' => ['name' => 'Bulgarian', 'native' => 'Български', 'script' => 'Cyrl', 'regional' => 'bg_BG', 'dir' => 'ltr'],
            'hr' => ['name' => 'Croatian', 'native' => 'Hrvatski', 'script' => 'Latn', 'regional' => 'hr_HR', 'dir' => 'ltr'],
            'sk' => ['name' => 'Slovak', 'native' => 'Slovenčina', 'script' => 'Latn', 'regional' => 'sk_SK', 'dir' => 'ltr'],
            'sl' => ['name' => 'Slovene', 'native' => 'Slovenščina', 'script' => 'Latn', 'regional' => 'sl_SI', 'dir' => 'ltr'],
            'sr' => ['name' => 'Serbian', 'native' => 'Српски', 'script' => 'Cyrl', 'regional' => 'sr_RS', 'dir' => 'ltr'],
            'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'script' => 'Arab', 'regional' => 'fa_IR', 'dir' => 'rtl'],
            'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'script' => 'Arab', 'regional' => 'ur_PK', 'dir' => 'rtl'],
            'he' => ['name' => 'Hebrew', 'native' => 'עברית', 'script' => 'Hebr', 'regional' => 'he_IL', 'dir' => 'rtl'],
            'ku' => ['name' => 'Kurdish', 'native' => 'کوردی', 'script' => 'Arab', 'regional' => 'ku_TR', 'dir' => 'rtl'],
            'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'script' => 'Deva', 'regional' => 'hi_IN', 'dir' => 'ltr'],
            'bn' => ['name' => 'Bengali', 'native' => 'বাংলা', 'script' => 'Beng', 'regional' => 'bn_BD', 'dir' => 'ltr'],
            'ja' => ['name' => 'Japanese', 'native' => '日本語', 'script' => 'Jpan', 'regional' => 'ja_JP', 'dir' => 'ltr'],
            'zh' => ['name' => 'Chinese (Simplified)', 'native' => '简体中文', 'script' => 'Hans', 'regional' => 'zh_CN', 'dir' => 'ltr'],
            'zh-Hant' => ['name' => 'Chinese (Traditional)', 'native' => '繁體中文', 'script' => 'Hant', 'regional' => 'zh_TW', 'dir' => 'ltr'],
            'ko' => ['name' => 'Korean', 'native' => '한국어', 'script' => 'Hang', 'regional' => 'ko_KR', 'dir' => 'ltr'],
            'th' => ['name' => 'Thai', 'native' => 'ไทย', 'script' => 'Thai', 'regional' => 'th_TH', 'dir' => 'ltr'],
            'vi' => ['name' => 'Vietnamese', 'native' => 'Tiếng Việt', 'script' => 'Latn', 'regional' => 'vi_VN', 'dir' => 'ltr'],
            'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'script' => 'Latn', 'regional' => 'id_ID', 'dir' => 'ltr'],
            'ms' => ['name' => 'Malay', 'native' => 'Bahasa Melayu', 'script' => 'Latn', 'regional' => 'ms_MY', 'dir' => 'ltr'],
            'sw' => ['name' => 'Swahili', 'native' => 'Kiswahili', 'script' => 'Latn', 'regional' => 'sw_KE', 'dir' => 'ltr'],
            'am' => ['name' => 'Amharic', 'native' => 'አማርኛ', 'script' => 'Ethi', 'regional' => 'am_ET', 'dir' => 'ltr'],
            'ha' => ['name' => 'Hausa', 'native' => 'Hausa', 'script' => 'Latn', 'regional' => 'ha_NG', 'dir' => 'ltr'],
            'az' => ['name' => 'Azerbaijani', 'native' => 'Azərbaycan', 'script' => 'Latn', 'regional' => 'az_AZ', 'dir' => 'ltr'],
            'ka' => ['name' => 'Georgian', 'native' => 'ქართული', 'script' => 'Geor', 'regional' => 'ka_GE', 'dir' => 'ltr'],
            'hy' => ['name' => 'Armenian', 'native' => 'Հայերեն', 'script' => 'Armn', 'regional' => 'hy_AM', 'dir' => 'ltr'],
            'kk' => ['name' => 'Kazakh', 'native' => 'Қазақ тілі', 'script' => 'Cyrl', 'regional' => 'kk_KZ', 'dir' => 'ltr'],
            'uz' => ['name' => 'Uzbek', 'native' => 'Oʻzbekcha', 'script' => 'Latn', 'regional' => 'uz_UZ', 'dir' => 'ltr'],
        ];
    }

    /**
     * @return array{name: string, native: string, script: string, regional: string, dir: string}
     */
    public static function get(string $locale): array
    {
        return self::all()[$locale] ?? [
            'name' => $locale,
            'native' => $locale,
            'script' => 'Latn',
            'regional' => $locale,
            'dir' => 'ltr',
        ];
    }
}
