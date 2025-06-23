<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LessonChunkController extends Controller
{
    public function handleChunk(Request $request)
        {
            $key = $request->header('X-Internal-API-Key');
        if ($key !== env('EDTRY_INTERNAL_API_KEY')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validatedData = $request->validate([
            'course_id' => 'required|integer',
            'lesson_id' => 'required|integer',
            'title' => 'required|string',
            'text' => 'required|string',
            'type' => 'required|string|in:created,updated,deleted',
            'chunks' => 'required|array',
        ]);

        $type = $validatedData['type'];
        $courseId = $validatedData['course_id'];
        $lessonId = $validatedData['lesson_id'];

       // Log::info('Received chunked data:', $validatedData);

        // Handle deletion
        if ($type === 'deleted') {
            $deleteResponse = Http::withHeaders([
                "api-key" => env('QDRANT_API_KEY'),
            ])->retry(5, 3000)->post(
                env('QDRANT_HOST') . '/collections/' . env('QDRANT_COLLECTION') . '/points/delete',
                [
                    'filter' => [
                        'must' => [
                            ['key' => 'lesson_id', 'match' => ['value' => $lessonId]]
                        ]
                    ]
                ]
            );

            if ($deleteResponse->failed()) {
                Log::error('Failed to delete from Qdrant', ['response' => $deleteResponse->body()]);
                return response()->json(['status' => 'error', 'message' => 'Failed to delete from Qdrant'], 500);
            }

            return response()->json(['status' => 'success', 'message' => 'Deleted lesson chunks from Qdrant']);
        }

        // Extract chunk texts
        $chunkTexts = array_map(fn($chunk) => $chunk['text'], $validatedData['chunks']);

        // Send to embedding API
        $embeddingResponse = Http::retry(5, 3000)->withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . env('TOGETHER_API_KEY')
        ])->post(env('TOGETHER_API_URL').'/embeddings', [
            'model' => 'BAAI/bge-base-en-v1.5',
            'input' => $chunkTexts
        ]);

       // Log::info('Embedding API response:', $embeddingResponse->json());

        if ($embeddingResponse->failed()) {
            Log::error('Embedding API call failed', ['error' => $embeddingResponse->body()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to embed data.',
                'details' => $embeddingResponse->json()
            ], 500);
        }

        $embeddingData = $embeddingResponse->json()['data'];

        // Prepare data for Qdrant
        $qdrantPoints = [];
        foreach ($embeddingData as $i => $embeddingEntry) {
            $chunk = $validatedData['chunks'][$i];
            $qdrantPoints[] = [
                'id' => (string) Str::uuid(), // generate a valid UUID for Qdrant
                'vector' => $embeddingEntry['embedding'],
                'payload' => [
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                    'chunk_index' => $chunk['chunk_index'],
                    'title' => $validatedData['title'],
                    'text' => $chunk['text'],
                    'custom_id' => "lesson_{$lessonId}_chunk_{$chunk['chunk_index']}", 
                ]
            ];
        }

      //  Log::info('Prepared Qdrant points:', array_slice($qdrantPoints, 0, 2)); 

        // Retry Qdrant insert up to 5 times with 3s delay
        $qdrantResponse = Http::withHeaders([
            "api-key" => env('QDRANT_API_KEY'),
        ])->retry(5, 3000)->put(
            env('QDRANT_HOST') . '/collections/' . env('QDRANT_COLLECTION') . '/points',
            ['points' => $qdrantPoints]
        );

        if ($qdrantResponse->failed()) {
            Log::error('Failed to insert into Qdrant', [
                'status' => $qdrantResponse->status(),
                'response_json' => $qdrantResponse->json(),
                'raw_body' => $qdrantResponse->body(),
                'sent_points_sample' => array_slice($qdrantPoints, 0, 2),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to insert into Qdrant',
                'details' => $qdrantResponse->json()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Chunks embedded and sent to Qdrant.',
            'inserted' => count($qdrantPoints)
        ]);
    }

    public function AskAI(Request $request)
    {
        $validatedData = $request->validate([
            'query' => 'required|string',
            'course_id' => 'required|integer',
            'lesson_id' => 'required|integer',
        ]);

        $query = $validatedData['query'];
        $courseId = $validatedData['course_id'];
        $lessonId = $validatedData['lesson_id'];

    Log::info('Received user query:', $validatedData);

        // Send to embedding API
        $embeddingResponse = Http::retry(5, 3000)->withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'Authorization' => 'Bearer ' . env('TOGETHER_API_KEY', 'default')
        ])->post(env('TOGETHER_API_URL').'/embeddings', [
            'model' => 'BAAI/bge-base-en-v1.5',
            'input' => [$query]
        ]);

       // Log::info('Embedding API response:', $embeddingResponse->json());

        if ($embeddingResponse->failed()) {
            Log::error('Embedding API call failed', ['error' => $embeddingResponse->body()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to embed query.',
                'details' => $embeddingResponse->json()
            ], 500);
        }

        $embeddingData = $embeddingResponse->json()['data'][0]['embedding'];

        // Query Qdrant for similar chunks
        // Ensure the IDs are integers
        $courseId = (int) $courseId;
        $lessonId = (int) $lessonId;

        $qdrantResponse = Http::withHeaders([
            "api-key" => env('QDRANT_API_KEY'),
        ])->retry(5, 3000)->post(
            env('QDRANT_HOST') . '/collections/' . env('QDRANT_COLLECTION') . '/points/search',
            [
                'vector' => $embeddingData,
                'filter' => [
                    'must' => [
                        ['key' => 'course_id', 'match' => ['value' => $courseId]],
                        ['key' => 'lesson_id', 'match' => ['value' => $lessonId]]
                    ]
                ],
                'limit' => 5,
                'with_payload' => true,
                'with_vector' => false,
            ]
        );

        Log::info('Qdrant search response:', $qdrantResponse->json());

        if ($qdrantResponse->failed()) {
            Log::error('Failed to search in Qdrant', [
                'status' => $qdrantResponse->status(),
                'response_json' => $qdrantResponse->json(),
                'raw_body' => $qdrantResponse->body(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search in Qdrant',
                'details' => $qdrantResponse->json()
            ], 500);
        }

        //Query for best match suggested next lesson
        $qdrantSuggestNextLessonResponse = Http::withHeaders([
            "api-key" => env('QDRANT_API_KEY'),
        ])->retry(5, 3000)->post(
            env('QDRANT_HOST') . '/collections/' . env('QDRANT_COLLECTION') . '/points/search',
            [
                'vector' => $embeddingData,
                'filter' => [
                    'must' => [
                        ['key' => 'course_id', 'match' => ['value' => (int) $courseId]]
                    ],
                    'must_not' => [
                        ['key' => 'lesson_id', 'match' => ['value' => (int) $lessonId]]
                    ]
                ],
                'limit' => 1,
                'with_payload' => true,
                'with_vector' => false,
            ]
        );
        Log::info('Qdrant suggest next lesson response:', $qdrantSuggestNextLessonResponse->json());

        $LLMPrompt = "You are Edtry – an educational assistant that embodies Education Through Trying. Your job is to help students learn by engaging with course materials in a friendly, conversational way, like a thoughtful teacher – not a bot. Core Principles: Encouragement: Effort leads to growth. Accuracy: Only answer using the provided context. Honesty: If unsure, say so clearly. Relevance: Suggest next steps only when they naturally fit the question. Response Rules: 0. Always speak conversationally, like a helpful teacher. If the answer is in the context: – Start with a direct answer. – Back it up using the lesson. – End with a natural, encouraging sentence. If the answer is not in the context: – Say it’s not covered, like a teacher would. – Never guess or add outside info. If a suggested lesson exists(which is just a random lesson that may match the user's prompt): – Mention it only if it fits the student’s prompt. – Introduce it casually, not as a “suggestion.” Keep all responses under 500 tokens.";
    
            $chunks = $qdrantResponse->json()['result'] ?? [];
            $suggestedNextLesson = $qdrantSuggestNextLessonResponse->json()['result'][0]['payload']['title'] ?? 'No suggestion available';
    
            // Prepare context for LLM
            $context = '';
            foreach ($chunks as $chunk) {
                $context .= "Chunk: {$chunk['payload']['text']}\n";
            }
    
            // Prepare final prompt
            $finalPrompt = "{$LLMPrompt}\n\nContext:\n{$context}\n\nStudent Prompt: {$query} and suggest this lesson if it makes sense base on the prompt: {$suggestedNextLesson}";
    
            Log::info('Final prompt for LLM:', ['prompt' => $finalPrompt]);
    
            // Call LLM API
            $llmResponse = Http::retry(5, 3000)->withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . env('TOGETHER_API_KEY')
            ])->post(env('TOGETHER_API_URL').'/chat/completions', [
                'model' => 'deepseek-ai/DeepSeek-R1-Distill-Llama-70B-free',
                'messages' => [
                    ['role' => 'system', 'content' => $LLMPrompt],
                    ['role' => 'user', 'content' => $finalPrompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);
    
            if ($llmResponse->failed()) {
                Log::error('LLM API call failed', ['error' => $llmResponse->body()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to get AI response.',
                    'details' => $llmResponse->json()
                ], 500);
            }

            $llmResponseData = $llmResponse->json();
            
            return response()->json([
                'status' => 'success',
                'answer' => $llmResponseData,
            ]);
        }
    }

