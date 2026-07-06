<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Title('Chat')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast;
    use WithFileUploads;
    use WithPagination;

    // ==========================================
    // CONVERSATION STATE
    // ==========================================
    public ?int $selectedConversationId = null;
    public string $search = '';
    public string $messageSearch = '';
    public bool $showSearchBar = false;

    // ==========================================
    // MESSAGE STATE
    // ==========================================
    public string $body = '';
    public array $attachments = [];
    public ?int $replyingTo = null;
    public ?int $editingMessageId = null;

    // ==========================================
    // PAGINATION STATE
    // ==========================================
    public int $messagesPerPage = 20;
    public int $messagesOffset = 0;
    public bool $hasMoreMessages = true;

    // ==========================================
    // UI STATE
    // ==========================================
    public bool $showNewChatModal = false;

    // ==========================================
    // QUERY STRING
    // ==========================================
    protected $queryString = [
        'selectedConversationId' => ['except' => null],
        'search' => ['except' => ''],
    ];

    // ==========================================
    // VALIDATION RULES
    // ==========================================
    protected function rules(): array
    {
        return [
            'body' => 'required_without:attachments|string|max:5000',
            'attachments.*' => 'file|max:10240', // 10MB max
        ];
    }

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    public function mount($conversation = null)
    {
        // If conversation ID provided in URL, select it
      if ($conversation) {
//        dd($conversation);
        $conv = Conversation::find($conversation);
        if ($conv && $conv->hasUser(auth()->id())) {
              $this->selectedConversationId = $conv->id;
          $this->selectConversation($conv->id);
                return;
            }
        }

        // Otherwise, auto-select first conversation (query directly, don't use computed property)
        $firstConversation = auth()->user()
            ->conversations()
            ->orderBy('updated_at', 'desc')
            ->first();
            
        if ($firstConversation) {
            $this->selectedConversationId = $firstConversation->id;
          $this->selectConversation($firstConversation->id);

        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMessageSearch(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================
    #[Computed]
    public function conversations()
    {
        $userId = auth()->id();

        return auth()->user()
            ->conversations()
            ->with(['userOne', 'userTwo', 'latestMessage.user'])
            ->when($this->search, function ($query) use ($userId) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function($q) use ($userId, $searchTerm) {
                    // Search in the OTHER user's name (not the current user)
                    $q->where(function($subQ) use ($userId, $searchTerm) {
                        $subQ->where('user_one_id', '!=', $userId)
                             ->whereHas('userOne', function($userQ) use ($searchTerm) {
                                 $userQ->where('name', 'like', $searchTerm)
                                       ->orWhere('email', 'like', $searchTerm);
                             });
                    })->orWhere(function($subQ) use ($userId, $searchTerm) {
                        $subQ->where('user_two_id', '!=', $userId)
                             ->whereHas('userTwo', function($userQ) use ($searchTerm) {
                                 $userQ->where('name', 'like', $searchTerm)
                                       ->orWhere('email', 'like', $searchTerm);
                             });
                    });
                });
            })
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    #[Computed]
    public function conversationUsers()
    {
        // Map conversation ID to other user ID
        return $this->conversations->mapWithKeys(function ($conversation) {
            return [$conversation->id => $conversation->getOtherUser(auth()->id())->id];
        })->toArray();
    }

    #[Computed]
    public function chatMessages()
    {
        if (!$this->selectedConversationId) {
            return collect([]);
        }

        $query = Message::where('conversation_id', $this->selectedConversationId)
            ->with(['user', 'parent.user', 'media', 'reactions.user'])
            ->where('is_deleted', false);

        if ($this->messageSearch) {
            $query->where('body', 'like', '%' . $this->messageSearch . '%');
        }

        // Get total count
        $totalMessages = $query->count();

        // Calculate how many messages to load (all loaded so far)
        $loadCount = $this->messagesOffset + $this->messagesPerPage;

        // Check if there are more messages
        $this->hasMoreMessages = $totalMessages > $loadCount;

        // Get all messages up to current offset (newest first, then reverse)
        return $query->orderBy('created_at', 'desc')
            ->take($loadCount)
            ->get()
            ->reverse()
            ->values();
    }

    public function loadOlderMessages()
    {
        if ($this->hasMoreMessages) {
            $this->messagesOffset += $this->messagesPerPage;
        }
    }

    public function resetMessages()
    {
        $this->messagesOffset = 0;
        $this->hasMoreMessages = true;
    }

    // ==========================================
    // CONVERSATION ACTIONS
    // ==========================================
    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $this->messageSearch = '';
        $this->replyingTo = null;
        $this->resetMessages();
        $this->resetPage();
        $this->markAsRead();
        $this->dispatch('conversationSelected', conversationId: $conversationId);
        $this->markUnreadMessagesAsRead();
    }

    public function startNewChat($userId): void
    {
        $conversation = Conversation::findOrCreateBetween(auth()->id(), $userId);
        $this->selectedConversationId = $conversation->id;
        $this->showNewChatModal = false;
        $this->markAsRead();
        $this->dispatch('conversationSelected', conversationId: $conversation->id);
    }

    // ==========================================
    // MESSAGE ACTIONS
    // ==========================================
    public function sendMessage(): void
    {
        // If editing, update instead
        if ($this->editingMessageId) {
            $this->updateMessage();
            return;
        }

        $this->validate();

        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if (!$conversation || !$conversation->hasUser(auth()->id())) {
            return;
        }

        // Check if either user has blocked the conversation
        $isBlockedByMe = \DB::table('conversation_user')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', auth()->id())
            ->value('is_blocked');

        $otherUser = $conversation->getOtherUser(auth()->id());
        $isBlockedByOther = \DB::table('conversation_user')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', $otherUser->id)
            ->value('is_blocked');

        if ($isBlockedByMe || $isBlockedByOther) {
            $this->error('Cannot send message. This conversation is blocked.');
            return;
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $this->selectedConversationId,
            'user_id' => auth()->id(),
            'parent_id' => $this->replyingTo,
            'body' => $this->body,
        ]);

        // Handle attachments using Spatie Media Library
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $message->addMedia($attachment->getRealPath())
                    ->usingFileName($attachment->getClientOriginalName())
                    ->withCustomProperties([
                        'mime_type' => $attachment->getMimeType(),
                        'size' => $attachment->getSize(),
                    ])
                    ->toMediaCollection('attachments');
            }
        }

        // Update conversation
        $conversation->update(['last_message_at' => now()]);

        // No broadcast needed - using whisper instead

        // Send notification
        $otherUser = $conversation->getOtherUser(auth()->id());

        // Check if user is online to send push notification
        $sendPush = $otherUser->isOnline();
        $otherUser->notify(new \App\Notifications\NewMessageNotification($message, $sendPush));

        // Reset form
        $this->reset(['body', 'attachments', 'replyingTo']);

        // Dispatch to Alpine to trigger whisper
        $this->dispatch('message-sent', messageId: $message->id);
    }

    public function toggleReaction($messageId, $emoji)
    {
        MessageReaction::toggle($messageId, auth()->id(), $emoji);
        // No broadcast needed - using whisper instead
    }

    public function setReplyingTo(int $messageId): void
    {
        $this->replyingTo = $messageId;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
    }

    public function editMessage(int $messageId): void
    {
        $message = Message::find($messageId);

        if ($message && $message->user_id === auth()->id()) {
            $this->editingMessageId = $messageId;
            $this->body = $message->body;
        }
    }

    public function cancelEdit(): void
    {
        $this->editingMessageId = null;
        $this->body = '';
    }

    public function updateMessage(): void
    {
        if (!$this->editingMessageId) {
            return;
        }

        $message = Message::find($this->editingMessageId);

        if ($message && $message->user_id === auth()->id()) {
            $message->update([
                'body' => $this->body,
                'edited_at' => now()
            ]);

            broadcast(new \App\Events\MessageUpdated($message))->toOthers();

            $this->cancelEdit();
        }
    }

    public function deleteMessage(int $messageId): void
    {
        $message = Message::find($messageId);

        if ($message && $message->user_id === auth()->id()) {
            $message->softDeleteMessage();
            broadcast(new \App\Events\MessageDeleted($message))->toOthers();
        }
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    // ==========================================
    // CONVERSATION MANAGEMENT
    // ==========================================
    public function deleteConversation(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if ($conversation && $conversation->hasUser(auth()->id())) {
            // Delete all messages in the conversation
            Message::where('conversation_id', $this->selectedConversationId)->delete();

            // Delete the conversation
            $conversation->delete();

            // Reset selection
            $this->selectedConversationId = null;
            $this->body = '';
            $this->replyingTo = null;
            $this->editingMessageId = null;

            $this->success('Conversation deleted successfully.');
        }
    }

    public function blockUser(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if ($conversation && $conversation->hasUser(auth()->id())) {
            // Update is_blocked field in conversation_user pivot table
            \DB::table('conversation_user')
                ->where('conversation_id', $this->selectedConversationId)
                ->where('user_id', auth()->id())
                ->update(['is_blocked' => true]);

            $this->success('User blocked successfully.');
        }
    }

    public function unblockUser(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);

        if ($conversation && $conversation->hasUser(auth()->id())) {
            // Update is_blocked field in conversation_user pivot table
            \DB::table('conversation_user')
                ->where('conversation_id', $this->selectedConversationId)
                ->where('user_id', auth()->id())
                ->update(['is_blocked' => false]);

            $this->success('User unblocked successfully.');
        }
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================
    public function refreshMessages(): void
    {
        // Mark unread messages as read when refreshing
        $this->markUnreadMessagesAsRead();
    }

    public function refreshConversations(): void
    {
        // Just trigger a re-render
    }

    public function isUserBlocked(): bool
    {
        if (!$this->selectedConversationId) {
            return false;
        }

        // Check if current user has blocked this conversation
        $result = \DB::table('conversation_user')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', auth()->id())
            ->value('is_blocked');

        return (bool) $result;
    }

    private function markUnreadMessagesAsRead(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        // Mark all unread messages in this conversation as read
        // Use DB::table to prevent updated_at from changing
        $updated = \DB::table('messages')
            ->where('conversation_id', $this->selectedConversationId)
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // If messages were marked as read, whisper to sender and refresh conversations
        if ($updated > 0) {
            $this->dispatch('messages-marked-read');
            // Force refresh conversations list to update unread count
            $this->dispatch('$refresh');
        }
    }

    #[Computed]
    public function otherUser()
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        if (!$conversation) {
            return null;
        }

        return $conversation->getOtherUser(auth()->id());
    }

    private function markAsRead()
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = Conversation::find($this->selectedConversationId);
        if ($conversation) {
            $conversation->markAsRead(auth()->id());
        }
    }

    #[Computed]
    public function availableUsers()
    {
        return User::where('id', '!=', auth()->id())
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function selectedConversation()
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        return Conversation::with(['userOne', 'userTwo'])->find($this->selectedConversationId);
    }
};
