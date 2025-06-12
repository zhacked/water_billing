@props([
    'name',
    'label' => '',
    'options' => [],
    'value' => old($name),
    'placeholder' => '-- Select --',
    'required' => false,
])

<div class="form-group">
    @if($label)
        <label for="{{ $name }}">{{ $label }}</label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        class="form-control {{ $errors->has($name) ? 'is-invalid' : '' }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($optionValue == $value)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <span class="error invalid-feedback">{{ $message }}</span>
    @enderror
</div>
