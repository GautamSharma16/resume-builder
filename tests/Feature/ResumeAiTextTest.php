<?php

use Illuminate\Support\Str;

it('requires starter text before generating a manual summary', function () {
    $response = $this->postJson(route('resume.ai-text'), [
        'context' => 'summary',
        'source' => 'manual',
        'text' => '',
        'resume' => [
            'designation' => 'Laravel Developer',
            'skills' => ['Laravel', 'MySQL'],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'Please write 2-3 lines about yourself first.',
        ]);
});

it('requires a job role before generating experience bullets', function () {
    $response = $this->postJson(route('resume.ai-text'), [
        'context' => 'experience',
        'source' => 'manual',
        'text' => '',
        'resume' => [
            'experience' => [
                ['company' => 'Google', 'role' => '', 'period' => '', 'points' => []],
            ],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'Please enter Job Role first.',
        ]);
});

it('returns concise experience bullets without overflowing response history', function () {
    config()->set('services.gemini.key', null);

    $response = $this->postJson(route('resume.ai-text'), [
        'context' => 'experience',
        'source' => 'manual',
        'job_role' => 'Driver',
        'text' => '',
        'previous_outputs' => ['Handled route scheduling and delivery coordination.'],
        'resume' => [
            'designation' => 'Driver',
            'skills' => ['Route planning', 'Vehicle maintenance', 'Documentation'],
            'experience' => [
                ['company' => 'Google', 'role' => 'Driver', 'period' => '2022 - Present', 'points' => []],
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('source', 'local_fallback');

    $text = (string) $response->json('text');
    $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $text) ?: [])));

    expect(count($lines))->toBeBetween(4, 5);
    expect($lines)->each->not->toBe('');
    expect(str_word_count($text))->toBeBetween(50, 100);
    expect(Str::contains(Str::lower($text), ['route', 'vehicle', 'delivery', 'safety', 'dispatch']))->toBeTrue();
    expect(count(array_unique(array_map(fn ($line) => Str::lower($line), $lines))))->toBe(count($lines));
});
