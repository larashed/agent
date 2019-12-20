<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as BaseResponse;
use Larashed\Agent\Trackers\Http\Response;
use Orchestra\Testbench\TestCase;

class ResponseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testBasicResponseToArrayHasKeys()
    {
        $response = \Mockery::mock(BaseResponse::class);
        $response->shouldReceive('getStatusCode')
            ->andReturn(500)
            ->andSet('exception', new \Exception());

        $result = (new Response($response))->toArray();

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('exception', $result);
        $this->assertArrayHasKey('trace', $result['exception'][0]);
        $this->assertEquals(500, $result['code']);
        $this->assertNotEmpty($result['exception'][0]['trace']);
    }

    public function testRedirectResponseToArrayHasKeys()
    {
        $response = \Mockery::mock(RedirectResponse::class, [
            'getStatusCode' => 302
        ]);

        $result = (new Response($response))->toArray();

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('exception', $result);
        $this->assertEquals(302, $result['code']);
        $this->assertNull($result['exception']);
    }
}
