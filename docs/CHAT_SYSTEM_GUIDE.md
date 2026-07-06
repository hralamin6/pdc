# Chat System Guide

## Overview
A comprehensive one-to-one chat system with real-time messaging powered by Pusher, featuring file attachments, message reactions, threading, read receipts, typing indicators, and full notification integration.

## Features

### Core Features
- ✅ **One-to-One Messaging** - Private conversations between two users
- ✅ **Real-time Updates** - Powered by Pusher for instant message delivery
- ✅ **File Attachments** - Support for images, documents, videos, and audio files
- ✅ **Message Reactions** - React to messages with emojis
- ✅ **Message Threading** - Reply to specific messages
- ✅ **Message Editing** - Edit your sent messages
- ✅ **Message Deletion** - Soft delete messages
- ✅ **Read Receipts** - See when messages are read
- ✅ **Typing Indicators** - Know when someone is typing
- ✅ **Message Search** - Search through conversation history
- ✅ **Push Notifications** - Get notified of new messages via PWA
- ✅ **In-app Notifications** - Database notifications for offline messages
- ✅ **Responsive Design** - Mobile-friendly interface

## Installation & Setup

### 1. Run Migrations
```bash
php artisan migrate
```

This will create the following tables:
- `conversations` - Stores conversation metadata
- `messages` - Stores all messages
- `message_attachments` - Stores file attachments
- `message_reactions` - Stores emoji reactions
- `conversation_user` - Pivot table for conversation participants

### 2. Configure Pusher

Add your Pusher credentials to `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 3. Install Pusher PHP SDK (if not already installed)

```bash
composer require pusher/pusher-php-server
```

### 4. Install Laravel Echo and Pusher JS (if not already installed)

```bash
npm install --save-dev laravel-echo pusher-js
```

### 5. Configure Laravel Echo

Update `resources/js/bootstrap.js` to include Echo setup:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }
});
```

### 6. Update Broadcasting Configuration

