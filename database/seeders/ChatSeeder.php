<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first two users
        $users = User::limit(2)->get();

        if ($users->count() < 2) {
            $this->command->warn('Need at least 2 users to seed chat data. Please run UserSeeder first.');
            return;
        }

        $user1 = $users[0];
        $user2 = $users[1];

        // Create a conversation
        $conversation = Conversation::findOrCreateBetween($user1->id, $user2->id);

        // Create some messages
        $messages = [
            ['user' => $user1, 'body' => 'Hey! How are you doing?'],
            ['user' => $user2, 'body' => 'I\'m doing great! Thanks for asking. How about you?'],
            ['user' => $user1, 'body' => 'Pretty good! Just working on this new chat feature.'],
            ['user' => $user2, 'body' => 'That sounds exciting! What features does it have?'],
            ['user' => $user1, 'body' => 'Real-time messaging with Pusher, file attachments, reactions, threading, and more!'],
            ['user' => $user2, 'body' => 'Wow! That\'s impressive. Can\'t wait to try it out! 🚀'],
        ];

        $createdMessages = [];
        foreach ($messages as $index => $messageData) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $messageData['user']->id,
                'body' => $messageData['body'],
                'created_at' => now()->subMinutes(count($messages) - $index),
            ]);

            $createdMessages[] = $message;
        }

        // Add some reactions
        if (count($createdMessages) > 0) {
            MessageReaction::create([
                'message_id' => $createdMessages[0]->id,
                'user_id' => $user2->id,
                'emoji' => '👍',
            ]);

            MessageReaction::create([
                'message_id' => $createdMessages[4]->id,
                'user_id' => $user2->id,
                'emoji' => '🔥',
            ]);

            MessageReaction::create([
                'message_id' => $createdMessages[5]->id,
                'user_id' => $user1->id,
                'emoji' => '❤️',
            ]);
        }

        // Create a reply (threaded message)
        if (count($createdMessages) > 2) {
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user2->id,
                'parent_id' => $createdMessages[2]->id,
                'body' => 'That\'s awesome! Keep up the great work!',
                'created_at' => now()->subMinutes(2),
            ]);
        }

        // Update conversation last message time
        $conversation->update(['last_message_at' => now()]);

        $this->command->info('Chat seeder completed successfully!');
        $this->command->info("Created conversation between {$user1->name} and {$user2->name}");
        $this->command->info('Created ' . count($createdMessages) . ' messages with reactions and replies');
    }
}
