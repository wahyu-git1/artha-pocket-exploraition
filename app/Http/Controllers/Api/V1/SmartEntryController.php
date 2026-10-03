<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Models\UserCategoryPreference;
use App\Services\AiSmartEntryParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartEntryController extends BaseController
{
    public function parse(Request $request, AiSmartEntryParser $parser): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:200'
        ]);

        try {
            $result = $parser->parse($request->text, $request->user()->id);
            return $this->success($result);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'PARSE_NO_AMOUNT') {
                return $this->error(
                    ErrorCode::PARSE_NO_AMOUNT,
                    'Nominal tidak ditemukan dalam teks.',
                    [],
                    422
                );
            }

            return $this->error(ErrorCode::SERVER_ERROR, 'Gagal memproses teks.', [
                'debug' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function feedback(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|max:60',
            'category_id' => 'required|uuid|exists:categories,id'
        ]);

        $pref = UserCategoryPreference::firstOrNew([
            'user_id' => $request->user()->id,
            'keyword' => strtolower(trim($request->keyword)),
        ]);

        $pref->category_id = $request->category_id;
        $pref->hit_count = $pref->exists ? $pref->hit_count + 1 : 1;
        $pref->save();

        return $this->success([
            'id' => $pref->id,
            'keyword' => $pref->keyword,
            'category_id' => $pref->category_id,
            'hit_count' => $pref->hit_count
        ]);
    }
}
