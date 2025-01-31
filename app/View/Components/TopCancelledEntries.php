<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class TopCancelledEntries extends Component
{
    public $entries;

    /**
     * Create a new component instance.
     */
    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|\Closure|string
    {
        return view('components.top-cancelled-entries');
    }
}
