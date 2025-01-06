<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class CalendarLabel extends Component
{
    public $calendar;

    public $short;

    /**
     * Create a new component instance.
     */
    public function __construct($calendar, $short = false)
    {
        $this->calendar = $calendar;
        $this->short = $short;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|\Closure|string
    {
        return view('components.calendar-label');
    }
}
