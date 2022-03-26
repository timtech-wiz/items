<?php

namespace App\Http\Livewire;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;

class Items extends Component
{
    public $active;
    public $q;
    public $sortBy = 'id';
    public $sortAsc = true;
    public $item;
    public $confirmItemAdd = false;
    public $confirmItemDeletion = false;

    protected $rules = [
        'item.name' => 'required|string|min:4',
        'item.price' => 'required|numeric|between:1,100',
        'item.status' => 'boolean'
    ];

    protected $queryString = [
        'active' => ['except' => false],
        'q'=> ['except' => ''],
        'sortBy'=> ['except' => 'id'],
        'sortAsc'=> ['except' => true]
    ];

    use WithPagination;

    public function render()
    {
        $items = Item::where('user_id', auth()->user()->id)
        ->when($this->q, function($query){
            $query->where(function($query){
                $query->where('name', 'like', '%'.$this->q .'%')
                ->orWhere('price', 'like', '%'.$this->q .'%');
            });
        })
        ->when($this->active, function($query){
            return $query->active();
        })
        ->orderBy($this->sortBy, $this->sortAsc ? 'ASC' : 'DESC')
        ->paginate(10);
        return view('livewire.items', [
            'items' => $items
        ]);
    }

    public function updatingActive(){
        $this->reset();
    }

    public function updatingQ(){
        $this->reset();
    }

    public function sortBy($field){
        if($field == $this->sortBy){
            $this->sortAsc = !$this->sortAsc;
        }
        $this->sortBy = $field;
    }

    public function confirmItemDeletion($id){

        //$item->delete();
        $this->confirmItemDeletion = $id;

    }

    public function deleteItem(Item $item){

        $item->delete();
        $this->confirmItemDeletion = false;
        session()->flash('message', 'Item Deleted Successfully');
        
    }


    public function confirmItemAdd(){

         $this->reset(['item']);
        $this->confirmItemAdd = true;

    }

    public function confirmItemEdit(Item $item){

        $this->item = $item;
        $this->confirmItemAdd = true;
    }

    public function saveItem(){
        $this->validate();
        if( isset($this->item->id) ){
            $this->item->save();
            session()->flash('message', 'Item Updated Successfully');
        }else{
            auth()->user()->items()->create([
                'name' => $this->item['name'],
                'price' => $this->item['price'],
                'status' => $this->item['status'] ?? 0
            ]);
            session()->flash('message', 'Item Added Successfully');
        }
     
        $this->confirmItemAdd = false;
    }

}
