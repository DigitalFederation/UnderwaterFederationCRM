{{-- Shared international certification page. Actions (validate/suspend/…) post to the user's own
     group routes through x-certification.layout, which already handles international certifications. --}}
@php
    $mainInstructor ??= $certification->mainInstructor->first();
    $assistants ??= $certification->assistantInstructors()->get();
    $showInstructorInfo ??= ! empty($mainInstructor) || $assistants->isNotEmpty();
    // Individuals download through the international card route; federations and admins through their own.
    $cardRouteName = auth()->user()->group->code === 'INDIVIDUAL' ? 'international-certification-card' : 'certification-card';
@endphp
<x-layout>
    <x-certification.layout :certification="$certification" :cardRouteName="$cardRouteName">
        <x-slot name="mainContent">
            <x-certification.validation-actions :certification="$certification" />

            <x-certification.details :certification="$certification" />

            <x-certification.organization-info :certification="$certification" />

            <x-certification.training-entity :certification="$certification" />

            @if ($showInstructorInfo)
                <x-certification.instructor-info :mainInstructor="$mainInstructor" :assistants="$assistants" />
            @endif
        </x-slot>

        <x-slot name="sidebar">
            <div class="space-y-4">
                <x-certification.preview :certification="$certification" />
            </div>
        </x-slot>
    </x-certification.layout>
</x-layout>
