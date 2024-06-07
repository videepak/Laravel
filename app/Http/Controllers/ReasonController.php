<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Html\FormFacade;
use App\Role;
use App\User;
use App\Permission;
use Illuminate\Support\Facades\DB;
use App\Subscriber;
use App\Subscription;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReasonController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Permission: only Property Manager can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only Property Manager can access:End
        
        $reason = \App\Reason::where('user_id', $this->user->subscriber_id)
                ->latest()->get();
        $this->data['reason'] = $reason;
        return view('reason.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End
        
        return view('reason.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Permission: only Property Manager can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only Property Manager can access:End
        
        $this->validate(
            $request,
            [
                'Reason' => 'required',
            ]
        );

        $status = \App\Reason::create(
            [
            'reason' => $request->Reason,
            'user_id' => $this->user->subscriber_id
            ]
        );


        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Violation rule created successfully.'
                    : 'Violation rule creation failed.';
        $data = array(
            'title' => 'Violation Rule',
            'text' => $message,
            'class' => $class
        );

        return redirect('reason')
                        ->with('status', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        die('show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End
        
        $reason = \App\Reason::where('id', $id)
                ->where('user_id', $this->user->subscriber_id)
                ->get();
        if ($reason->isNotEmpty()) {
            $this->data['reason'] = $reason;
            return view('reason.edit', $this->data);
        } else {
            return redirect('reason');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End
        
        $this->validate(
            $request,
            [
            'Reason' => 'required',
            ]
        );

        $role = \App\Reason::find($id);
        $role->reason = $request->Reason;
        $status = $role->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Violation rule update successfully.'
                : 'Violation rule update failed.';
        
        $data = [
            'title' => 'Violation Rule',
            'text' => $message,
            'class' => $class
        ];

        return redirect('reason')
                        ->with('status', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        //Permission: only admin can access:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access:End

        $checkExistingReason = \App\Violation::where('violation_reason', $id)
                ->get();

        if ($checkExistingReason->isNotEmpty()) {
            $class = 'error';
            $message = 'This violation rule can not be deleted'
                    . ' because violation is already created for this rule.';
            
            $data = [
                'title' => 'Violation Rule',
                'text' => $message,
                'class' => $class
            ];

            return redirect('reason')
                            ->with('status', $data);
        } else {
            $status = \App\Reason::destroy($id);

            $class = ($status) ? 'success' : 'error';
            
            $message = ($status) ? 'Violation rule delete successfully.'
                    : 'Violation rule delete failed.';
            
            $data = [
                'title' => 'Violation Rule',
                'text' => $message,
                'class' => $class
            ];

            return redirect('reason')
                            ->with('status', $data);
        }
    }
}
