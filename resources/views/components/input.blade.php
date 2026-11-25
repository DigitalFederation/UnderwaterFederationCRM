@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[#7FA1C3] focus:border-[#6482AD] focus:ring-3 focus:ring-[#7FA1C3]/30  rounded-lg shadow-xs w-full']) !!}>
