<?php

namespace App\Http\Controllers;

use App\Exceptions\SubscriptionExpiredException;
use App\Mail\TaskCreatedEmail;
use App\Models\Task;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WorkspaceTasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Workspace $workspace)
    {
        try {
            $this->authorize('update', $workspace);
            
            $tasks = $workspace->tasks()->with('creator', 'assignee')->get();
            
            if (request()->has('my')) {
                $tasks = $workspace->tasks()->where('user_responsible', Auth::user()->id)->get();
            } 

            if (request()->has('by')) {
                $tasks = $workspace->tasks()->where('created_by', Auth::user()->id)->get();
            } 

            if (request()->wantsJson()) {
                return response([$workspace, $tasks], 200);
            }

            return view('tasks.index', [
                'workspace' => $workspace,
                'tasks' => $tasks
            ]);
        } catch (SubscriptionExpiredException $e) {
            return response("Subscription exipred. Please renew your subscription.", 423);
        }
    }

    /**
     * summary
     *
     * @return void
     * @author 
     */
    public function create(Workspace $workspace)
    {
        try {
            $this->authorize('update', $workspace);

            return view('tasks.create', [
                'workspace' => $workspace,
                'members' => $workspace->allMembers(),
            ]);    
        } catch (SubscriptionExpiredException $e) {
            return response("Subscription exipred. Please renew your subscription.", 423);
        }
        
    }

    /**
     * summary
     *
     * @return void
     * @author 
     */
    public function store(Workspace $workspace)
    {
        try {
            $this->authorize('update', $workspace);

            $task = Task::create([
                'created_by' => Auth::user()->id,
                'workspace_id' => $workspace->id,
                'name' => request('name'),
                'user_responsible' => request('user_responsible'),
                'start_date' => request('start_date'),
                'finish_date' => request('finish_date'),
                'status' => request('status'),
            ]);

            Mail::to($task->assignee->email)->queue(new TaskCreatedEmail($task));

            if (request()->wantsJson()) {
                return response([], 201);
            }

            return redirect(route('tasks.index', $workspace));
        } catch (SubscriptionExpiredException $e) {
            return response("Subscription exipred. Please renew your subscription.", 423);
        }

    }

    /**
     * summary
     *
     * @return void
     * @author 
     */
    public function destroy(Workspace $workspace, Task $task)
    {
        try {
            $this->authorize('update', $workspace);

            $task->delete();
        } catch (SubscriptionExpiredException $e) {
            return response("Subscription exipred. Please renew your subscription.", 423);
        }
    }
}
