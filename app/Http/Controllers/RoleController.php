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
use Illuminate\Validation\Rule;

class RoleController extends BaseController
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

        //Only those admin can access this
        //panel which is created by super admin:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Only those admin can access this
        //panel which is created by super admin:End

        $users = User::where('subscriber_id', '=', $this->user->subscriber_id)
                ->get()->pluck('id');
        
        $roles = Role::whereIn(
            'user_id',
            function (
                $query
            ) {
                $query->select('id')
                    ->from('users')
                    ->where(
                        [
                            'subscriber_id' => $this->user->subscriber_id
                        ]
                    );
            }
        )->latest()->paginate(10);

        $this->data['roles'] = $roles;
        return view('role.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        //Only thoes admin can access
        //this panel which is created by super admin:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Only thoes admin can access
        //this panel which is created by super admin:End

        $permission = Permission::get()->toArray();
        $map = array();
        
        foreach ($permission as $node) {
            /* init self */
            if (!array_key_exists($node['id'], $map)) {
                $map[$node['id']] = array('self' => $node['display_name']);
            } else {
                $map[$node['id']]['self'] = $node['display_name'];
            }

            /* init parent */
            if (!array_key_exists($node['parent_id'], $map)) {
                $map[$node['parent_id']] = array();
            }

            /* add to parent */
            $map[$node['parent_id']][$node['id']] = & $map[$node['id']];
        }

        $this->data['break'] = (int) (count($map[0]) / 3);
        $this->data['permission'] = $map[0];
        return view('role.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //Only thoes admin can access
        // this panel which is created by super admin:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Only thoes admin can access
        // this panel which is created by super admin:End

        $this->validate(
            $request,
            [
                'display_name' => [
                'required',
                Rule::unique('roles')
                ->where('user_id', $this->user->id)->whereNull('deleted_at')
                ],
                'description' => 'required',
                'permission' => 'required',
            ]
        );

        if ($this->checkRoleName($request->input('display_name'))) {
            return redirect('role/create')
                    ->with('display_name', 'Display Name contain unique.');
        }


        $role = new Role();
        $role->name = strtolower($request->input('display_name'));
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');
        $role->user_id = Auth::user()->id;
        $status = $role->save();
     
        foreach ($request->input('permission') as $key => $value) {
            $role->attachPermission($value);
        }

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Role created successfully.'
                    : 'Role creation failed.';
        $data = array('title' => 'Role', 'text' => $message, 'class' => $class);

        return redirect('role')
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        //Only thoes admin can access
        //this panel which is created by super admin:Start
        if (!$this->user->hasRole(['admin']) || $this->checkRolePermission($id)) {
            return redirect('unauthorized');
        }
        //Only thoes admin can access
        //this panel which is created by super admin:End
        
        $role = Role::whereIn(
            'user_id',
            function ($query) {
                    $query->select('id')
                    ->from('users')
                    ->whereNull('deleted_at')
                    ->where('subscriber_id', $this->user->subscriber_id);
            }
        )
        ->where('id', $id)->get();

        if ($role->isEmpty()) {
            return redirect('role');
        }

        $role = Role::find($id);
        $map = array();
        $permission = Permission::get()->toArray();

        foreach ($permission as $node) {
            /* init self */
            if (!array_key_exists($node['id'], $map)) {
                $map[$node['id']] = array('self' => $node['display_name']);
            } else {
                $map[$node['id']]['self'] = $node['display_name'];
            }

            /* init parent */
            if (!array_key_exists($node['parent_id'], $map)) {
                $map[$node['parent_id']] = array();
            }

            /* add to parent */
            $map[$node['parent_id']][$node['id']] = & $map[$node['id']];
        }

        $rolePermissions = \DB::table("permission_role")
                ->where("permission_role.role_id", $id)
                ->pluck('permission_role.permission_id', 'permission_role.permission_id')
                ->toArray();

        $this->data['role'] = $role;
        $this->data['permission'] = $map[0];
        $this->data['rolePermissions'] = $rolePermissions;
        return view('role.edit', $this->data);
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

        //Only thoes admin can access
        //this panel which is created by super admin:Start
        if (!$this->user->hasRole(['admin'])) {
            return redirect('unauthorized');
        }
        //Only thoes admin can access
        //this panel which is created by super admin:End

        $this->validate(
            $request,
            [
                'display_name'
                => 'required|unique:roles,display_name,' . $id . ',id,user_id,'
                . $this->user->id . ',deleted_at,NULL',
                'description' => 'required',
                'permission' => 'required',
             ]
        );

        $role = Role::find($id);

        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');

        $status = $role->save();

        User::activeLog('Role Updated', $this->user->id, null, $request->ip(), null);

        DB::table("permission_role")->where("permission_role.role_id", $id)
                ->delete();

        foreach ($request->input('permission') as $key => $value) {
            $role->attachPermission($value);
        }

        $class = ($status) ? 'success' : 'error';
        $message = ($status) ? 'Role updated successfully.'
                    : 'Role updation failed.';
        $data = array(
            'title' => 'Role',
            'text' => $message,
            'class' => $class
        );

        return redirect('role')
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

        //Only thoes admin can access
        // this panel which is created by super admin:Start
        if (!$this->user->hasRole(['admin']) || $this->checkRolePermission($id)) {
            return redirect('unauthorized');
        }
        //Only thoes admin can access
        // this panel which is created by super admin:End

        $checkExistingRole = \DB::table("role_user")
                ->where('role_id', $id)->first();
        
        if ($checkExistingRole) {
            $class = 'error';
            $message = 'Cannot delete this role '
                    . 'as it has been assigned to a user.';
            $data = array(
                'title' => 'Role',
                'text' => $message,
                'class' => $class
            );

            return redirect()->route('role.index')->with('status', $data);
        } else {
            \App\Role::destroy($id);

            $class = 'success';
            $message = 'Role deleted successfully.';
            $data = array(
                'title' => 'Role',
                'text' => $message,
                'class' => $class
            );

            return redirect()->route('role.index')->with('status', $data);
        }
    }

    private function checkRoleName($displayName)
    {

        $role = Role::where(
            [
                'user_id' => Auth::user()->id,
                'display_name' => $displayName
            ]
        )->count();

        if ($role > 0) {
            return 1;
        } else {
            return 0;
        }
    }
}
