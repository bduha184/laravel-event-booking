<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
{

    public $count = 0;

    public function increment(){
        $this->count++;
    }

    public $name = '';

    public function mount(){
        $this->name = 'mount';
    }

    public function updated(){
        $this->name = 'updated';
    }


    public function mouseOver(){
        $this->name = 'mouseover';
    }
    public function mouseLeave(){
        $this->name = 'mouseleave';
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
