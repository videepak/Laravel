<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteReasonController extends BaseController
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
        //Because '$this->user' not working in constructor
        if(!$this->user->hasRole(['admin']))
            return redirect('unauthorized');
        //Because '$this->user' not working in constructer

        $reason = \App\NoteSubject::where(
            ['user_id'=> $this->subscriber_id]
        )->paginate(50);
        
        $this->data['reason'] = $reason;
        return view('note.notereasonlist', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        //Because '$this->user' not working in constructor
        if(!$this->user->hasRole(['admin']))
            return redirect('unauthorized');
        //Because '$this->user' not working in constructer

        return view('note.createnotereason', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Because '$this->user' not working in constructor
        if(!$this->user->hasRole(['admin']))
            return redirect('unauthorized');
        //Because '$this->user' not working in constructer

        $this->validate(
            $request, 
            [
                'Reason' => 'required',
            ]
        );

        $status = \App\NoteSubject::create(
            [
                'subject' => ucwords($request->Reason),
                'type' => 1,
                'user_id' => $this->user->subscriber_id
            ]
        );


        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Note reason created successfully.' 
                : 'Note Reason creation failed.';
        
        $data = array(
            'title' => 'Note Reason', 
            'text' => $message, 
            'class' => $class
        );

        return redirect('note-reason')
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        //Because '$this->user' not working in constructor
        if(!$this->user->hasRole(['admin']) || $this->checkNoteReasonPermission($id))
            return redirect('unauthorized');
        //Because '$this->user' not working in constructer

        $reason = \App\NoteSubject::find($id);
        $this->data['reason'] = $reason;
        return view('note.createnotereason', $this->data);
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
        //Because '$this->user' not working in constructor
        if(!$this->user->hasRole(['admin']))
            return redirect('unauthorized');
        //Because '$this->user' not working in constructer

        $this->validate(
            $request, 
            [
                'Reason' => 'required',
            ]
        );

        $role = \App\NoteSubject::find($id);
        $role->subject = ucwords($request->Reason);
        $status = $role->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Note reason updated successfully.' 
                : 'Note reason update failed.';
        $data = array(
            'title' => 'Note Reason', 
            'text' => $message, 
            'class' => $class
        );

        return redirect('note-reason')
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
        
        //Because '$this->user' not working in constructor
        if(!$this->user->hasRole(['admin']) || $this->checkNoteReasonPermission($id))
            return redirect('unauthorized');
        //Because '$this->user' not working in constructer

        $checkReasonInNotes = \App\BarcodeNotes::where('reason', $id)->get();

        if ($checkReasonInNotes->isNotEmpty()) {
            
            $class = 'error';
            $message = 'This notes reason can not be '
                    . 'deleted because note is'
                    . ' already created for this reason.';
            $data = array(
                'title' => 'Note Reason',
                'text' => $message,
                'class' => $class
            );

            return redirect('note-reason')
                            ->with('status', $data);
        } else {
            $status = \App\NoteSubject::findOrFail($id)->delete();

            $class = ($status) ? 'success' : 'error';
            $message = ($status) ? 'Notes reason deleted successfully.'
                        : 'Note reason delete failed.';
            $data = array(
                'title' => 'Note Reason',
                'text' => $message,
                'class' => $class
            );

            return redirect('note-reason')
                            ->with('status', $data);
        }
    }
}
