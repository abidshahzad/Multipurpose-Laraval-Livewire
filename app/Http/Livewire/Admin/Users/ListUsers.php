<?php

namespace App\Http\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ListUsers extends Component
{
    /* public $name;
    public $email;
    public $password;
    public $passwordConfirmation; */

    public $state = [];
    public $user;
    public $showEditModal = false;
    public $userBeingRemoved = null;

    public function addNew(){
        $this->state = [];
        $this->showEditModal = false;
        $this->dispatchBrowserEvent('show-form');
    }

    public function createUser(){
        $v = Validator::make($this->state, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ])->validate();

        $v['password'] = bcrypt($v['password']);

        User::create($v);

        //session()->flash('message', 'User added successfully!');

        $this->dispatchBrowserEvent('hide-form', ['message' => 'User added successfully!']);

        return redirect()->back();
    }

    public function edit(User $user){
        $this->showEditModal = true;

        $this->user = $user;

        $this->state = $user->toArray();

        $this->dispatchBrowserEvent('show-form');
    }

    public function updateUser(){
        $v = Validator::make($this->state, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$this->user->id,
            'password' => 'sometimes|confirmed'
        ])->validate();

        if(!empty($v['password']))
            $v['password'] = bcrypt($v['password']);

        $this->user->update($v);

        //session()->flash('message', 'User added successfully!');

        $this->dispatchBrowserEvent('hide-form', ['message' => 'User updated successfully!']);

        return redirect()->back();
    }

    public function confirmUserRemoval($userId){
        $this->userBeingRemoved = $userId;

        $this->dispatchBrowserEvent('show-delete-modal');
    }

    public function deleteUser(){
        $user = User::findOrFail($this->userBeingRemoved);
        $user->delete();
        $this->dispatchBrowserEvent('hide-delete-modal', ['message' => 'User deleted successfully!']);

        return redirect()->back();
    }

    public function render()
    {
        $users = User::latest()->paginate();
        return view('livewire.admin.users.list-users', ['users' => $users]);
    }
}
