@props([
    'label' => 'Submit',
])

<button 
    type="submit"
    {{ $attributes->merge(['class' => 'inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-md shadow focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition']) }}
>
    {{ $label }}
</button>
