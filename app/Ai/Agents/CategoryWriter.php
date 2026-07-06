<?php

namespace App\Ai\Agents;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;
#[Provider('custom')]
#[Model('category')]
// #[UseCheapestModel]
// #[MaxTokens(20000)]
// #[Temperature(0.7)]
class CategoryWriter implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
      $existingCategories = Category::pluck('name')->toArray();

      $existingList = !empty($existingCategories)
        ? 'এই বিদ্যমান ক্যাটাগরিগুলো এড়িয়ে চলুন: ' . implode(', ', array_slice($existingCategories, 0, 20))
        : '';

      $prompt = "আমার প্রজেক্টের জন্য একটি ইউনিক, সৃজনশীল এবং অর্থবহ ক্যাটাগরি নাম তৈরি করুন।

এই প্রজেক্টটি স্বয়ংসম্পূর্ণতা ও প্রাকৃতিক জীবনদর্শনভিত্তিক একটি নলেজ প্ল্যাটফর্ম। মূল ফোকাস এরিয়াগুলো হলো:
গ্রামীণ জীবনধারা, হোমস্টেডিং, অফ-গ্রিড জীবনযাপন, রিজেনারেটিভ কৃষি, প্রাণীজ পুষ্টি,
ফারমেন্টেশন ঐতিহ্য, ক্লিন ফুড সিস্টেম, নৈতিক পণ্য, সংকট প্রস্তুতি, ইকো হাউজিং,
ভোক্তাবাদ বিরোধীতা এবং শৃঙ্খলাভিত্তিক মিশনমুখী জীবনযাপন।

ক্যাটাগরির নাম যেন সচেতন, টেকসই, প্রকৃতিনির্ভর ও আত্মনির্ভর জীবনধারাকে প্রতিফলিত করে।

{$existingList}।

শুধুমাত্র নিচের ফরম্যাটে একটি JSON অবজেক্ট রিটার্ন করুন:
{\"name\": \"Category Name\", \"slug\": \"category-slug\"}।

নিয়মাবলি:
- ক্যাটাগরি নাম ২–৪ শব্দের মধ্যে হতে হবে।
- নামটি নির্দিষ্ট, অর্থবহ ও আকর্ষণীয় হতে হবে।
- খুব জেনেরিক শব্দ (যেমন শুধু ‘লাইফস্টাইল’, ‘হেলথ’) ব্যবহার করা যাবে না।
- slug lowercase হবে এবং hyphen (-) ব্যবহার করতে হবে।
- ক্যাটাগরিটি প্রাকৃতিক জীবন, খাদ্য স্বাধীনতা, কৃষি, প্রস্তুতি বা নৈতিক অর্থনীতির সাথে প্রাসঙ্গিক হতে হবে।";

      return $prompt;
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
            'name' => $schema->string()->required(),
            'slug' => $schema->string()->required(),
        ];
    }
}
