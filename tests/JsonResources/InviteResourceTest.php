<?php

namespace RTippin\Messenger\Tests\JsonResources;

use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Http\Resources\InviteResource;
use RTippin\Messenger\Models\Invite;
use RTippin\Messenger\Models\Thread;
use RTippin\Messenger\Tests\FeatureTestCase;

class InviteResourceTest extends FeatureTestCase
{
    private Thread $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = $this->createGroupThread($this->tippin);
    }

    /** @test */
    public function it_transforms_invite()
    {
        $invite = Invite::factory()
            ->for($this->group)
            ->owner($this->tippin)
            ->create();

        $resource = (new InviteResource($invite))->resolve();
        $route = $invite->getInvitationRoute();
        $invite = $invite->toArray();

        $this->assertSame($invite['id'], $resource['id']);
        $this->assertSame($invite['code'], $resource['code']);
        $this->assertSame($invite['uses'], $resource['uses']);
        $this->assertSame($invite['max_use'], $resource['max_use']);
        $this->assertSame($route, $resource['route']);
        $this->assertSame($this->group->id, $resource['thread_id']);
        $this->assertSame($invite['created_at'], $resource['created_at']);
        $this->assertSame($invite['updated_at'], $resource['updated_at']);
        $this->assertEquals($this->tippin->getKey(), $resource['owner_id']);
        $this->assertSame($this->tippin->getMorphClass(), $resource['owner_type']);
        $this->assertIsArray($resource['owner']);
    }

    /** @test */
    public function it_transforms_invite_with_join()
    {
        $invite = Invite::factory()
            ->for($this->group)
            ->owner($this->tippin)
            ->create();

        $resource = (new InviteResource($invite, true))->resolve();

        $this->assertIsArray($resource['options']);
        $this->assertArrayNotHasKey('owner', $resource);
        $this->assertTrue($resource['options']['is_valid']);
        $this->assertIsArray($resource['options']['api_avatar']);
        $this->assertIsArray($resource['options']['avatar']);
        $this->assertSame('First Test Group', $resource['options']['thread_name']);
    }

    /** @test */
    public function it_transforms_invalid_invite_with_join()
    {
        $invite = Invite::factory()
            ->for($this->group)
            ->owner($this->tippin)
            ->invalid()
            ->create();

        $resource = (new InviteResource($invite, true))->resolve();

        $this->assertIsArray($resource['options']);
        $this->assertArrayNotHasKey('owner', $resource);
        $this->assertFalse($resource['options']['is_valid']);
        $this->assertNull($resource['options']['api_avatar']);
        $this->assertNull($resource['options']['avatar']);
        $this->assertNull($resource['options']['thread_name']);
    }

    /** @test */
    public function it_transforms_invite_with_join_without_messenger_auth()
    {
        $invite = Invite::factory()
            ->for($this->group)
            ->owner($this->tippin)
            ->create();

        $resource = (new InviteResource($invite, true))->resolve();

        $this->assertFalse($resource['options']['messenger_auth']);
        $this->assertFalse($resource['options']['in_thread']);
    }

    /** @test */
    public function it_transforms_invite_with_join_when_provider_in_thread()
    {
        Messenger::setProvider($this->tippin);
        $invite = Invite::factory()
            ->for($this->group)
            ->owner($this->tippin)
            ->create();

        $resource = (new InviteResource($invite, true))->resolve();

        $this->assertTrue($resource['options']['messenger_auth']);
        $this->assertTrue($resource['options']['in_thread']);
    }

    /** @test */
    public function it_transforms_invite_with_join_when_provider_not_in_thread()
    {
        Messenger::setProvider($this->doe);
        $invite = Invite::factory()
            ->for($this->group)
            ->owner($this->tippin)
            ->create();

        $resource = (new InviteResource($invite, true))->resolve();

        $this->assertTrue($resource['options']['messenger_auth']);
        $this->assertFalse($resource['options']['in_thread']);
    }
}
