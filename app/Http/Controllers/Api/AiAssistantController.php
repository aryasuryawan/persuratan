<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\Setting;
use App\Services\AiAssistantService;
use App\Services\AiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    public function ask(Request $request, AiAssistantService $ai): JsonResponse
    {
        if (Setting::get('ai_enabled') !== '1') {
            return response()->json([
                'message' => 'Fitur AI Assistant sedang dinonaktifkan oleh admin.',
            ], 403);
        }

        if (! $request->user()->hasPermission('ai-assistant.use')) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk menggunakan AI Assistant.',
            ], 403);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'history' => ['nullable', 'array', 'max:6'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $result = $ai->ask(
                $request->user(),
                $validated['question'],
                $validated['history'] ?? []
            );

            AiChatLog::create([
                'user_id' => $request->user()->id,
                'question' => $validated['question'],
                'model' => $result['model'],
                'modules_accessed' => $result['modules_accessed'],
                'results_count' => $result['results_count'],
                'success' => true,
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'cost_estimate' => $result['cost_estimate'],
            ]);

            return response()->json([
                'answer' => $result['answer'],
                'links' => $result['links'],
                'meta' => [
                    'model' => $result['model'],
                    'modules_accessed' => $result['modules_accessed'],
                ],
            ]);
        } catch (AiException $e) {
            AiChatLog::create([
                'user_id' => $request->user()->id,
                'question' => $validated['question'],
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            Log::error('AI ask unexpected error', ['error' => $e->getMessage()]);

            AiChatLog::create([
                'user_id' => $request->user()->id,
                'question' => $validated['question'],
                'success' => false,
                'error_message' => 'Kesalahan internal.',
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan internal. Silakan coba lagi.',
            ], 500);
        }
    }
}
