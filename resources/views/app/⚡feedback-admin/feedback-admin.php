<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Feedback;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public string $filterType = '';
    public bool $showUnreadOnly = false;

    public function mount()
    {
        Gate::authorize('feedback.manage');
    }

    public function with(): array
    {
        $query = Feedback::query()->latest();

        if ($this->search) {
            $query->where('message', 'like', "%{$this->search}%");
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->showUnreadOnly) {
            $query->where('is_read', false);
        }

        return [
            'messages' => $query->paginate(15),
            'unreadCount' => Feedback::where('is_read', false)->count(),
        ];
    }

    public function toggleRead(Feedback $feedback)
    {
        $feedback->update(['is_read' => !$feedback->is_read]);
        $this->success('Status updated.');
    }

    public function deleteMessage(Feedback $feedback)
    {
        $feedback->delete();
        $this->warning('Message deleted.');
    }
    
    public function markAllAsRead()
    {
        Feedback::where('is_read', false)->update(['is_read' => true]);
        $this->success('All marked as read.');
    }
};
