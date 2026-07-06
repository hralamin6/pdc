@unblaze
<div class="flex flex-col lg:flex-row h-[calc(100vh-4rem)] bg-base-200" x-data="aiChat()">
    @include('app.⚡ai-chat.sidebar')
    @include('app.⚡ai-chat.main-area')
    @include('app.⚡ai-chat.modals')
</div>

<script>
function aiChat() {
    return {
        init() {
            this.scrollToBottom();
            Livewire.on('message-sent', () => {
                setTimeout(() => this.scrollToBottom(), 100);
            });
            Livewire.on('scroll-to-bottom', () => {
                setTimeout(() => this.scrollToBottom(), 100);
            });
        },
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Copied to clipboard!', type: 'success' }
                }));
            });
        }
    }
}

// Global function for copying code from code blocks
function copyCode(button) {
    const codeBlock = button.closest('.code-block-wrapper').querySelector('code');
    const code = codeBlock.textContent;

    navigator.clipboard.writeText(code).then(() => {
        const originalHTML = button.innerHTML;
        button.innerHTML = `
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            Copied!
        `;
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>
