@props([
    'type' => 'button',
    'form' => null,
    'href' => null,
    'title' => '',
    'icon' => 'edit',
    'class' => '',
    'color' => 'primary',

])

@if($form)
    <form action="{{ $form['action'] }}" method="{{ $form['method'] ?? 'POST' }}" class="{{ $form['class'] ?? 'd-inline' }}">
        @csrf
        @if(isset($form['spoof']))
            @method($form['spoof'])
        @endif
        <button type="{{ $type }}" class="btn btn-sm btn-{{ $color }} {{ $class }}" title="{{ $title }}">
            <i class="bi bi-{{ $icon }}"></i>
        </button>
    </form>
@elseif($href)
    <a href="{{ $href }}" class="btn btn-sm btn-{{ $color }} {{ $class }}" title="{{ $title }}"  {{ $attributes }} >
           <i class="bi bi-{{ $icon }} me-1"></i> {{ $title }}
    </a>
@else
    <button type="{{ $type }}" class="btn btn-sm btn-{{ $color }} {{ $class }}" title="{{ $title }}">
        <i class="bi bi-{{ $icon }}"></i>
    </button>
@endif
