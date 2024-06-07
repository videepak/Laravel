<?php

namespace App\Http\Controllers;

use App\Tasks;
use Illuminate\Http\Request;

class TasksController extends BaseController
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
        $this->data['properties'] = $this->propertyList()->get();
        return view('tasks.list', $this->data);
    }

    public function getTask(Request $request)
    {
        $i = $request->start + 1;
        $search = $request->search['value'];
        $propertyArray = [];
        $user = "";

        //Get total result:Start (Todo: merge the both queries)
        $taskCount = \App\Tasks::whereIn(
            'user_id',
            function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('subscriber_id', $this->user->subscriber_id)
                    ->whereNull('deleted_at');
            }
        );
        //Get total result:End
        //Get result with limit:Start
        $tasks = \App\Tasks::select('id', 'name', 'user_id', 'description', 'frequency', 'is_photo', 'start_date', 'end_date', 'notify_property_manager')
            ->whereIn(
                'user_id',
                function ($query) {
                    $query->select('id')
                        ->from('users')
                        ->where('subscriber_id', $this->user->subscriber_id)
                        ->whereNull('deleted_at');
                }
            )
            ->when(
                isset($search),
                function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('updated_at', 'like', '%' . $search . '%')
                        ->orWhere('created_at', 'like', '%' . $search . '%')
                        ->orWhereRaw("user_id in (select `id` from `users` where CONCAT_WS(' ', `title`, `firstname`, `lastname`) LIKE '%$search%')")
                        ;
                }
            )
            ->with(
                [
                    'getUser' => function ($query) use ($search) {
                        $query->select('id', \DB::raw("CONCAT_WS(' ', `title`, `firstname`, `lastname`) as name"))
                            ->withTrashed();
                    },
                    'property' => function ($query) {
                        $query->select('name');
                    }
                ]
            )
            ->latest()
            ->limit($request->length)
            ->offset($request->start)
            ->get();
        //Get result with limit:End
            
        foreach ($tasks as $task) {
            if (!is_null($task->getUser)) {
                $user = $task->getUser->name;
            }

            $action = "<a href='javascript:void(0);' title='Edit'><li class='fa fa-edit get-detail' data-id='" . $task->id . "'></li></a> | ";
            
            $action .= "<a href='" . url('tasks/' . $task->id) . "' onclick='return deleteProperty(this, event);' title='Delete'><li class='fa fa-trash-o'></li></a>";

            if ($task->is_photo) {
                $photo = '<li class="fa fa-check-circle fa-lg" style="color:green;"></li>';
            } else {
                $photo = '<li class="fa fa-check-circle fa-lg" style="color:gray;"></li>';
            }

            if ($task->notify_property_manager) {
                $notifyManager = '<li class="fa fa-check-circle fa-lg" style="color:green;"></li>';
            } else {
                $notifyManager = '<li class="fa fa-check-circle fa-lg" style="color:gray;"></li>';
            }
            
            $propertyArray[] = [
                's_no' => $i++,
                'name' => ucwords($task->name),
                'start' => \Carbon\Carbon::parse($task->start_date)->format('m-d-Y') . ' To ' . \Carbon\Carbon::parse($task->end_date)->format('m-d-Y'),
                'user' => $user,
                'photo' => $photo,
                'notify' => $notifyManager,
                'property' => isset($task->property[0]->name) ? ucwords($task->property[0]->name) : '',
                'action' => $action,
            ];
        }

        return json_encode(
            [
                'draw' => intval($request->draw),
                'recordsTotal' => !empty($properties) ? $properties->count() : 0,
                'recordsFiltered' => !empty($properties) ? $properties->count() : 0,
                'data' => $propertyArray,
            ]
        );
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tasks.model', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        #1439: Task Management within Trash Scan Comment #35: Start
        $frequency = $request->frequency;
        $currDate = \Carbon\Carbon::now()->timezone(getUserTimezone());

        if ($frequency == 1) {
            $start = getStartEndTime()->startTime;
            $end = getStartEndTime()->endTime;

            $text = 'Service has been completed on this day.Please update start date of task in order to save.';
        }

        if ($frequency == 2) {
            if ($currDate->dayOfWeek == \Carbon\Carbon::MONDAY) {
                $end = \Carbon\Carbon::parse($currDate->format('Y-m-d') . ' 05:59:59', $this->timezone);
            } else {
                $end = \Carbon\Carbon::parse('next monday', getUserTimezone())
                    ->addHours(5)->addMinutes(59)->addSeconds(59);
            }

            $start = \Carbon\Carbon::parse($end->copy()->subDays(1)->format('Y-m-d') . ' 06:00:00', getUserTimezone());
                            
            $text = 'Service has been completed for this week.Please update start date of task in order to save.';
        }

        if ($frequency == 3) {
            $end = new \Carbon\Carbon('first day of next month');
            $end->startOfMonth()->addHours(5)->addMinutes(59)->addSeconds(59);

            $start = \Carbon\Carbon::parse($end->copy()->subDays(1)->format('Y-m-d') . ' 06:00:00', getUserTimezone());

            $text = 'Service has been completed for this month.Please update start date of task in order to save."';
        }

        $isCheckOut = \App\PropertiesCheckIn::query()
            ->select('id')
            ->where('property_id', $request->property_id)
            ->where('check_in_complete', 1)
            ->whereBetween(
                \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
                [
                    $start, $end
                ]
            )
        ->first();

        if (!is_null($isCheckOut)) {
            return redirect('tasks')
                ->with(
                    'status',
                    [
                        'title' => 'Tasks',
                        'text' => $text,
                        'class' => 'error',
                    ]
                );
        }
        #1439: Task Management within Trash Scan Comment #35: End

        $tasks = \App\Tasks::create(
            [
                'name' => $request->taskTitle,
                'description' => $request->description,
                'start_date' => \Carbon\Carbon::parse(explode('-', $request->datefilter)[0])->format('Y-m-d'),
                'end_date' => \Carbon\Carbon::parse(explode('-', $request->datefilter)[1])->format('Y-m-d'),
                'is_photo' => $request->onoff == 'on' ? 1 : 0,
                'notify_property_manager' => $request->notify == 'on' ? 1 : 0,
                'frequency' => $request->frequency,
                'user_id' => $this->user->id,
            ]
        );

        if (isset($tasks->id) && isset($request->property_id)) {
            \App\TaskAssign::create(
                [
                    'property_id' => $request->property_id,
                    'task_id' => $tasks->id,
                ]
            );

            $tas = \App\Tasks::find($tasks->id);
            $tas->barcode_id = \Hashids::encode($tasks->id);
            $tas->save();
        }
        
        $class = ($tasks) ? 'success' : 'error';
        $message = ($tasks) ? 'Task created successfully.'
            : 'Tasks creation failed.';
        
        $data = [
            'title' => 'Tasks',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('tasks')
            ->with('status', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function show(Tasks $tasks)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function edit(Tasks $tasks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tasks $tasks)
    {
        dd('97987898');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tasks $tasks, $id)
    {
        $tasks = \App\Tasks::find($id)->delete();
        \App\TaskAssign::where('task_id', $id)->delete();

        $class = ($tasks) ? 'success' : 'error';
        $message = ($tasks) ? 'Task delete successfully.'
            : 'Tasks delete failed.';
        


        $data = [
            'title' => 'Tasks',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('tasks')
            ->with('status', $data);
    }

    public function getTaskDetail($id)
    {
        //Permission: only admin can access this panel:Start
        if (!$this->user->hasRole('admin')) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access this panel:End

        $task = \App\Tasks::select('id', 'name', 'description', 'is_photo', 'frequency', 'start_date', 'end_date', 'notify_property_manager')
            ->with(
                [
                    'property' => function ($query) {
                        $query->select('properties.id', 'name');
                    }
                ]
            )
        ->find($id);
            
        return response()
            ->json(
                [
                    'detail' => $task,
                    'result' => true,
                ]
            );
    }

    public function updateTask(Request $request, $id)
    {
        //Permission: only admin can access this panel:Start
        if (!$this->user->hasRole('admin')) {
            return redirect('unauthorized');
        }
        //Permission: only admin can access this panel:End

        #1439: Task Management within Trash Scan Comment #35: Start
        $frequency = $request->frequency;
        $currDate = \Carbon\Carbon::now()->timezone(getUserTimezone());

        if ($frequency == 1) {
            $start = getStartEndTime()->startTime;
            $end = getStartEndTime()->endTime;

            $text = 'Service has been completed on this day.Please update start date of task in order to save.';
        }

        if ($frequency == 2) {
            if ($currDate->dayOfWeek == \Carbon\Carbon::MONDAY) {
                $end = \Carbon\Carbon::parse($currDate->format('Y-m-d') . ' 05:59:59', $this->timezone);
            } else {
                $end = \Carbon\Carbon::parse('next monday', getUserTimezone())
                    ->addHours(5)->addMinutes(59)->addSeconds(59);
            }

            $start = \Carbon\Carbon::parse($end->copy()->subDays(1)->format('Y-m-d') . ' 06:00:00', getUserTimezone());
                            
            $text = 'Service has been completed for this week.Please update start date of task in order to save.';
        }

        if ($frequency == 3) {
            $end = new \Carbon\Carbon('first day of next month');
            $end->startOfMonth()->addHours(5)->addMinutes(59)->addSeconds(59);

            $start = \Carbon\Carbon::parse($end->copy()->subDays(1)->format('Y-m-d') . ' 06:00:00', getUserTimezone());

            $text = 'Service has been completed for this month.Please update start date of task in order to save."';
        }

        $isCheckOut = \App\PropertiesCheckIn::query()
            ->select('id')
            ->where('property_id', $request->property_id)
            ->where('check_in_complete', 1)
            ->whereBetween(
                \DB::raw("convert_tz(created_at,'UTC','" . getUserTimezone() . "')"),
                [
                    $start, $end
                ]
            )
        ->first();

        if (!is_null($isCheckOut)) {
            return redirect('tasks')
                ->with(
                    'status',
                    [
                        'title' => 'Tasks',
                        'text' => $text,
                        'class' => 'error',
                    ]
                );
        }
        #1439: Task Management within Trash Scan Comment #35: End

        $template = \App\Tasks::find($id)
            ->update(
                [
                    'name' => $request->taskTitle,
                    'description' => $request->description,
                    'start_date' => \Carbon\Carbon::parse(explode('-', $request->datefilter)[0])->format('Y-m-d'),
                    'end_date' => \Carbon\Carbon::parse(explode('-', $request->datefilter)[1])->format('Y-m-d'),
                    'is_photo' => $request->onoff == 'on' ? 1 : 0,
                    'notify_property_manager' => $request->notify == 'on' ? 1 : 0,
                    'frequency' => $request->frequency,
                    'user_id' => $this->user->id
                ]
            );

        if ($request->property_id) {
            \App\TaskAssign::updateOrCreate(
                [
                    'task_id' => $id,
                ],
                [
                    'property_id' => $request->property_id,
                ]
            );
        } else {
            \App\TaskAssign::where('task_id', $id)->forceDelete();
        }

        $class = ($template) ? 'success' : 'error';
        $message = ($template) ? 'Task updated successfully.'
            : 'Some error occur please try after sometime.';

        $data = [
            'title' => 'Edit Task',
            'text' => $message,
            'class' => $class,
        ];

        return redirect('tasks')
            ->with('status', $data);
    }
}
