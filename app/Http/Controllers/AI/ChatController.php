<?php

namespace App\Http\Controllers\AI;

use App\Helpers\DataHandlers\TableDataHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\AI\StoreChatRequest;
use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\UploadedFile;
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

        DB::transaction(function () use ($request, $user, &$message, &$uploadData) {

            $chat = AiChat::query()->create([
                'user_id'    => $user->id,
                'company_id' => $user->company_id,
                'title'      => $request->message,
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

                $path = "chat/files/{$chat->id}/{$message->id}";

                $filePath = Storage::disk('local')->putFileAs(
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
        new TableDataHandler($message->id, $uploadData->id);
    }
}
