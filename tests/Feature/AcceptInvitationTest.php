<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function viewing_unused_invitations()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'INVITATIONCODE123',
            'workspace_id' => factory(Workspace::class)->create()->id,
        ]);

        $response = $this->get("/invitations/INVITATIONCODE123");

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->viewData('invitation')->is($invitation));
    }

    /** @test */
    function viewing_used_invitations()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => 1,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');
        $response->assertStatus(404);
    }

    /** @test */
    function viewing_non_existent_invitations()
    {
        $this->get('/invitations/NONEXISTINGINVITATIONCODE')->assertStatus(404);
    }

    /** @test */
    function registering_with_a_valid_invitation_code()
    {   
        $this->withoutExceptionHandling();
        $workspace = factory(Workspace::class)->create();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'INVITATIONCODE123',
            'workspace_id' => $workspace->id,
        ]);

        $response = $this->json('POST', '/register', [
            'first_name' => 'Jae',
            'last_name' => 'Sremmurd',
            'email' => 'jae@example.com',
            'password' => Hash::make(request('password')),
            'invitation_code' => $invitation->code,
        ]);

        $this->assertTrue($invitation->fresh()->hasBeenUsed());
        $member = $workspace->members()->where('email', 'jae@example.com')->first();
        $this->assertNotNull($member);
        $response->assertRedirect("/workspaces/{$workspace->id}");
    }
}