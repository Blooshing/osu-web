<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace Tests\Libraries;

use App\Exceptions\AuthorizationException;
use App\Exceptions\VerificationRequiredException;
use App\Libraries\Chat;
use App\Models\Chat\Channel;
use App\Models\Chat\Message;
use App\Models\OAuth\Client;
use App\Models\User;
use Exception;
use Tests\TestCase;

class ChatTest extends TestCase
{
    /**
     * @dataProvider createAnnouncementApiDataProvider
     */
    public function testCreateAnnouncementApi(?string $group, bool $isApiRequest, bool $isAllowed)
    {
        $sender = User::factory()->withGroup($group)->create()->markSessionVerified();
        $users = User::factory()->count(2)->create();

        if ($isApiRequest) {
            $client = Client::factory()->create(['user_id' => $sender]);
            $token = $this->createToken($sender, ['chat.write'], $client);
            $sender->withAccessToken($token);
        }

        if (!$isAllowed) {
            $this->expectException(AuthorizationException::class);
        }

        $channel = Chat::createAnnouncement($sender, [
            'channel' => [
                'description' => 'best',
                'name' => 'announcements',
            ],
            'message' => 'test',
            'target_ids' => $users->pluck('user_id')->toArray(),
        ]);

        if ($isAllowed) {
            $this->assertTrue($channel->fresh()->exists());
        }
    }

    public function testCreateAnnouncementIncludesSender()
    {
        $sender = User::factory()->withGroup('announce')->create()->markSessionVerified();
        $user = User::factory()->create();

        $channel = Chat::createAnnouncement($sender, [
            'channel' => [
                'description' => 'best',
                'name' => 'announcements',
            ],
            'message' => 'test',
            'target_ids' => [$user->getKey()],
        ]);

        $this->assertTrue($channel->fresh()->users()->contains('user_id', $sender->getKey()));
    }

    /**
     * @dataProvider verifiedDataProvider
     */
    public function testSendMessage(bool $verified, $expectedException)
    {
        $sender = User::factory()->create();
        $channel = Channel::factory()->type('public')->create();
        $channel->addUser($sender);

        if ($verified) {
            $sender->markSessionVerified();
        }

        if ($expectedException === null) {
            $this->expectNotToPerformAssertions();
        } else {
            $this->expectException($expectedException);
        }

        Chat::sendMessage($sender, $channel, 'test', false);
    }

    public function testSendPM()
    {
        $sender = User::factory()->create();
        $sender->markSessionVerified();
        $target = User::factory()->create(['pm_friends_only' => false]);

        $initialChannelsCount = Channel::count();
        $initialMessagesCount = Message::count();

        Chat::sendPrivateMessage($sender, $target, 'test message', false);

        $channel = Channel::findPM($sender, $target);

        $this->assertInstanceOf(Channel::class, $channel);
        $this->assertSame($initialChannelsCount + 1, Channel::count());
        $this->assertSame($initialMessagesCount + 1, Message::count());
    }

    /**
     * @dataProvider sendPMMinPlaysDataProvider
     */
    public function testSendPMMinPlays(?string $groupIdentifier, $hasMinPlays, $successful)
    {
        config()->set('osu.user.min_plays_allow_verified_bypass', false);
        config()->set('osu.user.min_plays_for_posting', 2);

        $playCount = $hasMinPlays ? null : 1;

        $sender = User::factory()->withGroup($groupIdentifier)->withPlays($playCount)->create();
        $sender->markSessionVerified();
        $target = User::factory()->create(['pm_friends_only' => false]);

        $countChange = $successful ? 1 : 0;

        $this->expectCountChange(fn () => Channel::count(), $countChange);
        $this->expectCountChange(fn () => Message::count(), $countChange);

        if (!$successful) {
            $this->expectException(AuthorizationException::class);
        }

        Chat::sendPrivateMessage($sender, $target, 'test message', false);

        if ($successful) {
            $this->assertInstanceOf(Channel::class, Channel::findPM($sender, $target));
        }
    }

