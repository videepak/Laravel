<?php

namespace App\Http\Controllers;

use App\Tickets;
use Illuminate\Http\Request;
use App\Notifications\EmailTemplate;
use Auth;


class TicketsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.tickets.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function show(Tickets $tickets, Request $request)
    {
        $i = $request->start + 1;
        $subscriberArray = [];
        $email = $mobile = '';
        $search = $request->search['value'];
        $status = $request->status;
        
        //Get total record count: Start
        $subscribersCount = \App\Tickets::all();
        //Get total record count: End

        $subscribers = \App\Tickets::query()
            ->when(
                !empty($search),
                function ($query) use ($search) {
                    $query->where('ticket_id', 'like', "%$search%")
                        ->orWhere('created_at', 'like', "%$search%")
                        ->orWhereRaw("user_id in (select `id` from `users` where `email` LIKE '%" . $search . "%' or  `mobile` LIKE '%" . $search . "%' and `deleted_at` is null)");
                }
            )
            ->when(
                !empty($status > -1),
                function ($query) use ($status) {
                    $query->where('status', $status);
                }
            )
            ->latest()
            ->limit($request->length)->offset($request->start)
            ->with(
                [
                    //'getState',
                    'user' => function ($query) {
                        $query->select(\DB::raw("CONCAT_WS(' ', `firstname`, `lastname`) as name"), 'id', 'email', 'mobile')
                        ->withTrashed();
                    }
                ]
            )
        ->get();
        
        foreach ($subscribers as $subscriber) {
            if (isset($subscriber->user->name)) {
                $name = ucwords($subscriber->user->name);
            }

            if (isset($subscriber->user->email)) {
                $email = ucwords($subscriber->user->email);
            }

            if (isset($subscriber->user->mobile)) {
                $mobile = ucwords($subscriber->user->mobile);
            }

            if (isset($subscriber->ticket_id)) {
                $ticketId = '#' . $subscriber->ticket_id;
            }

            if (isset($subscriber->message)) {
                $message = ucwords($subscriber->message);
            }

            if (isset($subscriber->created_at)) {
                $createdAt = \Carbon\Carbon::parse($subscriber->created_at)
                ->timezone(getUserTimezone())->format('m-d-Y h:i A');

                //$createdAt = $subscriber->created_at->toDateTimeString();
            }

            $url = url('admin/tickets-status');

            if ($subscriber->status == 0) {
                $status = "<a href='#' class='action-change' data-type='select' data-pk='" . $subscriber->id . "' data-url='" . $url . "' data-title='Select Action'>Not Started</a>";
            }
            if ($subscriber->status == 1) {
                $status = "<a href='#' class='action-change' data-type='select' data-pk='" . $subscriber->id . "' data-url='" . $url . "' data-title='Select Action'>In Progress</a>";
            }
            if ($subscriber->status == 2) {
                $status = "<a href='#' class='action-change' data-type='select' data-pk='" . $subscriber->id . "' data-url='" . $url . "' data-title='Select Action'>Closed</a>";
            }
            if ($subscriber->status == 3) {
                $status = "<a href='#' class='action-change' data-type='select' data-pk='" . $subscriber->id . "' data-url='" . $url . "' data-title='Select Action'>Archived</a>";
            }

            if (!empty($subscriber->user->id)) {
                $action = '<a href="javascript:void(0);" data-id="' . $subscriber->id . '"';
                $action .= 'class="get-detail"  title="Comment"><li class="fa fa-comment"></li></a>';
            }
            
            $subscriberArray[] = [
                'id' => $i++,
                'ticketId' => $ticketId,
                'name' => $name,
                'status' => $status,
                'email' => $email,
                'mobile' => $mobile,
                'createdAt' => $createdAt,
                'message' => $message,
                'action' => $action,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($subscribersCount) ? $subscribersCount->count() : 0,
                'recordsFiltered' => !empty($subscribersCount) ? $subscribersCount->count() : 0,
                'data' => $subscriberArray,
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function edit(Tickets $tickets)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tickets $tickets)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Tickets  $tickets
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tickets $tickets)
    {
        //
    }

    public function addComment(Request $request)
    {
        $status = \App\TicketComment::create(
            [
                'ticket_id' => $request->ticket_id,
                'admin_id' => \Auth::user()->id,
                'comment' => $request->comment
            ]
        );

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Comment added successfully.' : 'Employee creation failed.';

        $data = [
            'title' => 'Tickets',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('admin/tickets')
            ->with('status', $data);
    }
    
    public function ticketStatus(Request $request)
    {
        $status = \App\Tickets::where('id', $request->pk)->update(
            [
                'status' => $request->value,
            ]
        );

        $status = \App\Tickets::with(
            [
                'user' => function ($query) {
                    $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"), 'email');
                }
            ]
        )->find($request->pk);


        if (!is_null($status)) {
            $status->status = $request->value;
            $status->save();
        
            if ($status->status == 2) {
                $user = $status->user;
                
                $content = "Your report issue (#$status->ticket_id) has been closed successfully.<br/><br/>";

                $user->notify(new EmailTemplate($content, "#$status->ticket_id has been closed successfully."));
            }

            return response()->json(['status' => 'true']);
        } else {
            return response()->json(['status' => 'false']);
        }
    }
    
    public function viewComment($id)
    {
        $status = \App\Tickets::where('id', $id)
            ->with(
                [
                    'ticketCategory',
                    'comment' => function ($query) {
                        $query->latest()
                            ->with(
                                [
                                    'getAdmin'
                                ]
                            );
                    }
                ]
            )
        ->first();

        return view('admin.tickets.commentmodal', ['status' => $status]);
    }
}
