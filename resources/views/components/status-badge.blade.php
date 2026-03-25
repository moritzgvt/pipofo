@props(['status'])
@php
$classes = match($status) {
    'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'submitted' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'accepted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'declined' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    'corrections' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    default => 'bg-gray-100 text-gray-800',
};
$label = match($status) {
    'draft' => 'Draft',
    'submitted' => 'Submitted',
    'accepted' => 'Accepted',
    'declined' => 'Declined',
    'corrections' => 'Corrections',
    default => ucfirst($status),
};
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $label }}
</span>