    /**
     * @dataProvider sendPmFriendsOnlyGroupsDataProvider
     */
    public function testSendPMFriendsOnly(?string $groupIdentifier, $successful)
    {
        $sender = User::factory()->withGroup($groupIdentifier)->create();
        $sender->markSessionVerified();
        $target = User::factory()->create(['pm_friends_only' => true]);

        $initialChannelsCount = Channel::count();
        $initialMessagesCount = Message::count();

        try {
            Chat::sendPrivateMessage($sender, $target, 'test message', false);
        } catch (Exception $e) {
            $savedException = $e;
        }

        if ($successful) {
            $this->assertSame($initialChannelsCount + 1, Channel::count());
            $this->assertSame($initialMessagesCount + 1, Message::count());
        } else {
            $this->assertNull(Channel::findPM($sender, $target));
            $this->assertSame($initialChannelsCount, Channel::count());
            $this->assertSame($initialMessagesCount, Message::count());
            $this->assertSame(
                osu_trans('authorization.chat.friends_only'),
                $savedException->getMessage()
            );
        }
    }

    /**
     * @dataProvider sendPmSenderFriendsOnlyGroupsDataProvider
     */
    public function testSendPmSenderFriendsOnly(?string $groupIdentifier)
    {
        $sender = User::factory()->withGroup($groupIdentifier)->create(['pm_friends_only' => true]);
        $sender->markSessionVerified();
        $target = User::factory()->create(['pm_friends_only' => false]);

        $initialChannelsCount = Channel::count();
        $initialMessagesCount = Message::count();

        try {
            Chat::sendPrivateMessage($sender, $target, 'test message', false);
        } catch (Exception $e) {
            $savedException = $e;
        }

        $this->assertNull(Channel::findPM($sender, $target));
        $this->assertSame($initialChannelsCount, Channel::count());
        $this->assertSame($initialMessagesCount, Message::count());
        $this->assertSame(
            osu_trans('authorization.chat.receive_friends_only'),
            $savedException->getMessage()
        );
    }


    public function testSendPMTooLongNotCreatingNewChannel()
    {
        $sender = User::factory()->create();
        $sender->markSessionVerified();
        $target = User::factory()->create(['pm_friends_only' => false]);

        $initialChannelsCount = Channel::count();
        $initialMessagesCount = Message::count();
        $longMessage = str_repeat('a', config('osu.chat.message_length_limit') + 1);

        try {
            Chat::sendPrivateMessage($sender, $target, $longMessage, false);
        } catch (Exception $e) {
            $savedException = $e;
        }

        $this->assertNull(Channel::findPM($sender, $target));
        $this->assertSame($initialChannelsCount, Channel::count());
        $this->assertSame($initialMessagesCount, Message::count());
        $this->assertSame(
            osu_trans('api.error.chat.too_long'),
            $savedException->getMessage()
        );
    }

    public function testSendPMSecondTime()
    {
        $sender = User::factory()->create();
        $sender->markSessionVerified();
        $target = User::factory()->create(['pm_friends_only' => false]);

        Chat::sendPrivateMessage($sender, $target, 'test message', false);

        $initialChannelsCount = Channel::count();
        $initialMessagesCount = Message::count();

        Chat::sendPrivateMessage($sender, $target, 'test message again', false);

        $this->assertSame($initialChannelsCount, Channel::count());
        $this->assertSame($initialMessagesCount + 1, Message::count());
    }

    public function createAnnouncementApiDataProvider()
    {
        return [
            [null, false, false],
            [null, true, false],
            ['admin', false, true],
            ['admin', true, false],
            ['announce', false, true],
            ['announce', true, true], // announce group retains its group permission with OAuth.
            ['bng', false, false],
            ['bng', true, false],
            ['bot', false, false],
            ['bot', true, false],
            ['gmt', false, true],
            ['gmt', true, false],
            ['nat', false, true],
            ['nat', true, false],
        ];
    }

    public function sendPmFriendsOnlyGroupsDataProvider()
    {
        return [
            ['admin', true],
            ['bng', false],
            ['bot', true],
            ['gmt', true],
            ['nat', true],
            [null, false],
        ];
    }

    public function sendPMMinPlaysDataProvider()
    {
        return [
            [null, true, true],
            [null, false, false],
            ['bot', true, true],
            ['bot', false, true],
        ];
    }

    public function sendPmSenderFriendsOnlyGroupsDataProvider()
    {
        return [
            // admin skip because OsuAuthorize skips the check when admin.
            ['bng'],
            ['bot'],
            ['gmt'],
            ['nat'],
            [null],
        ];
    }

    public function verifiedDataProvider()
    {
        return [
            [false, VerificationRequiredException::class],
            [true, null],
        ];
    }
}
