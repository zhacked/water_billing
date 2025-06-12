@props([
    'name',
    'label' => '',
    'value' => old($name),
    'placeholder' => '',
    'required' => false,
])

<div class="xl:col-span-12">
    <label for="{{ $name }}" class="inline-block mb-2 text-base font-medium">{{ $label }}</label>
    <input
        type="date"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input border-slate-200 dark:border-zink-500 dark:bg-zink-700']) }}
    >
    @error($name)
        <span class="text-sm text-red-500 mt-1 block">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
