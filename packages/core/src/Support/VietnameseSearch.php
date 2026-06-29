<?php

namespace Quochao56\Core\Support;

use Illuminate\Database\Eloquent\Builder;
use Normalizer;

/**
 * Helper tìm kiếm không dấu tiếng Việt dùng MySQL 8.0+
 * Sử dụng COLLATE utf8mb4_0900_ai_ci (accent-insensitive, case-insensitive)
 */
class VietnameseSearch
{
    /**
     * Bảng thay thế ký tự có dấu → không dấu (tiếng Việt đầy đủ).
     * Bao gồm cả đ/Đ vì Unicode không decompose chúng tự động.
     */
    private const VI_MAP = [
        'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a',
        'ă'=>'a','ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'ấ'=>'a','ầ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a',
        'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
        'ế'=>'e','ề'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
        'ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
        'ơ'=>'o','ớ'=>'o','ờ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ố'=>'o','ồ'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o',
        'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
        'ư'=>'u','ứ'=>'u','ừ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
        'đ'=>'d',
        'À'=>'a','Á'=>'a','Â'=>'a','Ã'=>'a','Ä'=>'a','Å'=>'a',
        'Ă'=>'a','Ắ'=>'a','Ặ'=>'a','Ằ'=>'a','Ẳ'=>'a','Ẵ'=>'a',
        'Ấ'=>'a','Ầ'=>'a','Ậ'=>'a','Ẩ'=>'a','Ẫ'=>'a',
        'È'=>'e','É'=>'e','Ê'=>'e','Ë'=>'e',
        'Ế'=>'e','Ề'=>'e','Ệ'=>'e','Ể'=>'e','Ễ'=>'e',
        'Ì'=>'i','Í'=>'i','Î'=>'i','Ï'=>'i',
        'Ò'=>'o','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ö'=>'o',
        'Ơ'=>'o','Ớ'=>'o','Ờ'=>'o','Ợ'=>'o','Ở'=>'o','Ỡ'=>'o',
        'Ố'=>'o','Ồ'=>'o','Ộ'=>'o','Ổ'=>'o','Ỗ'=>'o',
        'Ù'=>'u','Ú'=>'u','Û'=>'u','Ü'=>'u',
        'Ư'=>'u','Ứ'=>'u','Ừ'=>'u','Ự'=>'u','Ử'=>'u','Ữ'=>'u',
        'Ỳ'=>'y','Ý'=>'y','Ỵ'=>'y','Ỷ'=>'y','Ỹ'=>'y',
        'Đ'=>'d',
        // Phụ âm khác
        'ñ'=>'n','Ñ'=>'n','ç'=>'c','Ç'=>'c',
    ];

    /**
     * Xóa dấu tiếng Việt và chuyển thành chữ thường.
     * "Học cụ" → "hoc cu", "Đèn" → "den"
     */
    public static function removeDiacritics(string $text): string
    {
        // Bước 1: thay thế các ký tự Việt không decompose được (đ, Đ, v.v.)
        $text = strtr($text, self::VI_MAP);

        // Bước 2: dùng Unicode NFD + strip combining characters cho phần còn lại
        if (class_exists(Normalizer::class)) {
            $nfd = Normalizer::normalize($text, Normalizer::FORM_D);
            if ($nfd !== false) {
                $text = preg_replace('/[\x{0300}-\x{036f}]/u', '', $nfd) ?? $text;
            }
        }

        return mb_strtolower($text);
    }

    /**
     * Áp dụng search không dấu cho một hoặc nhiều cột (MySQL query).
     *
     * @param Builder  $query
     * @param string   $search
     * @param string[] $columns  Tên cột (chỉ hỗ trợ cột local, không hỗ trợ dot-notation)
     */
    public static function apply(Builder $query, string $search, array $columns): Builder
    {
        $term = '%' . trim($search) . '%';

        return $query->where(function (Builder $q) use ($term, $columns) {
            foreach ($columns as $i => $column) {
                $method = $i === 0 ? 'whereRaw' : 'orWhereRaw';

                if (str_contains($column, '.')) {
                    continue;
                }

                $q->{$method}("`{$column}` LIKE ? COLLATE utf8mb4_0900_ai_ci", [$term]);
            }
        });
    }

    /**
     * Tạo closure dùng cho ->searchable(query: ...) của Filament TextColumn.
     *
     * @param string $column  Tên cột trong DB (không có dot-notation)
     */
    public static function column(string $column): \Closure
    {
        return function (Builder $query, string $search) use ($column): Builder {
            return $query->orWhereRaw(
                "`{$column}` LIKE ? COLLATE utf8mb4_0900_ai_ci",
                ['%' . trim($search) . '%']
            );
        };
    }
}
