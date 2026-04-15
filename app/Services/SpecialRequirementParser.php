<?php

namespace App\Services;

class SpecialRequirementParser
{
    /**
     * Parse special_requirement (non-AI dictionary) -> tags + facts + matches trace.
     * Output:
     * - tags: string[]
     * - facts: associative array (bool/int/string)
     * - matches: array of {keyword, tag, note}
     */
    public function parse(string $text): array
    {
        $raw = strtolower(trim($text));
        $raw = preg_replace('/\s+/', ' ', $raw ?? '') ?: '';

        // kalau kosong
        if ($raw === '') {
            return [
                'tags' => [],
                'facts' => [],
                'matches' => [],
            ];
        }

        // Dictionary: keyword/alias -> tag
        // Kamu bisa tambah kapan aja tanpa ubah engine logic.
        $dict = [
            // audio brands
            'jbl' => 'audio_brand_jbl',
            'shure' => 'audio_brand_shure',
            'sennheiser' => 'audio_brand_sennheiser',

            // instruments
            'drum' => 'needs_drum_set',
            'drummer' => 'needs_drum_set',
            'keyboard' => 'needs_keyboard',
            'piano' => 'needs_keyboard',
            'gitar' => 'needs_guitar',

            // stage / misc
            'podium' => 'needs_podium',
            'lectern' => 'needs_podium',
            'kursi' => 'needs_chairs',
            'chair' => 'needs_chairs',
            'tenda' => 'needs_tent',
            'tent' => 'needs_tent',

            // livestream / video
            'live' => 'needs_livestream',
            'livestream' => 'needs_livestream',
            'streaming' => 'needs_livestream',

            // outdoor hints
            'outdoor' => 'is_outdoor',
            'lapangan' => 'is_outdoor',
            'garden' => 'is_outdoor',
        ];

        $tags = [];
        $facts = [];
        $matches = [];

        foreach ($dict as $kw => $tag) {
            if ($kw === '') continue;

            // simple contains matching
            if (str_contains($raw, $kw)) {
                if (!in_array($tag, $tags, true)) $tags[] = $tag;

                $matches[] = [
                    'keyword' => $kw,
                    'tag' => $tag,
                    'note' => "matched by contains()",
                ];
            }
        }

        // facts tambahan (contoh)
        $facts['has_special_requirement'] = true;
        $facts['special_raw'] = $raw;
        $facts['tag_count'] = count($tags);

        // convenience facts
        $facts['needs_drum_set'] = in_array('needs_drum_set', $tags, true);
        $facts['needs_livestream'] = in_array('needs_livestream', $tags, true);
        $facts['audio_brand'] = $this->detectAudioBrand($tags);

        return [
            'tags' => $tags,
            'facts' => $facts,
            'matches' => $matches,
        ];
    }

    private function detectAudioBrand(array $tags): ?string
    {
        foreach (['audio_brand_jbl','audio_brand_shure','audio_brand_sennheiser'] as $t) {
            if (in_array($t, $tags, true)) return $t;
        }
        return null;
    }
}