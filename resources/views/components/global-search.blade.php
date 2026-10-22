<div x-data="globalSearch()" class="relative flex-1 max-w-md mx-4">
    <!-- Search Input -->
    <div class="relative">
        <input 
            type="text" 
            x-model="query"
            @input.debounce.300ms="search()"
            @focus="showResults = true"
            @keydown.escape="showResults = false"
            @keydown.down.prevent="navigateResults('down')"
            @keydown.up.prevent="navigateResults('up')"
            @keydown.enter.prevent="selectResult()"
            placeholder="Search students, schools, users..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm"
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <div x-show="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <svg class="animate-spin h-5 w-5 text-[#00bde0]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Search Results Dropdown -->
    <div 
        x-show="showResults && (hasResults() || query.length >= 2)"
        @click.away="showResults = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-2 w-full bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto"
        x-cloak
    >
        <!-- No Results -->
        <div x-show="!loading && query.length >= 2 && !hasResults()" class="p-4 text-center text-gray-500 text-sm">
            No results found for "<span x-text="query"></span>"
        </div>

        <!-- Students Section -->
        <template x-if="results.students && results.students.length > 0">
            <div class="border-b border-gray-200">
                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Students
                </div>
                <template x-for="(student, index) in results.students" :key="student.id">
                    <a 
                        :href="student.url"
                        class="block px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="{ 'bg-gray-100': selectedIndex === getGlobalIndex('students', index) }"
                        @mouseenter="selectedIndex = getGlobalIndex('students', index)"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-[#00bde0] bg-opacity-10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-html="student.highlight"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="student.subtitle"></p>
                                <p class="text-xs text-gray-400 truncate" x-text="student.meta"></p>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        <!-- Schools Section -->
        <template x-if="results.schools && results.schools.length > 0">
            <div class="border-b border-gray-200">
                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Schools
                </div>
                <template x-for="(school, index) in results.schools" :key="school.id">
                    <a 
                        :href="school.url"
                        class="block px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="{ 'bg-gray-100': selectedIndex === getGlobalIndex('schools', index) }"
                        @mouseenter="selectedIndex = getGlobalIndex('schools', index)"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-[#00bde0] bg-opacity-10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-html="school.highlight"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="school.subtitle"></p>
                                <p class="text-xs text-gray-400 truncate" x-text="school.meta"></p>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        <!-- Users Section -->
        <template x-if="results.users && results.users.length > 0">
            <div>
                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Users
                </div>
                <template x-for="(user, index) in results.users" :key="user.id">
                    <a 
                        :href="user.url"
                        class="block px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="{ 'bg-gray-100': selectedIndex === getGlobalIndex('users', index) }"
                        @mouseenter="selectedIndex = getGlobalIndex('users', index)"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-[#00bde0] bg-opacity-10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-html="user.highlight"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="user.subtitle"></p>
                                <p class="text-xs text-gray-400 truncate" x-text="user.meta"></p>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </template>
    </div>
</div>

<script>
function globalSearch() {
    return {
        query: '',
        results: {
            students: [],
            schools: [],
            users: []
        },
        showResults: false,
        loading: false,
        selectedIndex: -1,

        search() {
            if (this.query.length < 2) {
                this.results = { students: [], schools: [], users: [] };
                this.showResults = false;
                return;
            }

            this.loading = true;
            
            fetch(`/search?q=${encodeURIComponent(this.query)}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data;
                    this.showResults = true;
                    this.selectedIndex = -1;
                })
                .catch(error => {
                    console.error('Search error:', error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        hasResults() {
            return (this.results.students && this.results.students.length > 0) ||
                   (this.results.schools && this.results.schools.length > 0) ||
                   (this.results.users && this.results.users.length > 0);
        },

        getAllResults() {
            let all = [];
            if (this.results.students) all = all.concat(this.results.students);
            if (this.results.schools) all = all.concat(this.results.schools);
            if (this.results.users) all = all.concat(this.results.users);
            return all;
        },

        getGlobalIndex(type, index) {
            let offset = 0;
            if (type === 'schools') {
                offset = (this.results.students || []).length;
            } else if (type === 'users') {
                offset = (this.results.students || []).length + (this.results.schools || []).length;
            }
            return offset + index;
        },

        navigateResults(direction) {
            const allResults = this.getAllResults();
            if (allResults.length === 0) return;

            if (direction === 'down') {
                this.selectedIndex = (this.selectedIndex + 1) % allResults.length;
            } else if (direction === 'up') {
                this.selectedIndex = this.selectedIndex <= 0 ? allResults.length - 1 : this.selectedIndex - 1;
            }
        },

        selectResult() {
            const allResults = this.getAllResults();
            if (this.selectedIndex >= 0 && this.selectedIndex < allResults.length) {
                window.location.href = allResults[this.selectedIndex].url;
            }
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
    mark {
        background-color: #fef08a;
        padding: 0;
    }
</style>
