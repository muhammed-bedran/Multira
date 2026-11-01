<?php

namespace Melbedran\Multira\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Melbedran\Multira\Facades\Multira;

class Switcher extends Component
{
    public function __construct(
        public string $separator = '|',
        public bool $native = true,
    ) {}

    public function render(): View
    {
        return view('multira::switcher', [
            'locales' => Multira::getLocalesOrder(),
            'current' => Multira::getCurrentLocale(),
            'separator' => $this->separator,
            'native' => $this->native,
        ]);
    }
}
