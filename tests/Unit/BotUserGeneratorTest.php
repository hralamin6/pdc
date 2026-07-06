<?php

use App\Ai\Agents\BotUserGenerator;

test('bot user generator returns structured profile data', function () {
    $prompt = 'একজন বাংলাদেশী ব্যবহারকারীর প্রোফাইল তৈরি করো।';

    $response = BotUserGenerator::make()
        ->prompt($prompt);

    expect($response)->not->toBeNull();

    $structured = $response->structured;

    // Check all required fields are present
    expect($structured)->toHaveKeys(['name', 'gender', 'address', 'bio']);

    // Validate field types and content
    expect($structured['name'])->toBeString()->not->toBeEmpty();
    expect($structured['gender'])->toBeString()->toBeIn(['male', 'female']);
    expect($structured['address'])->toBeString()->not->toBeEmpty();
    expect($structured['bio'])->toBeString()->not->toBeEmpty();

    // Bio should be substantial (at least 50 characters)
    expect(mb_strlen($structured['bio']))->toBeGreaterThan(50);
})->skip('This test requires AI service to be available');

test('bot user generator handles multiple requests', function () {
    $results = [];

    for ($i = 0; $i < 3; $i++) {
        $response = BotUserGenerator::make()
            ->prompt('একজন বাংলাদেশী ব্যবহারকারীর প্রোফাইল তৈরি করো।');

        $results[] = $response->structured;
    }

    // All should have valid names
    foreach ($results as $result) {
        expect($result['name'])->toBeString()->not->toBeEmpty();
        expect($result['gender'])->toBeIn(['male', 'female']);
    }

    // Names should be different (probabilistically)
    $names = array_column($results, 'name');
    expect(count(array_unique($names)))->toBeGreaterThan(1);
})->skip('This test requires AI service to be available and may be slow');
