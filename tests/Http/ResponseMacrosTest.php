<?php

namespace Tonysm\TurboLaravel\Tests\Http;

use Illuminate\Support\Facades\View;
use Tonysm\TurboLaravel\Tests\TestCase;

class ResponseMacrosTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addNamespace('test-stubs', __DIR__ . '/stubs/');
    }

    /** @test */
    public function streams_model_on_create()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = <<<html
<turbo-stream target="test_models" action="append">
    <template>
        <div id="test_model_{$testModel->id}">hello</div>
    </template>
</turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals('text/html; turbo-stream', $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_update()
    {
        $testModel = TestModel::create(['name' => 'test'])->fresh();

        $expected = <<<html
<turbo-stream target="test_model_{$testModel->id}" action="replace">
    <template>
        <div id="test_model_{$testModel->id}">hello</div>
    </template>
</turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals('text/html; turbo-stream', $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_model_on_delete()
    {
        $testModel = tap(TestModel::create(['name' => 'test']))->delete();

        $expected = <<<html
<turbo-stream target="test_model_{$testModel->id}" action="remove"></turbo-stream>
html;

        $resp = response()->turboStream($testModel);

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals('text/html; turbo-stream', $resp->headers->get('Content-Type'));
    }

    /** @test */
    public function streams_custom_view()
    {
        $testModel = TestModel::create(['name' => 'test']);

        $expected = <<<html
<div id="test_model_{$testModel->id}">hello</div>
html;

        $resp = response()->turboStreamView(View::file(__DIR__ . '/stubs/_test_model.blade.php', [
            'testModel' => $testModel,
        ]));

        $this->assertEquals($expected, trim($resp->getContent()));
        $this->assertEquals('text/html; turbo-stream', $resp->headers->get('Content-Type'));
    }
}

class TestModel extends \Tonysm\TurboLaravel\Tests\TestModel
{
    public function hotwirePartialName()
    {
        return "test-stubs::_test_model";
    }
}