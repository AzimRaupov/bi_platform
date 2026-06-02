<?php

namespace App\Livewire\Company\Pages;
use App\Models\AiChat;
use App\Models\AiChatMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardChat extends Component
{

    public AiChat $chat;

    public ?int $chatId = null;

    public string $message = '';

    public bool $collapsed = false;

    public function render()
    {
        return view('livewire.company.pages.dashboard-chat')
            ->layout('company.layouts.app');
    }
    public function mount(?AiChat $chat = null): void
    {
        $user = Auth::user();

        if ($chat) {
            $this->chat = $chat;
        } else {
            $this->chat = AiChat::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'title' => 'AI Dashboard Chat',
                ],
                [
                    'status' => 'analyzing',
                ]
            );
        }

        $this->chat->load('messages');
    }
    public function toggleCollapse(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    public function clearChat(): void
    {
        $this->chat->messages()->delete();
        $this->chat->load(['messages' => fn ($query) => $query->orderBy('id')]);
    }

    public function quickAsk(string $text): void
    {
        $this->message = $text;
        $this->sendMessage();
    }

    public function sendMessage(): void
    {
        $text = trim($this->message);

        if ($text === '') {
            return;
        }

        AiChatMessage::query()->create([
            'chat_id' => $this->chat->id,
            'message' => $text,
            'status' => 'send',
        ]);

        $this->chat->load(['messages' => fn ($query) => $query->orderBy('id')]);
        $this->message = '';

        $this->dispatch('scroll-chat');
    }
}
