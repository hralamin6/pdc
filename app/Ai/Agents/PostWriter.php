<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('custom')]
#[Model('post')]
// #[UseCheapestModel]
// #[MaxTokens(10000)]
// #[Temperature(0.7)]
// #[Timeout(120)]
class PostWriter implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {

        return 'একটি বিস্তারিত এবং আকর্ষণীয় ব্লগ পোস্ট লিখুন। '
            ."শর্তাবলী:\n"
            ."- দৈর্ঘ্য: ১০-২০ শব্দ (সংক্ষিপ্ত কিন্তু তথ্যবহুল রাখুন)\n"
            ."- যথাযথ স্থানে বুলেট পয়েন্ট এবং সংখ্যায়িত তালিকা ব্যবহার করুন\n"
            ."- মূল পয়েন্টগুলোতে জোর দেওয়ার জন্য **বোল্ড** ব্যবহার করুন\n"
            ."- সূক্ষ্ম গুরুত্ব বোঝাতে *ইটালিক* ব্যবহার করুন\n"
            ."- ব্যস্ততা বাড়াতে প্রাসঙ্গিক ইমোজি (💪, 🏃, 🥗, ইত্যাদি) পরিমিতভাবে ব্যবহার করুন\n"
            ."- কোনো টেবিল বা জটিল ফরম্যাটিং ব্যবহার করবেন না\n"
            ."- শেষে একটি সংক্ষিপ্ত কল-টু-অ্যাকশন অন্তর্ভুক্ত করুন\n"
            ."- এটি তথ্যবহুল, কার্যকর এবং অনুপ্রেরণামূলক করুন\n"
            ."- বন্ধুত্বপূর্ণ এবং পেশাদার টোনে বাংলায় লিখুন\n\n"
            ."শুধুমাত্র এই ফরম্যাটে একটি JSON অবজেক্ট রিটার্ন করুন:\n"
            .'{"title": "আকর্ষণীয় পোস্টের শিরোনাম", "excerpt": "১৫০ অক্ষরের সারসংক্ষেপ", "content": "মার্কডাউন ফরম্যাটে সম্পূর্ণ পোস্টের কন্টেন্ট", "image_prompt": "write a nice small blog post image prompt in english for this post, no girl and text", "category_id": number"}';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [

        ];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'content' => $schema->string()->required(),
            'excerpt' => $schema->string()->required(),
            'image_prompt' => $schema->string()->required(),
            'category_id' => $schema->number()->required(),
        ];
    }
}
