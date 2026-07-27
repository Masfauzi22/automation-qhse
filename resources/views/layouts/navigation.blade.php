<nav class="w-64 shrink-0 h-screen bg-white border-r border-gray-100 flex flex-col">
    <!-- Logo Perusahaan (Fixed, tidak ikut scroll) -->
    <div class="shrink-0 flex items-center justify-center h-16 border-b border-gray-100">
        <a href="{{ route('dashboard') }}" class="font-bold text-xl text-gray-800">
            AQHSE
        </a>
    </div>

    <!-- Area Links Menu (Scrollable) -->
    <div class="flex-1 overflow-y-auto flex flex-col w-full mt-6 space-y-1 px-3">

        <!-- Menu Dashboard (Single) -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Menu Document Control -->
        <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('documents.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            <span>Document Control</span>
        </a>

        <!-- 1. Menu Manajemen Akses -->
        @role('superadmin')
        <div x-data="{ open: {{ request()->routeIs('users.*', 'roles.*') ? 'true' : 'false' }} }" class="w-full">
            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('users.*', 'roles.*') ? 'text-gray-900 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition focus:outline-none">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Manajemen Akses</span>
                </span>
                <svg class="w-4 h-4 transition-transform duration-200 transform shrink-0" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-1 pl-4">
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('users.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span>Data User</span>
                </a>
                <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('roles.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                    <span>Data Role</span>
                </a>
            </div>
        </div>
        @endrole

        <!-- 1.5 Menu Master Data -->
        @hasanyrole('superadmin|qhse')
        <div x-data="{ open: {{ request()->routeIs('sampling-subjects.*', 'sampling-parameters.*') ? 'true' : 'false' }} }" class="w-full">
            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('sampling-subjects.*', 'sampling-parameters.*') ? 'text-gray-900 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition focus:outline-none">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    <span>Master Data</span>
                </span>
                <svg class="w-4 h-4 transition-transform duration-200 transform shrink-0" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-1 pl-4">
                <a href="{{ route('sampling-subjects.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('sampling-subjects.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z" />
                    </svg>
                    <span>Kategori Subject</span>
                </a>
                <a href="{{ route('sampling-parameters.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('sampling-parameters.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 010 3m0-3a1.5 1.5 0 000 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 010 3m0-3a1.5 1.5 0 000 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 010 3m0-3a1.5 1.5 0 000 3m0 9.75V10.5" />
                    </svg>
                    <span>Parameter Uji</span>
                </a>
            </div>
        </div>
        @endhasanyrole

        <!-- 2. Menu Request QHSE (Termasuk pic_dept) -->
        @hasanyrole('superadmin|qhse|pekerja|medis|ga|pic_dept')
        <div x-data="{ open: {{ request()->routeIs('fit-to-work.*', 'permit-to-work.*', 'ppe.*', 'simper.*') ? 'true' : 'false' }} }" class="w-full">
            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('fit-to-work.*', 'permit-to-work.*', 'ppe.*', 'simper.*') ? 'text-gray-900 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition focus:outline-none">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 15a8 8 0 1116 0M2.5 16.5h19a1 1 0 011 1v.5a1 1 0 01-1 1h-19a1 1 0 01-1-1v-.5a1 1 0 011-1zM11 4v3M9 4h6" />
                    </svg>
                    <span>Request QHSE</span>
                </span>
                <svg class="w-4 h-4 transition-transform duration-200 transform shrink-0" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-1 pl-4">
                
                <a href="{{ route('fit-to-work.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('fit-to-work.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2-7 4 14 2-7h6" />
                    </svg>
                    <span>Fit to Work</span>
                </a>
                
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('permit-to-work.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-7.5 8.5l2 2 4-4" />
                    </svg>
                    <span>Permit to Work</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('ppe.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 4L5 6v13a1 1 0 001 1h3V6.5M16 4l3 2v13a1 1 0 01-1 1h-3V6.5M8 4a4 4 0 008 0M9 10h1m4 0h1m-6 4h1m4 0h1" />
                    </svg>
                    <span>PPE (Equipment)</span>
                </a>

                <a href="{{ route('simper.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('simper.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <circle cx="8.5" cy="10.5" r="2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 16c0-1.5 1.2-2.5 3-2.5s3 1 3 2.5M14 9h5M14 12.5h5M14 16h3" />
                    </svg>
                    <span>SIMPER</span>
                </a>
            </div>
        </div>
        @endhasanyrole

        <!-- 3. Menu Tasklist -->
        @hasanyrole('superadmin|qhse|pekerja|medis|ga')
        <div x-data="{ open: {{ request()->routeIs('hazard-report.*', 'investigation.*', 'request-qhse.*') ? 'true' : 'false' }} }" class="w-full">
            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('hazard-report.*', 'investigation.*', 'request-qhse.*') ? 'text-gray-900 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition focus:outline-none">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6a1 1 0 011 1V6h1a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V8a2 2 0 012-2h1V5.5a1 1 0 011-1zM9 12h6M9 15.5h6M9 8.5h6" />
                    </svg>
                    <span>Tasklist</span>
                </span>
                <svg class="w-4 h-4 transition-transform duration-200 transform shrink-0" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-1 pl-4">
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('hazard-report.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008M10.29 3.86L1.82 18a1.5 1.5 0 001.29 2.25h17.78a1.5 1.5 0 001.29-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z" />
                    </svg>
                    <span>Hazard Report</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('investigation.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0a7 7 0 10-9.9-9.9 7 7 0 009.9 9.9z" />
                    </svg>
                    <span>Investigation</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('request-qhse.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M9 4.5h6a1 1 0 011 1V6h1a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V8a2 2 0 012-2h1V5.5a1 1 0 011-1z" />
                    </svg>
                    <span>Request QHSE</span>
                </a>
            </div>
        </div>
        @endhasanyrole

        <!-- 4. Menu Environmental Monitoring -->
        @hasanyrole('superadmin|qhse|ga')
        <div x-data="{ open: {{ request()->routeIs('daily-monitoring.*', 'env-sampling.*', 'waste-lb3.*', 'waste-sampah.*', 'waste-domestic.*') ? 'true' : 'false' }} }" class="w-full">
            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('daily-monitoring.*', 'env-sampling.*', 'waste-lb3.*', 'waste-sampah.*', 'waste-domestic.*') ? 'text-gray-900 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition focus:outline-none">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-1-8-4.5-8-9.5C4 6 8 3 12 3s8 3 8 8.5c0 5-3.5 8.5-8 9.5zM12 21V9m0 0c2.5-1.5 4-3.5 4-6M12 9c-2.5-1.5-4-3.5-4-6" />
                    </svg>
                    <span>Environmental</span>
                </span>
                <svg class="w-4 h-4 transition-transform duration-200 transform shrink-0" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-1 pl-4">
                
                @hasanyrole('superadmin|qhse')
                <a href="{{ route('daily-monitoring.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('daily-monitoring.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6h15a.75.75 0 01.75.75V19.5a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V6.75A.75.75 0 014.5 6zM12 12.75v3l2 1.25" />
                    </svg>
                    <span>Daily Monitoring</span>
                </a>
                <a href="{{ route('env-sampling.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('env-sampling.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 2.25h6M9.75 2.25v6.44a2 2 0 01-.386 1.183L4.9 16.03A2.5 2.5 0 006.9 20h10.2a2.5 2.5 0 002-3.97l-4.464-6.157a2 2 0 01-.386-1.183V2.25M8.25 14.25h7.5" />
                    </svg>
                    <span>Sampling</span>
                </a>
                <a href="{{ route('waste-lb3.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('waste-lb3.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15l-1.06 12.106A2 2 0 0116.45 20.6H7.55a2 2 0 01-1.99-1.744L4.5 6.75zM3.75 6.75h16.5M9 6.75V4.5a1 1 0 011-1h4a1 1 0 011 1v2.25M12 10.5v6M9 12l3 1.5 3-1.5" />
                    </svg>
                    <span>Waste Management (LB3)</span>
                </a>
                @endhasanyrole

                <a href="{{ route('waste-domestic.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('waste-sampah.*', 'waste-domestic.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M9 6.75V4.5a1 1 0 011-1h4a1 1 0 011 1v2.25m4 0-.72 12.24a2 2 0 01-2 1.86H8.72a2 2 0 01-2-1.86L6 6.75M10 10.5v6M14 10.5v6" />
                    </svg>
                    <span>Waste Management (Sampah)</span>
                </a>
            </div>
        </div>
        @endhasanyrole

        <!-- 5. Menu Personal Record -->
        @hasanyrole('superadmin|qhse|pekerja|medis|ga')
        <div x-data="{ open: {{ request()->routeIs('my-hazard-report.*', 'my-investigation.*', 'safety-talk.*', 'training.*', 'my-simper.*') ? 'true' : 'false' }} }" class="w-full">
            <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 mt-1 text-sm font-medium {{ request()->routeIs('my-hazard-report.*', 'my-investigation.*', 'safety-talk.*', 'training.*', 'my-simper.*') ? 'text-gray-900 bg-gray-50' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md transition focus:outline-none">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Personal Record</span>
                </span>
                <svg class="w-4 h-4 transition-transform duration-200 transform shrink-0" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-1 pl-4">
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('my-hazard-report.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008M10.29 3.86L1.82 18a1.5 1.5 0 001.29 2.25h17.78a1.5 1.5 0 001.29-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z" />
                    </svg>
                    <span>Hazard Report</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('my-investigation.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0a7 7 0 10-9.9-9.9 7 7 0 009.9 9.9z" />
                    </svg>
                    <span>Investigation</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('safety-talk.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84L4.5 13.5v-3l5.84-2.34a13.5 13.5 0 007.66-3.66v14A13.5 13.5 0 0010.34 15.84zM4.5 13.5v6h2.25m10.5-15.75v2.25m2.25 2.25H21m-2.06 4.19l1.56 1.56M18.94 8.31l1.56-1.56" />
                    </svg>
                    <span>Safety Talk</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm {{ request()->routeIs('training.*') ? 'bg-gray-100 text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }} rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L2.25 8.25 12 13.5l9.75-5.25L12 3zM4.5 9.75v6c0 .414 1 1.25 3 2s3.5.75 4.5.75 2.5 0 4.5-.75 3-1.586 3-2v-6" />
                    </svg>
                    <span>Training</span>
                </a>
            </div>
        </div>
        @endhasanyrole

    </div>
</nav>