@if(session('success'))
    <div class="flash-message flash-message-success mb-4 rounded-md bg-green-50 dark:bg-green-900/50 p-4" role="alert">
        <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
    </div>
@endif
@if(session('error'))
    <div class="flash-message flash-message-error mb-4 rounded-md bg-red-50 dark:bg-red-900/50 p-4" role="alert">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
    </div>
@endif
