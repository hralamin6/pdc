<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('custom')]
#[Model('user')]
#[MaxTokens(20000)]
#[Temperature(0.7)]

class BotUserGenerator implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'তুমি একজন বাংলাদেশী ব্যবহারকারীর প্রোফাইল তৈরি করছো। '
            ."একটি বাস্তবসম্মত এবং পেশাদার প্রোফাইল তৈরি করো।\n\n"
            ."নিশ্চিত করো:\n"
            ."- নাম সম্পূর্ণ বাংলায় এবং বাস্তবসম্মত বাংলাদেশী নাম\n"
            ."- ঠিকানা শুধু গ্রাম/মহল্লার নাম (বিভাগ/জেলা ছাড়া)\n"
            ."- বায়ো পেশাদার, তথ্যবহুল এবং অনুপ্রেরণামূলক (150-200 শব্দ)\n"
            ."- লিঙ্গ অনুযায়ী উপযুক্ত নাম\n"
            ."- gender অবশ্যই 'male' হতে হবে\n\n"
            ."শুধুমাত্র এই ফরম্যাটে একটি JSON অবজেক্ট রিটার্ন করুন:\n"
            .'{"name": "সম্পূর্ণ বাংলা নাম", "gender": "male", "address": "গ্রাম/মহল্লা নাম", "bio": "পেশাদার বায়ো only 5 sentenceP", "occupation": "পেশা"}';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [];
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
            'gender' => $schema->string()->required(),
            'address' => $schema->string()->required(),
            'bio' => $schema->string()->required(),
            'occupation' => $schema->string()->required(),
        ];
    }
}
