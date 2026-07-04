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
    
    // For creating custom items
    public string $customTitle = '';
    public string $customType = 'boolean';
    public bool $showCustomModal = false;

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $user = auth()->user();
        $this->templates = DailyReportTemplate::orderBy('sort_order')->get();
        
        $this->userItems = UserReportItem::where('user_id', $user->id)
            ->get()
            ->keyBy('daily_report_template_id')
            ->toArray();
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
            UserReportItem::create([
                'user_id' => $user->id,
                'daily_report_template_id' => $templateId,
                'type' => DailyReportTemplate::find($templateId)->type,
                'is_active' => true,
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

        UserReportItem::create([
            'user_id' => auth()->id(),
            'custom_title' => $this->customTitle,
            'type' => $this->customType,
            'is_active' => true,
        ]);

        $this->success('Custom item added!');
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
};
