<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Workspace;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function it_belongs_to_a_workspace()
	{
		$workspace = factory(Workspace::class)->create();
		$invitation = factory(Invitation::class)->create(['workspace_id' => $workspace->id]);

		$this->assertInstanceOf('App\Models\Workspace', $invitation->workspace);
	}

	/** @test */
	function it_can_be_retrieved_by_its_code()
	{
		$invitation = factory(Invitation::class)->create(['code' => 'TESTCODE123']);

		$retrievedInvitation = Invitation::findByCode('TESTCODE123');

		$this->assertTrue($retrievedInvitation->is($invitation));
	}

	/** @test */
	function it_can_know_if_it_has_been_used()
	{
		$usedInvitation = factory(Invitation::class)->create(['user_id' => 1]);
		$unusedInvitation = factory(Invitation::class)->create(['user_id' => null]);

		$this->assertTrue($usedInvitation->fresh()->hasBeenUsed());
		$this->assertFalse($unusedInvitation->fresh()->hasBeenUsed());
	}

	/** @test */
	function it_can_get_invitees_full_name()
	{
		$invitation = factory(Invitation::class)->create([
			'code' => 'TESTCODE123',
			'first_name' => 'Ron',
			'last_name' => 'Weasely'
		]);

		$this->assertEquals('Ron Weasely', $invitation->full_name);
	}
}
