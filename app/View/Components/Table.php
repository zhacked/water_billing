<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class Table extends Component
{
    public array $headers;
    public Collection|LengthAwarePaginator $rows;
    public string $editRoute;
    public string $deleteRoute;

    /**
     * Create a new component instance.
     */
    public function __construct(
        array $headers,
        Collection|LengthAwarePaginator $rows,
        string $editRoute = '',
        string $deleteRoute = ''
    ) {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->editRoute = $editRoute ?? '';
        $this->deleteRoute = $deleteRoute ?? '';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.table');
    }
}
