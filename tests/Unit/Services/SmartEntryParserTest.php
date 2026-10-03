<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\SmartEntryParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SmartEntryParserTest extends TestCase
{
    protected SmartEntryParser $parser;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SmartEntryParser();

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Parser Tester',
            'email' => strtolower('parser_' . Str::random(8) . '@example.com'),
            'password_hash' => Hash::make('secret123'),
        ]);
    }

    public function test_parses_single_expense_nominal_shorthand(): void
    {
        $result = $this->parser->parse('beli bakso 15k', $this->user->id);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];

        $this->assertEquals(15000, $item['amount']);
        $this->assertEquals('expense', $item['type']);
        $this->assertStringContainsString('bakso', strtolower($item['item']));
    }

    public function test_parses_nominal_with_rb_and_relative_date(): void
    {
        $result = $this->parser->parse('isi bensin 50rb kemarin', $this->user->id);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];

        $this->assertEquals(50000, $item['amount']);
        $this->assertEquals(Carbon::yesterday()->toDateString(), $item['spent_at']);
    }

    public function test_parses_income_keyword_and_jt_nominal(): void
    {
        $result = $this->parser->parse('terima gajian 5jt', $this->user->id);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];

        $this->assertEquals(5000000, $item['amount']);
        $this->assertEquals('income', $item['type']);
    }

    public function test_parses_multi_items_separated_by_sama(): void
    {
        $result = $this->parser->parse('bakso 15k sama es teh 5k', $this->user->id);

        $this->assertCount(2, $result['items']);
        $this->assertEquals(15000, $result['items'][0]['amount']);
        $this->assertEquals(5000, $result['items'][1]['amount']);
    }

    public function test_throws_exception_when_no_amount_found(): void
    {
        $this->expectException(\Exception::class);
        $this->parser->parse('jalan jalan santai di taman', $this->user->id);
    }
}
