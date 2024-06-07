<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Property;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        /*
        * Customer can handel the property manager add,edit,delete,
        * view etc functionality because it's under the manage
        * customer in nav bar.
        */
        $this->middleware('RoleAndPermission:customers');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_history(Request $request)
    {
        $cusDetails = \App\Customer::where('subscriber_id', $this->user->subscriber_id)
                ->latest()->get();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($cusDetails);
        $perPage = 10;
        $currentPageItems = $itemCollection
                ->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($itemCollection), $perPage);
        $this->data['customers'] = $paginatedItems->setPath($request->url());

        return view('payment/payment_history', $this->data);
    }

    public function payment_detail($id)
    {
        return view('payment/payment_detail', $this->data);
    }

    public function current_plan()
    {
        return view('payment/current_plan', $this->data);
    }
}
