<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportIssueController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('RoleAndPermission:report');
    }

    public function index()
    {

        $reportIssue = \App\ReportIssue::when(
            $this->user->hasRole('admin')
                && !$this->user->hasRole('property_manager'),
            function ($query) {
                $query->where('subscribers_id', $this->user->subscriber_id);
            }
        )
        ->when(
            !$this->user->hasRole('admin')
                && !$this->user->hasRole('property_manager'),
            function ($query) {
                $query->where('user_id', $this->user->id);
            }
        )
        ->when(
            !$this->user->hasRole('admin')
                && $this->user->hasRole('property_manager'),
            function ($query) {
                    $query->whereIn(
                        'property_id',
                        function ($query) {
                            $query->select('property_id')
                                ->from('user_properties')
                                ->where('user_id', $this->user->id)
                                ->whereNull('deleted_at');
                        }
                    );
            }
        )
        ->with(
            [
                'getUser' => function ($query) {
                    $query->withTrashed();
                },
                'getProperty' => function ($query) {
                    $query->withTrashed();
                },
                'getBuilding' => function ($query) {
                    $query->withTrashed();
                },
                'getReportReason' => function ($query) {
                    $query->withTrashed();
                }
            ]
        )
        ->latest()
        ->withTrashed()
        ->paginate(15);

        $this->data['reportIssue'] = $reportIssue;
        return view('report.reportissue', $this->data);
    }

    /**
     * Mark Issue Exclude
     *
     *
     */
    public function markIssueExclude($id)
    {

        \App\ReportIssue::where('id', $id)->update(['status' => 1]);

        $report = \App\ReportIssue::find($id);

        $excluded = \App\ExcludedProperty::create(
            [
                'property_id' => $report->property_id,
                'building_id' => $report->building_id,
                'exclude_date' => $report->issue_date,
                'report_issue_id' => $id,
            ]
        );

        $class = ($excluded) ? 'success'
                    : 'error';
        $message = ($excluded) ? 'Exception added successfully'
                     : 'Exception added failed.';
        $data = array(
            'title' => 'Property',
            'text' => $message,
            'class' => $class
        );
        
        return redirect('reported-issue');
    }

    public function listReportIssueReason()
    {
        $reportIssue = \App\IssueReason::where(
            'user_id',
            $this->user->subscriber_id
        )->latest()->paginate(10);

        $this->data['reportIssue'] = $reportIssue;
        return view('report.listissuereason', $this->data);
    }

    public function createIssueReason()
    {
        return view('report.createissuereason', $this->data);
    }

    public function editIssueReason($id)
    {
        //Check permission:Start
        if ($this->checkExceptionPermission($id)) {
            return redirect('unauthorized');
        }
        //Check permission:End
        
        $reason = \App\IssueReason::findOrFail($id);
        $this->data['reason'] = $reason;
        return view('report.editissuereason', $this->data);
    }

    public function updateIssueReason(Request $request, $id)
    {

        $this->validate(
            $request,
            [
            'reason' => 'required',
            ],
            [
            'reason.required' => 'The exception type field is required.'
            ]
        );

        $role = \App\IssueReason::find($id);
        $role->reason = $request->reason;
        $status = $role->save();

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Exception type updated successfully.'
                : 'Some error occur please try after somr time.';
        $data = array(
            'title' => 'Exception Type',
            'text' => $message,
            'class' => $class
        );

        return redirect('report-issue-reason')->with('status', $data);
    }

    public function storeIssueReason(Request $request)
    {
        
        $this->validate(
            $request,
            [
                'reason' => 'required',
            ],
            [
                'reason.required' => 'The exception type field is required.'
            ]
        );

        $status = \App\IssueReason::create(
            [
                    'reason' => $request->reason,
                    'user_id' => $this->user->subscriber_id
            ]
        );

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Exception type added successfully.'
                : 'Some error occur please try after somr time.';
        $data = array(
            'title' => 'Exception Type',
            'text' => $message,
            'class' => $class
        );

        return redirect('report-issue-reason')
                    ->with('status', $data);
    }

    public function issueReasonDestory($id)
    {
        //Check permission:Start
        if ($this->checkExceptionPermission($id)) {
            return redirect('unauthorized');
        }
        //Check permission:End

        $status = \App\IssueReason::where('id', $id)->delete();
        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Exception deleted successfully.'
                : 'Some error occur please try after somr time.';
        $data = array(
            'title' => 'Exception Type',
            'text' => $message,
            'class' => $class
        );
        
        return redirect('report-issue-reason')->with('status', $data);
    }
}