Ensure `config/broadcasting.php` has Pusher configured:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true,
    ],
],
```

### 7. Enable Broadcasting in App

Uncomment the BroadcastServiceProvider in `config/app.php`:

```php
App\Providers\BroadcastServiceProvider::class,
```

Or create it if it doesn't exist:

```bash
php artisan make:provider BroadcastServiceProvider
```

### 8. Seed Test Data (Optional)

```bash
php artisan db:seed --class=ChatSeeder
```

### 9. Build Assets

```bash
npm run build
# or for development
npm run dev
```

## Usage

### Accessing the Chat
Navigate to `/app/chat/` or click the "Chat" menu item in the sidebar.

### Starting a Conversation
1. Click the "+" button in the chat sidebar
2. Search for a user
3. Click on the user to start a conversation

### Sending Messages
1. Type your message in the input field
2. Press Enter or click the send button
3. Use Shift+Enter for new lines

### Attaching Files
1. Click the paperclip icon
2. Select one or multiple files
3. Preview appears above the input
4. Send the message with attachments

### Reacting to Messages
1. Hover over a message
2. Click the emoji button
3. Select an emoji from the picker
4. Click again to remove your reaction

### Replying to Messages
1. Hover over a message
2. Click the reply arrow icon
3. Type your reply
4. The original message context appears above your input

### Deleting Messages
1. Hover over your own message
2. Click the trash icon
3. Confirm deletion
4. Message is soft-deleted (removed from view but kept in database)

### Searching Messages
Use the search input at the top of the conversation list to search through message content.

## Database Schema

### Conversations Table
- `id` - Primary key
- `user_one_id` - First participant
- `user_two_id` - Second participant
- `last_message_at` - Timestamp of last message
- `created_at`, `updated_at`

### Messages Table
- `id` - Primary key
- `conversation_id` - Foreign key to conversations
- `user_id` - Message sender
- `parent_id` - For threaded replies (nullable)
- `body` - Message text content
- `read_at` - When message was read
- `edited_at` - When message was edited
- `is_deleted` - Soft delete flag
- `created_at`, `updated_at`

### Message Attachments Table
- `id` - Primary key
- `message_id` - Foreign key to messages
- `file_name` - Original filename
- `file_path` - Storage path
- `file_type` - Type (image, document, video, audio)
- `mime_type` - MIME type
- `file_size` - Size in bytes
- `created_at`, `updated_at`

### Message Reactions Table
- `id` - Primary key
- `message_id` - Foreign key to messages
- `user_id` - User who reacted
- `emoji` - Emoji character
- `created_at`, `updated_at`

### Conversation User Pivot Table
- `id` - Primary key
- `conversation_id` - Foreign key to conversations
- `user_id` - Foreign key to users
- `last_read_at` - Last time user read messages
- `is_typing` - Typing indicator flag
- `typing_at` - When user started typing
- `is_blocked` - Block status
- `created_at`, `updated_at`

## Events & Broadcasting

### MessageSent
Broadcast when a new message is sent.
- **Channel**: `chat.{conversationId}`
- **Payload**: Full message object with relations

### MessageUpdated
Broadcast when a message is edited.
- **Channel**: `chat.{conversationId}`
- **Payload**: Updated message object

### MessageDeleted
Broadcast when a message is deleted.
- **Channel**: `chat.{conversationId}`
- **Payload**: Message ID

### MessageReacted
Broadcast when a reaction is added/removed.
- **Channel**: `chat.{conversationId}`
- **Payload**: Message ID, User ID, Emoji

### UserTyping
Broadcast when a user starts/stops typing.
- **Channel**: `chat.{conversationId}`
- **Payload**: User ID, Typing status

## Notifications

### NewMessageNotification
Sent when a user receives a new message.
- **Channels**: Database, WebPush (if enabled)
- **Respects**: User notification preferences for 'chat' category

## Models & Relationships

### Conversation Model
- `userOne()` - BelongsTo User
- `userTwo()` - BelongsTo User
- `messages()` - HasMany Message
- `users()` - BelongsToMany User (with pivot data)
- `latestMessage()` - HasOne Message
- `getOtherUser($userId)` - Helper method
- `hasUser($userId)` - Check if user is participant
- `getUnreadCount($userId)` - Get unread message count
- `markAsRead($userId)` - Mark conversation as read
- `findOrCreateBetween($user1, $user2)` - Static helper

### Message Model
- `conversation()` - BelongsTo Conversation
- `user()` - BelongsTo User
- `parent()` - BelongsTo Message (for threading)
- `replies()` - HasMany Message
- `attachments()` - HasMany MessageAttachment
- `reactions()` - HasMany MessageReaction
- Scopes: `notDeleted()`, `parentMessages()`

### MessageAttachment Model
- `message()` - BelongsTo Message
- `getUrlAttribute()` - Get full URL
- `getFormattedSizeAttribute()` - Human-readable size
- `isImage()` - Check if attachment is image
- Auto-deletes file from storage on model deletion

### MessageReaction Model
- `message()` - BelongsTo Message
- `user()` - BelongsTo User
- `toggle($messageId, $userId, $emoji)` - Static helper

## Livewire Components

### Chat\Index
Main chat component that manages conversation list and selected conversation.

### Chat\MessageList
Displays messages in a conversation with real-time updates.

### Chat\MessageInput
Handles message composition, file uploads, and sending.

## Security

### Authorization
- Private channels ensure only conversation participants can listen
- Message deletion only allowed for message owner
- Conversation access validated on all operations

### File Uploads
- Maximum file size: 10MB per file
- Allowed types: Images, videos, audio, documents
- Files stored in `storage/app/public/chat-attachments`
- Automatic cleanup on message deletion

## Performance Considerations

### Pagination
- Messages are paginated (50 per page)
- Load more functionality for older messages

### Eager Loading
- Messages loaded with user, attachments, and reactions
- Prevents N+1 query problems

### Indexing
- Database indexes on conversation participants
- Indexes on message timestamps for sorting
- Indexes on parent_id for threading

## Customization

### Emoji Reactions
Edit the emoji list in `message-list.blade.php`:
```php
@foreach(['👍', '❤️', '😂', '😮', '😢', '🙏', '🎉', '🔥', '👏', '✨', '💯', '🚀'] as $emoji)
```

### File Upload Limits
Adjust in `MessageInput.php`:
```php
protected $rules = [
    'attachments.*' => 'file|max:10240', // 10MB max
];
```

### Message Pagination
Change in `MessageList.php`:
```php
return $query->orderBy('created_at', 'asc')->paginate(50);
```

### Styling
The chat UI uses DaisyUI components. Customize colors and styles in your Tailwind configuration or directly in the Blade templates.

## Troubleshooting

### Messages not appearing in real-time
1. Check Pusher credentials in `.env`
2. Verify Laravel Echo is properly configured
3. Check browser console for WebSocket errors
4. Ensure BroadcastServiceProvider is registered

### File uploads failing
1. Check storage is linked: `php artisan storage:link`
2. Verify file permissions on storage directory
3. Check max upload size in `php.ini`

### Notifications not working
1. Verify user has notification preferences set
2. Check WebPush configuration
3. Ensure user has subscribed to push notifications

## Future Enhancements

Potential features to add:
- Group chat support
- Voice/video calling
- Message forwarding
- Pinned messages
- User blocking
- End-to-end encryption
- Message status (sent, delivered, read)
- Online/offline status tracking
- Last seen timestamps
- Message export functionality

## API Reference

### Conversation Methods
```php
// Find or create conversation
$conversation = Conversation::findOrCreateBetween($userId1, $userId2);

// Get other user
$otherUser = $conversation->getOtherUser(auth()->id());

// Check if user is participant
$hasAccess = $conversation->hasUser($userId);

// Get unread count
$unreadCount = $conversation->getUnreadCount(auth()->id());

// Mark as read
$conversation->markAsRead(auth()->id());
```

### Message Methods
```php
// Create message
$message = Message::create([
    'conversation_id' => $conversationId,
    'user_id' => auth()->id(),
    'body' => 'Hello!',
]);

// Soft delete
$message->softDeleteMessage();

// Mark as read
$message->markAsRead();

// Get grouped reactions
$reactions = $message->getGroupedReactions();
```

### Reaction Methods
```php
// Toggle reaction
$added = MessageReaction::toggle($messageId, $userId, '👍');
```

## Support

For issues or questions about the chat system, please refer to:
- Laravel Broadcasting Documentation: https://laravel.com/docs/broadcasting
- Pusher Documentation: https://pusher.com/docs
- Livewire Documentation: https://livewire.laravel.com/docs
