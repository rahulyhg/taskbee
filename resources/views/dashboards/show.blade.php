@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @forelse($workspaces as $workspace)
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <a href="{{ route('workspaces.show', $workspace) }}">{{ $workspace->name }}</a>
                        </div>

                        <div class="card-body text-left">
                            {{ $workspace->creator->full_name }}

                            @forelse($workspace->invitations as $invitation)

                                <li style="list-style: none;">
                                    {{ $invitation->full_name }}

                                    <span
                                        class="badge badge-pill {{ $invitation->hasBeenUsed() ? 'badge-success' : 'badge-warning'}}">
                                        {{ $invitation->hasBeenUsed() ? 'Accepted' : 'Invited' }}
                                    </span>
                                </li>
                            @empty
                                <p>Still no invited members</p>
                            @endforelse
                        </div>

                        <div class="card-footer">
                            @if ($workspace->authorization->invites_remaining > 0)
                                You can
                                <a href="{{ route('workspace-setup.show', $workspace->authorization->code) }}">invite</a>
                                {{ $workspace->authorization->invites_remaining }}
                                more {{ str_plural('member', $workspace->authorization->invites_remaining) }}
                            @else
                                You have used all your invites. Setup or purchase more slots <a
                                        href="{{ route('workspace-members.index', $workspace) }}">here</a>.
                            @endif

                        </div>
                    </div>
                </div>
            @empty
                <p>Nothing here.</p>
            @endforelse
        </div>
    </div>
@endsection
