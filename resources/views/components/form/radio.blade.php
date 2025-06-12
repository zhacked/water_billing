@props([
    'name',
    'label' => '',
    'options' => [],
    'value' => old($name),
    'required' => false,
])

<div class="xl:col-span-12">
    <span class="inline-block mb-2 text-base font-medium">{{ $label }}</span>
    <div class="space-y-2">
        @foreach($options as $radioValue => $radioLabel)
            <label class="flex items-center space-x-2">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $radioValue }}"
                    {{ $required ? 'required' : '' }}
                    @checked($radioValue == $value)
                    class="form-radio text-custom-500"
                >
                <span>{{ $radioLabel }}</span>
            </label>
        @endforeach
    </div>
    @error($name)
        <span class="text-sm text-red-500 mt-1 block">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
