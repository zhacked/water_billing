@props([
    'label' => '',
    'name',
    'id' => $name,
    'value' => '',
    'placeholder' => '',
    'rows' => 3,
])

<div class="xl:col-span-12">
    @if ($label)
        <label for="{{ $id }}" class="inline-block mb-2 text-base font-medium">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        {{ $attributes }}
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        aria-describedby="@error($name) {{ $name }}-error @enderror"
        class="form-textarea
            w-full
            border border-slate-200
            dark:border-zink-500
            rounded-md
            px-3 py-2
            text-sm
            dark:text-zink-100
            dark:bg-zink-700
            placeholder:text-slate-400
            dark:placeholder:text-zink-200
            focus:outline-none
            focus:ring-2
            focus:ring-custom-500
            focus:border-custom-500
            transition
            duration-200
            ease-in-out
            resize-y
            disabled:bg-slate-100
            dark:disabled:bg-zink-600
            disabled:border-slate-300
            dark:disabled:border-zink-500
            disabled:text-slate-500
            dark:disabled:text-zink-200
            @error($name) border-red-500 ring-red-500 @enderror"
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <span id="{{ $name }}-error" class="invalid-feedback text-sm text-red-500 mt-1 block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
