<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\DataUser as Data;
use App\Entities\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Session, DB;

class RolesController extends Controller
{
  public function roles(Request $request)
  {

    $total = $request->total;
    $email = $request->email;
    $cedula = $request->cedula;


    if ($total) {
      $total = $total;
    } else {
      $total = 30;
    }

    $users = Data::leftJoin('users', 'users.id', '=', 'data_users.user_id');

    if ($email) {
      $users = $users->where('email', 'LIKE', '%' . $email . '%');
    } else {
      $users = $users;
    }

    if ($cedula) {
      $users = $users->where('card_id', $cedula)->paginate($page);
    } else {
      $users = $users->paginate($total);
    }

    foreach ($users as $user) {
      $myRol[] = ['id' => $user->id, 'contents' => User::find(Data::where('user_id', $user->user_id)->first()->user_id)->getRoleNames()->toArray()];
    }
    $roles = Role::get()->pluck('name', 'id');
    return view('backend.user.user-rol', compact('users', 'myRol', 'roles'));
  }

  public function roles_store($id, Request $request)
  {
    
    $user = User::find($id); 

    foreach($request->roles as $rol){
        switch ($rol) {
          case 'estudiante':
              $theme = 'skin-blue';
              break;
          case 'docente':
              $theme = 'skin-red';
              break;
          case 'admin':
              $theme = 'skin-green';
              break;
          case 'empresa':
              $theme = 'skin-purple';
              break;
          default:
              $theme = 'skin-blue';    
      }
      $user->update([
        'theme' => $theme
      ]);
    }
  
    $user->syncRoles($request->roles);
    Session::flash('snackbar-success', 'Rol Asignado Correctamente');
    return back();
  }

  public function permission()
  {

    $permissions = DB::table('role_has_permissions')
      ->leftJoin('roles', 'role_id', '=', 'roles.id')
      ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
      ->select('permission_id', 'role_id', 'permissions.name as permission_name', 'roles.name as role_name')
      ->get();
    $roles =  Role::all();
    $allPermission = Permission::all()->pluck('id', 'name');
    return view('backend.user.permission', compact('roles', 'permissions', 'allPermission'));
  }

  public function permission_store(Request $request)
  {
    $response = $request->all();
    Permission::create(['guard_name' => 'web', 'name' => $response['name']]);
    Session::flash('snackbar-success', 'Permiso creado Correctamente');
    return back();
    /**/
  }

  public function assign(Request $request)
  {
    $response = $request->all();
    //dd($response);
    $role = Role::findByName($response['rol']);
    $role->syncPermissions($response['roles']);
    Session::flash('snackbar-success', 'Permiso se a Asignado Correctamente');
    return back();
  }
}
