@props([
    'name',
    'label' => '',
    'options' => [],
    'value' => old($name, []),
])

<div class="xl:col-span-12">
    <span class="inline-block mb-2 text-base font-medium">{{ $label }}</span>
    <div class="space-y-2">
        @foreach($options as $checkboxValue => $checkboxLabel)
            <label class="flex items-center space-x-2">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $checkboxValue }}"
                    @checked(in_array($checkboxValue, (array)$value))
                    class="form-checkbox text-custom-500"
                >
                <span>{{ $checkboxLabel }}</span>
            </label>
        @endforeach
    </div>
    @error($name)
        <span class="text-sm text-red-500 mt-1 block">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
