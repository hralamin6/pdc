{{-- Message Input Area --}}
@unblaze
<div class="p-3 md:p-4 bg-base-100 border-t border-base-300">
    {{-- Editing Mode --}}
    @if($editingMessageId)
        <div class="mb-3 p-3 bg-info/10 border-l-4 border-info rounded">
            <div class="flex items-center justify-between">
                <span class="text-sm text-info-content">Editing message</span>
                <button wire:click="cancelEdit" class="btn btn-ghost btn-xs btn-circle">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Attachments Preview --}}
    @if(!empty($attachments))
        <div class="mb-3 flex flex-wrap gap-2">
            @foreach($attachments as $index => $attachment)
                <div class="relative group">
                    @if(str_starts_with($attachment->getMimeType(), 'image/'))
                        {{-- Image Preview --}}
                        <div class="relative">
                            <img 
                                src="{{ $attachment->temporaryUrl() }}" 
                                alt="Preview" 
                                class="h-20 w-20 object-cover rounded-lg border-2 border-base-300"
                            >
                            <button
                                wire:click="removeAttachment({{ $index }})"
                                class="absolute -top-2 -right-2 btn btn-error btn-xs btn-circle"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @else
                        {{-- File Preview --}}
                        <div class="p-2 bg-base-200 rounded-lg flex items-center gap-2">
                            <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span class="text-sm text-base-content">{{ $attachment->getClientOriginalName() }}</span>
                            <button
                                wire:click="removeAttachment({{ $index }})"
                                class="btn btn-ghost btn-xs btn-circle text-error"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <form wire:submit="{{ $editingMessageId ? 'updateMessage' : 'sendMessage' }}" class="flex items-end gap-2">
        {{-- File Upload --}}
        <label class="flex-shrink-0 cursor-pointer btn btn-ghost btn-sm btn-circle" title="Upload images (Vision: Gemini, Mistral, Groq, Pollinations)">
            <input type="file" wire:model="attachments" multiple accept="image/*" class="hidden" />
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </label>

        {{-- Message Input --}}
        <textarea
            wire:model="message"
            placeholder="Type your message... (Images work with vision models marked 'img')"
            rows="1"
            class="flex-1 textarea textarea-bordered bg-base-200 text-base-content resize-none text-sm md:text-base"
            @keydown.enter.prevent="if (!$event.shiftKey) { $wire.{{ $editingMessageId ? 'updateMessage' : 'sendMessage' }}() }"
            x-data
            x-init="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
        ></textarea>

        {{-- Send Button --}}
        <button
            type="submit"
            class="btn btn-primary btn-sm md:btn-md"
            wire:loading.attr="disabled"
        >
            <svg class="w-4 md:w-5 h-4 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </form>

    <p class="text-xs text-base-content/60 mt-2">
        Press Enter to send, Shift+Enter for new line. 📷 Vision + 🎨 Image Gen: Pollinations!
    </p>
</div>
