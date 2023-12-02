<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\State;
use App\Models\Company;
use App\Http\Controllers\NotificationController as Notify; 
use Validator, Auth;

class StateController extends Controller
{
    public static function state_store($data)
    {
        $validator = Validator::make($data, State::$rules);

        if ($validator->fails()) {
            return $validator->errors()->toJson();
        } else {
            State::create($data);
            return 'ok';
        }
    }

    public static function state_update($id, $data)
    {
        $validator = Validator::make($data, State::$rules);
        if ($validator->fails()) {
            return $validator->errors()->toJson();
        } else {
            State::where('id', $id)->update($data);
            return 'ok';
        }
    }

    // public function state_show()
    // {
    //     $states = State::all();
    // view: backend.laboratory.state', compact('states'));
    // }

    // public function state_edit($id)
    // {
    //     $states = State::where('id', $id)->first();
    //     view: backend.laboratory.state_edit', compact('states'));
    // }

    // public function state_delete($id)
    // {
    //     $states = State::findOrFail($id);
    //     $states->delete();
    //     Session::flash('snackbar-success', 'Se a Elimninado el Estado');
    //     return Redirect::to('/backend/state/');
    // }

    public function reportCompanyUser(Request $request)
    {
        $page = $request->page;
        $total = $request->total;
        $email = $request->email;
        $cedula = $request->cedula;

        if ($total) {
            $total = $total;
        } else {
            $total = 30;
        }

        if (Auth::user()->hasRole('admin')) {
            $lists = User::leftJoin('data_users', 'user_id', '=', 'users.id')
                ->leftJoin(
                    'companies',
                    'data_users.company_id',
                    '=',
                    'companies.id'
                )
                ->orderBy('users.id', 'asc')
                ->select(
                    'first',
                    'last',
                    'email',
                    'company',
                    'users.id as id',
                    'data_users.company_id',
                    'data_users.id as id_data',
                    'data_users.mobile',
                    'data_users.address',
                    'type_card',
                    'card_id',
                    'data_users.neighborhood'
                );
            if ($email) {
                $lists = $lists->where('email', 'LIKE', '%' . $email . '%');
            } else {
                $lists = $lists;
            }

            if ($cedula) {
                $lists = $lists->where('card_id', $cedula);
            } else {
                $lists = $lists;
            }

            $lists = $lists->get();
            foreach ($lists as $key => $list) {
                $users[] = [
                    'first' => $list->first,
                    'last' => $list->last,
                    'email' => $list->email,
                    'company' => $list->company,
                    'id' => $list->id,
                    'company_id' => $list->company_id,
                    'id_data' => $list->id_data,
                    'mobile' => $list->mobile,
                    'address' => $list->address,
                    'type_card' => $list->type_card,
                    'card_id' => $list->card_id,
                    'neighborhood' => $list->neighborhood,
                    'count-list' => count($list->state) > 0 ? 1 : 0,
                    'list' => count($list->state) > 0 ? $list->state : 0,
                ];
            }
        } else {
            $companies = Company::where(
                'id',
                Auth::user()
                    ->datauser()
                    ->first()->company_id
            )
                ->orWhere(
                    'parent',
                    Auth::user()
                        ->datauser()
                        ->first()->company_id
                )
                ->get();
            foreach ($companies as $comp) {
                $lists = User::leftJoin(
                    'data_users',
                    'user_id',
                    '=',
                    'users.id'
                )
                    ->leftJoin(
                        'companies',
                        'data_users.company_id',
                        '=',
                        'companies.id'
                    )
                    ->orderBy('users.id', 'asc')
                    ->select(
                        'first',
                        'last',
                        'email',
                        'company',
                        'users.id as id',
                        'data_users.company_id',
                        'data_users.id as id_data',
                        'data_users.mobile',
                        'data_users.address',
                        'type_card',
                        'card_id',
                        'data_users.neighborhood'
                    )
                    ->where('data_users.company_id', $comp->id);

                if ($email) {
                    $lists = $lists->where('email', 'LIKE', '%' . $email . '%');
                } else {
                    $lists = $lists;
                }

                if ($cedula) {
                    $lists = $lists->where('card_id', $cedula);
                } else {
                    $lists = $lists;
                }

                $lists = $lists->get();
                foreach ($lists as $key => $list) {
                    $users[] = [
                        'first' => $list->first,
                        'last' => $list->last,
                        'email' => $list->email,
                        'company' => $list->company,
                        'id' => $list->id,
                        'company_id' => $list->company_id,
                        'id_data' => $list->id_data,
                        'mobile' => $list->mobile,
                        'address' => $list->address,
                        'type_card' => $list->type_card,
                        'card_id' => $list->card_id,
                        'neighborhood' => $list->neighborhood,
                        'count-list' => count($list->state) > 0 ? 1 : 0,
                        'list' => count($list->state) > 0 ? $list->state : 0,
                    ];
                }
            }
        }

        $count = count($users) / $total + 1;
        $users = collect($users)
            ->reverse('id')
            ->forPage($page, $total);
        // view: backend.user.user-list-company
        return Notify::ms(
            'ok',
            201,
            [$users, $count],
            'Se a listado correctamente'
        );
    }
}
