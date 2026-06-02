<?php

namespace App\Livewire\Company\Pages;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.company.pages.dashboard')
            ->layout('company.layouts.app');
    }
}
