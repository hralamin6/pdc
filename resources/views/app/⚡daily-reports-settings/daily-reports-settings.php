<?php

use App\Models\DailyReportTemplate;
use App\Models\UserReportItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Daily Report Settings')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public $templates = [];
    public $userItems = [];
    public string $search = '';
    public string $selectedCategory = 'all';

    // For creating custom items
    public string $customTitle = '';
    public string $customType = 'boolean';
    public bool $showCustomModal = false;

    public function mount()
    {
        UserReportItem::ensureDefaultsForUser(auth()->id());
        $this->loadItems();
    }

    public function loadItems()
    {
        $user = auth()->user();
        $this->templates = DailyReportTemplate::orderBy('sort_order')->get();

        $this->userItems = UserReportItem::where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
    }

    public function toggleTemplate($templateId)
    {
        $user = auth()->user();
        $existing = UserReportItem::where('user_id', $user->id)
            ->where('daily_report_template_id', $templateId)
            ->first();

        if ($existing) {
            $existing->update(['is_active' => !$existing->is_active]);
        } else {
            $maxSort = UserReportItem::where('user_id', $user->id)->max('sort_order') ?? 0;
            UserReportItem::create([
                'user_id' => $user->id,
                'daily_report_template_id' => $templateId,
                'type' => DailyReportTemplate::find($templateId)->type,
                'is_active' => true,
                'sort_order' => $maxSort + 10,
            ]);
        }

        $this->loadItems();
        $this->success('Settings updated!');
    }

    public function saveCustomItem()
    {
        $this->validate([
            'customTitle' => 'required|string|max:255',
            'customType' => 'required|in:boolean,number,text,mixed',
        ]);

        $maxSort = UserReportItem::where('user_id', auth()->id())->max('sort_order') ?? 0;

        UserReportItem::create([
            'user_id' => auth()->id(),
            'custom_title' => $this->customTitle,
            'type' => $this->customType,
            'is_active' => true,
            'sort_order' => $maxSort + 10,
        ]);

        $this->success('Custom trackable item created!');
        $this->showCustomModal = false;
        $this->customTitle = '';
        $this->loadItems();
    }

    public function toggleCustomItem($itemId)
    {
        $item = UserReportItem::where('user_id', auth()->id())->where('id', $itemId)->firstOrFail();
        $item->update(['is_active' => !$item->is_active]);
        $this->loadItems();
        $this->success('Item updated!');
    }

    public function deleteCustomItem($itemId)
    {
        $item = UserReportItem::where('user_id', auth()->id())->where('id', $itemId)->firstOrFail();
        $item->delete();
        $this->loadItems();
        $this->success('Custom item removed!');
    }

    public function moveUp($itemId)
    {
        $items = UserReportItem::where('user_id', auth()->id())->orderBy('sort_order')->get();
        $currentIndex = $items->search(fn ($item) => $item->id === $itemId);

        if ($currentIndex !== false && $currentIndex > 0) {
            $prevItem = $items[$currentIndex - 1];
            $currentItem = $items[$currentIndex];

            $tempSort = $currentItem->sort_order;
            $currentItem->update(['sort_order' => $prevItem->sort_order]);
            $prevItem->update(['sort_order' => $tempSort]);

            $this->loadItems();
        }
    }

    public function moveDown($itemId)
    {
        $items = UserReportItem::where('user_id', auth()->id())->orderBy('sort_order')->get();
        $currentIndex = $items->search(fn ($item) => $item->id === $itemId);

        if ($currentIndex !== false && $currentIndex < $items->count() - 1) {
            $nextItem = $items[$currentIndex + 1];
            $currentItem = $items[$currentIndex];

            $tempSort = $currentItem->sort_order;
            $currentItem->update(['sort_order' => $nextItem->sort_order]);
            $nextItem->update(['sort_order' => $tempSort]);

            $this->loadItems();
        }
    }

    public function resetDefaults()
    {
        $user = auth()->user();
        UserReportItem::where('user_id', $user->id)->whereNotNull('daily_report_template_id')->delete();
        UserReportItem::ensureDefaultsForUser($user->id);
        $this->loadItems();
        $this->success('Reset to system default trackable items!');
    }
};
