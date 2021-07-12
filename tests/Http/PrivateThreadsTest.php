<?php

namespace RTippin\Messenger\Tests\Http;

use Illuminate\Http\UploadedFile;
use RTippin\Messenger\Tests\HttpTestCase;

class PrivateThreadsTest extends HttpTestCase
{
    /** @test */
    public function creating_private_thread_with_non_friend_is_pending()
    {
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'message' => 'Hello World!',
            'recipient_alias' => 'user',
            'recipient_id' => $this->doe->getKey(),
        ])
            ->assertSuccessful()
            ->assertJson([
                'type' => 1,
                'type_verbose' => 'PRIVATE',
                'pending' => true,
                'group' => false,
                'unread' => true,
                'name' => 'John Doe',
                'options' => [
                    'awaiting_my_approval' => false,
                ],
            ]);
    }

    /** @test */
    public function creating_private_thread_with_friend_is_not_pending()
    {
        $this->logCurrentRequest('api.messenger.privates.store');
        $this->createFriends($this->tippin, $this->doe);
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'message' => 'Hello World!',
            'recipient_alias' => 'user',
            'recipient_id' => $this->doe->getKey(),
        ])
            ->assertSuccessful()
            ->assertJson([
                'pending' => false,
            ]);
    }

    /** @test */
    public function creating_new_private_thread_with_image()
    {
        $this->logCurrentRequest('api.messenger.privates.store', '200_IMAGE');
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'image' => UploadedFile::fake()->image('picture.jpg'),
            'recipient_alias' => 'user',
            'recipient_id' => $this->doe->getKey(),
        ])
            ->assertSuccessful();
    }

    /** @test */
    public function creating_new_private_thread_with_document()
    {
        $this->logCurrentRequest('api.messenger.privates.store', '200_DOCUMENT');
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'document' => UploadedFile::fake()->create('test.pdf', 500, 'application/pdf'),
            'recipient_alias' => 'user',
            'recipient_id' => $this->doe->getKey(),
        ])
            ->assertSuccessful();
    }

    /** @test */
    public function creating_new_private_thread_with_audio()
    {
        $this->logCurrentRequest('api.messenger.privates.store', '200_AUDIO');
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'audio' => UploadedFile::fake()->create('test.mp3', 500, 'audio/mpeg'),
            'recipient_alias' => 'user',
            'recipient_id' => $this->doe->getKey(),
        ])
            ->assertSuccessful();
    }

    /** @test */
    public function creating_new_private_forbidden_when_one_exist()
    {
        $this->logCurrentRequest('api.messenger.privates.store');
        $this->createPrivateThread($this->tippin, $this->doe);
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'message' => 'Hello World!',
            'recipient_alias' => 'user',
            'recipient_id' => $this->doe->getKey(),
        ])
            ->assertForbidden();
    }

    /**
     * @test
     * @dataProvider messageValidation
     * @param $messageValue
     */
    public function create_new_private_checks_message($messageValue)
    {
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'message' => $messageValue,
            'recipient_alias' => 'user',
            'recipient_id' => 1,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('message');
    }

    /**
     * @test
     * @dataProvider recipientValidation
     * @param $aliasValue
     * @param $idValue
     * @param $errors
     */
    public function create_new_private_checks_recipient_values($aliasValue, $idValue, $errors)
    {
        $this->actingAs($this->tippin);

        $this->postJson(route('api.messenger.privates.store'), [
            'message' => 'Hello!',
            'recipient_alias' => $aliasValue,
            'recipient_id' => $idValue,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors($errors);
    }

    public function messageValidation(): array
    {
        return [
            'Message cannot be empty' => [''],
            'Message cannot be integers' => [5],
            'Message cannot be boolean' => [true],
            'Message cannot be null' => [null],
            'Message cannot be an array' => [[1, 2]],
        ];
    }

    public function recipientValidation(): array
    {
        return [
            'Alias and ID cannot be empty' => ['', '', ['recipient_alias', 'recipient_id']],
            'Alias and ID cannot be boolean' => [true, true, ['recipient_alias', 'recipient_id']],
            'Alias and ID cannot be null' => [null, null, ['recipient_alias', 'recipient_id']],
            'Alias must be string' => [5, 1, ['recipient_alias']],
            'ID cannot be array' => ['user', [1, 2], ['recipient_id']],
        ];
    }
}
