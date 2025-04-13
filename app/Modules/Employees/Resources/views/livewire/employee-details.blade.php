<div wire:key="employee-details-{{ $employee->id ?? 'new' }}">
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-800 dark:text-green-100" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-800 dark:text-red-100" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center space-x-6 mb-8">
                <div class="flex-shrink-0">
                    @php
                        $customAvatars = [
                            'pah@indexed.dk' => 'https://www.indexed.dk/assets/profileimages/palle-emailsignatur.png',
                            'peter@indexed.dk' => 'https://www.indexed.dk/assets/profileimages/peter-emailsignatur.png',
                            'meb@indexed.dk' => 'https://www.indexed.dk/assets/profileimages/morten-emailsignatur.png',
                            'mk@indexed.dk' => 'https://www.indexed.dk/assets/profileimages/mathi-emailsignatur.png',
                            'ks@indexed.dk' => 'https://www.indexed.dk/assets/profileimages/kristian-email-signatur_720.png'
                        ];
                        $avatarUrl = $customAvatars[$employee->email] ?? $employee->avatar;
                    @endphp

                    @if($avatarUrl)
                        <img class="h-24 w-24 rounded-full object-cover" 
                             src="{{ $avatarUrl }}" 
                             alt="{{ $employee->name }}">
                    @else
                        <div class="h-24 w-24 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center">
                            <span class="text-indigo-800 dark:text-indigo-100 font-medium text-xl">{{ substr($employee->name, 0, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $employee->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->title ?? 'No title set' }}</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Profile Section -->

                <!-- Holiday Information -->
                {{-- Temporarily removed holiday info component
                <livewire:holidays.holiday-info :employee="$employee" />
                --}}

                <!-- Contact Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Personal Information</h3>
                            <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-5">
                                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->email }}</dd>
                                    </div>
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Work Phone</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->work_phone ?? 'N/A' }}</dd>
                                    </div>
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Private Phone</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->private_phone ?? 'N/A' }}</dd>
                                    </div>
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Birthday</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->birthday ? $employee->birthday->format('M d, Y') : 'N/A' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Employment Details</h3>
                            <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-5">
                                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->title ?? 'N/A' }}</dd>
                                    </div>
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->start_date ? $employee->start_date->format('M d, Y') : 'N/A' }}</dd>
                                    </div>
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->end_date ? $employee->end_date->format('M d, Y') : 'N/A' }}</dd>
                                    </div>
                                    <div class="py-4 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenure</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ number_format($employee->tenure, 1) }} years</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($employee->notes)
                <div class="mt-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Notes</h3>
                    <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $employee->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Projects Section -->
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Current Projects</h3>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-full">
                            {{ count($projects) }} {{ Str::plural('project', count($projects)) }}
                        </span>
                    </div>
                </div>

                @if(count($projects) > 0)
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Link</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($projects as $project)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $project['key'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $project['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <a href="https://indexed.atlassian.net/browse/{{ $project['key'] }}" 
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Active Projects</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            This employee is not currently assigned to any active projects in Jira.
                        </p>
                    </div>
                @endif
            </div>
            <!-- End Projects Section -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        console.log('EmployeeDetails component loaded');
    });
</script>
@endpush