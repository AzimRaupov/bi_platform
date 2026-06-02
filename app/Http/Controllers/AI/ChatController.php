<?php

namespace App\Http\Controllers\AI;

use App\Helpers\Ai\AIService;
use App\Helpers\Dashboard\DashboardGenerator;
use App\Helpers\DataHandlers\TableDataHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\AI\StoreChatRequest;
use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function store(StoreChatRequest $request)
    {
        $user = Auth::user();

        $message = null;
        $uploadData = null;

        DB::transaction(function () use ($request, $user, &$message, &$uploadData,&$chat) {

            $chat = AiChat::query()->create([
                'user_id'    => $user->id,
                'company_id' => $user->company_id,
                'title'      => 'None',
            ]);

            $message = AiChatMessage::query()->create([
                'chat_id' => $chat->id,
                'message' => $request->message,
            ]);

            if ($request->hasFile('file')) {

                $file = $request->file('file');

                $extension = strtolower($file->getClientOriginalExtension());

                $allowedTypes = [
                    'pdf','doc','docx','xls','xlsx',
                    'txt','ppt','pptx','sql','csv'
                ];

                $fileType = in_array($extension, $allowedTypes)
                    ? $extension
                    : 'other';

                $path = $user->email."/chats/{$chat->id}/data";

                $filePath = Storage::disk('company')->putFileAs(
                    $path,
                    $file,
                    $file->getClientOriginalName()
                );

                $uploadData = UploadedFile::query()->create([
                    'company_id'    => $user->company_id,
                    'chat_id'       => $chat->id,
                    'message_id'    => $message->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path'     => $filePath,
                    'file_type'     => $fileType,
                    'file_size'     => $file->getSize(),
                    'status'        => 'pending',
                ]);
            }
        });
        // безопасный вызов

//        $save_handler=new TableDataHandler($message->id, $uploadData->id,$chat->id);
//        $result=$save_handler->end();


        $resultDashboard=new DashboardGenerator(1,1);
        dd($resultDashboard);

    }
    public function show($chat_id)
    {

        return view('company.pages.chat.show');
    }

    public function message(Request $request, AiChat $chat): JsonResponse
    {
        $user = Auth::user();

        abort_unless($user && $chat->company_id === $user->company_id, 404);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $messageText = trim($data['message']);

        $message = AiChatMessage::query()->create([
            'chat_id' => $chat->id,
            'message' => $messageText,
            'status'  => 'send',
        ]);

        try {
            $reply = (new AIService())->ask(
                $this->buildDashboardPrompt($chat, $messageText, $message->id),
                'Ты умный AI-ассистент, встроенный в дашборд компании. Отвечай кратко, конкретно и на русском языке.'
            );

            $replyText = is_array($reply)
                ? json_encode($reply, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : trim((string) $reply);

            $message->update([
                'answer' => $replyText,
                'status' => 'generate',
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'saved' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->message,
                    'answer' => null,
                    'status' => $message->status,
                ],
                'reply' => '⚠️ Не удалось получить ответ от сервера. Сообщение сохранено.',
                'error' => true,
            ]);
        }

        $message->refresh();

        return response()->json([
            'saved' => true,
            'message' => [
                'id' => $message->id,
                'content' => $message->message,
                'answer' => $message->answer,
                'status' => $message->status,
            ],
            'reply' => $message->answer,
            'error' => false,
        ]);
    }

    private function buildDashboardPrompt(AiChat $chat, string $messageText, ?int $excludeMessageId = null): string
    {
        $historyQuery = $chat->messages()->orderByDesc('id');

        if ($excludeMessageId) {
            $historyQuery->where('id', '<', $excludeMessageId);
        }

        $history = $historyQuery
            ->limit(8)
            ->get()
            ->reverse()
            ->values()
            ->map(function (AiChatMessage $item): string {
                $turn = "Пользователь: {$item->message}";

                if (filled($item->answer)) {
                    $turn .= "\nАссистент: {$item->answer}";
                }

                return $turn;
            })
            ->implode("\n\n");

        $context = $this->dashboardContext();

        return <<<PROMPT
Ты AI-ассистент дашборда компании.
Отвечай кратко, по делу, на русском языке.
Если данных недостаточно, скажи об этом честно.

Текущий контекст дашборда:
{$this->formatContext($context)}

Последние сообщения:
{$history}

Новый запрос пользователя:
{$messageText}
PROMPT;
    }

    private function dashboardContext(): array
    {
        return [
            'todaySales' => 6782,
            'growthRate' => '78.4%',
            'growthDelta' => '-1%',
            'totalUsers' => 75782,
            'activeUsers' => 25782,
            'conversionRate' => '75%',
            'revenue' => '$4,300',
            'newClients' => 6782,
            'activeSubscriptions' => 2986,
            'socialTraffic' => [
                'Instagram' => 3550,
                'Twitter' => 1798,
                'Facebook' => 1245,
                'TikTok' => 986,
                'Pinterest' => 854,
            ],
            'topPages' => [
                ['page' => '/', 'visitors' => 4896, 'bounceRate' => '82.54%'],
                ['page' => '/form-elements.html', 'visitors' => 3652, 'bounceRate' => '76.29%'],
                ['page' => '/index.html', 'visitors' => 3256, 'bounceRate' => '72.65%'],
            ],
            'tasks' => [
                ['name' => 'Extend the data model', 'done' => 2, 'total' => 7, 'status' => 'in progress'],
                ['name' => 'Verify the event flow', 'done' => 0, 'total' => 5, 'status' => 'not started'],
                ['name' => 'Database backup', 'done' => 0, 'total' => 5, 'status' => 'not started'],
                ['name' => 'Identify implementation team', 'done' => 6, 'total' => 10, 'status' => 'in progress'],
                ['name' => 'Check Pull Requests', 'done' => 2, 'total' => 9, 'status' => 'in progress'],
            ],
        ];
    }

    private function formatContext(array $context): string
    {
        return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
