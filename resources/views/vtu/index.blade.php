<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">VTU & Bills</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm sm:text-base mt-0.5">Buy airtime, data, cable TV, and electricity from your wallet.</p>
            </div>
            <div class="px-4 py-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Balance</span>
                <span class="ml-2 font-bold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount($walletBalance) }}</span>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-6">
        @if (session('message'))
            <div class="rounded-2xl bg-mint-50 dark:bg-mint-900/20 border border-mint-200 dark:border-mint-800 px-4 sm:px-5 py-3.5 text-mint-800 dark:text-mint-200 text-sm font-medium">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 sm:px-5 py-3.5 text-red-800 dark:text-red-200 text-sm font-medium">{{ session('error') }}</div>
        @endif

        @unless($enabled && $configured)
            <div class="rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30 px-5 py-4 text-sm text-amber-900 dark:text-amber-200">
                VTU is not available right now. Please contact support.
            </div>
        @endunless

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
                x-data="{
                    type: '{{ old('type', $initialType ?? 'airtime') }}',
                    serviceId: '{{ old('service_id', 'mtn') }}',
                    serviceOpen: false,
                    serviceSearch: '',
                    loading: false,
                    plans: [],
                    planPeriod: 'days',
                    planOpen: false,
                    planSearch: '',
                    selectedPlan: '',
                    amount: '{{ old('amount') }}',
                    serviceGroups: {
                        airtime: [
                            { id: 'mtn', name: 'MTN', logo: '{{ asset('images/vtu/mtn.png') }}', classes: 'bg-yellow-400 text-slate-950' },
                            { id: 'airtel', name: 'Airtel', logo: '{{ asset('images/vtu/airtel.png') }}', classes: 'bg-red-600 text-white' },
                            { id: 'glo', name: 'Glo', logo: '{{ asset('images/vtu/glo.png') }}', classes: 'bg-green-600 text-white' },
                            { id: '9mobile', name: '9mobile', logo: '{{ asset('images/vtu/9mobile.png') }}', classes: 'bg-lime-500 text-slate-950' },
                        ],
                        data: [
                            { id: 'mtn', name: 'MTN', logo: '{{ asset('images/vtu/mtn.png') }}', classes: 'bg-yellow-400 text-slate-950' },
                            { id: 'airtel', name: 'Airtel', logo: '{{ asset('images/vtu/airtel.png') }}', classes: 'bg-red-600 text-white' },
                            { id: 'glo', name: 'Glo', logo: '{{ asset('images/vtu/glo.png') }}', classes: 'bg-green-600 text-white' },
                            { id: '9mobile', name: '9mobile', logo: '{{ asset('images/vtu/9mobile.png') }}', classes: 'bg-lime-500 text-slate-950' },
                        ],
                        cable: [
                            { id: 'dstv', name: 'DStv', logo: 'D', classes: 'bg-blue-600 text-white' },
                            { id: 'gotv', name: 'GOtv', logo: 'GO', classes: 'bg-emerald-600 text-white' },
                            { id: 'startimes', name: 'StarTimes', logo: 'ST', classes: 'bg-orange-500 text-white' },
                        ],
                        electricity: [
                            { id: 'ikeja-electric', name: 'Ikeja', logo: 'IK', classes: 'bg-amber-500 text-white' },
                            { id: 'eko-electric', name: 'Eko', logo: 'EK', classes: 'bg-cyan-600 text-white' },
                            { id: 'abuja-electric', name: 'Abuja', logo: 'AB', classes: 'bg-violet-600 text-white' },
                            { id: 'kano-electric', name: 'Kano', logo: 'KN', classes: 'bg-rose-600 text-white' },
                            { id: 'portharcourt-electric', name: 'Port Harcourt', logo: 'PH', classes: 'bg-teal-600 text-white' },
                        ],
                    },
                    currentServices() {
                        return this.serviceGroups[this.type] || [];
                    },
                    filteredServices() {
                        const q = (this.serviceSearch || '').toLowerCase().trim();
                        if (!q) return this.currentServices();
                        return this.currentServices().filter((service) => {
                            return String(service.name || '').toLowerCase().includes(q)
                                || String(service.id || '').toLowerCase().includes(q);
                        });
                    },
                    selectedService() {
                        return this.currentServices().find((service) => service.id === this.serviceId) || this.currentServices()[0] || null;
                    },
                    setType(nextType) {
                        this.type = nextType;
                        this.plans = [];
                        this.selectedPlan = '';
                        this.amount = '';
                        this.serviceOpen = false;
                        this.serviceSearch = '';
                        this.serviceId = (this.currentServices()[0] || {}).id || '';
                        if (this.type === 'data') setTimeout(() => this.loadPlans(), 50);
                    },
                    setService(id) {
                        this.serviceId = id;
                        this.selectedPlan = '';
                        this.serviceOpen = false;
                        this.serviceSearch = '';
                        if (this.type === 'data') this.loadPlans();
                    },
                    planText(plan) {
                        return [
                            plan.name,
                            plan.variation_name,
                            plan.plan,
                            plan.validity,
                            plan.duration,
                            plan.description,
                        ].filter(Boolean).join(' ').toLowerCase();
                    },
                    planMatchesPeriod(plan) {
                        const text = this.planText(plan);
                        if (!text) return true;
                        if (this.planPeriod === 'year') return /year|yearly|365\s*day/.test(text);
                        if (this.planPeriod === 'month') return /month|monthly|28\s*day|30\s*day|31\s*day/.test(text);
                        if (this.planPeriod === 'week') return /week|weekly|7\s*day|14\s*day/.test(text);
                        return /day|daily|24\s*hour|48\s*hour|hour/.test(text) && !/week|month|year|7\s*day|14\s*day|28\s*day|30\s*day|31\s*day|365\s*day/.test(text);
                    },
                    filteredPlans() {
                        const q = (this.planSearch || '').toLowerCase().trim();
                        return this.plans.filter((plan) => {
                            const text = this.planText(plan);
                            return this.planMatchesPeriod(plan) && (!q || text.includes(q));
                        });
                    },
                    planCode(plan) {
                        return plan.variation_code || plan.variationCode || plan.code || plan.id || '';
                    },
                    planName(plan) {
                        return plan.name || plan.variation_name || plan.plan || 'Data plan';
                    },
                    planPrice(plan) {
                        return plan.variation_amount || plan.amount || plan.price || plan.cost || '';
                    },
                    planValidity(plan) {
                        return plan.validity || plan.duration || '';
                    },
                    selectedPlanObject() {
                        return this.plans.find((plan) => String(this.planCode(plan)) === String(this.selectedPlan)) || null;
                    },
                    async loadPlans() {
                        if (this.type !== 'data') return;
                        const network = this.serviceId || '';
                        if (!network) return;
                        this.loading = true;
                        this.plans = [];
                        this.selectedPlan = '';
                        this.planOpen = false;
                        this.planSearch = '';
                        try {
                            const res = await fetch(`{{ url('/api/vtu/catalog/data') }}?network=${encodeURIComponent(network)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                            const json = await res.json();
                            const raw = json.data?.data || json.data?.variations || json.data?.content || json.data || [];
                            this.plans = Array.isArray(raw) ? raw : Object.values(raw || {});
                        } catch (_) {
                            this.plans = [];
                        } finally {
                            this.loading = false;
                        }
                    },
                    choosePlan(plan) {
                        this.selectedPlan = this.planCode(plan);
                        this.planOpen = false;
                        this.planSearch = '';
                        const price = this.planPrice(plan);
                        if (price) this.amount = price;
                    }
                }">
                <div class="p-5 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">New purchase</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Your wallet is debited first. If SprintPay fails, the amount is refunded automatically.</p>
                </div>

                <form method="POST" action="{{ route('vtu.purchase') }}" class="p-5 sm:p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="type" :value="type">

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(['airtime' => 'Airtime', 'data' => 'Data', 'cable' => 'Cable TV', 'electricity' => 'Electricity'] as $key => $label)
                            <button type="button" @click="setType('{{ $key }}')" class="min-h-[48px] rounded-2xl text-sm font-semibold transition" :class="type === '{{ $key }}' ? 'bg-mint-500 text-white shadow-lg shadow-mint-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'">{{ $label }}</button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Provider / service</label>
                            <input type="hidden" name="service_id" x-model="serviceId" required>
                            <div class="relative" @click.away="serviceOpen = false; serviceSearch = ''">
                                <button type="button" @click="serviceOpen = !serviceOpen" class="w-full min-h-[48px] rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 flex items-center justify-between gap-3 text-left focus:outline-none focus:ring-2 focus:ring-mint-500/30">
                                        <span class="flex items-center gap-3 min-w-0">
                                        <template x-if="selectedService()?.logo && String(selectedService().logo).startsWith('http')">
                                            <span class="w-10 h-10 rounded-xl bg-white border border-slate-100 shrink-0 flex items-center justify-center overflow-hidden p-1">
                                                <img :src="selectedService().logo" :alt="selectedService().name" class="max-w-full max-h-full object-contain">
                                            </span>
                                            </template>
                                        <template x-if="!selectedService()?.logo || !String(selectedService().logo).startsWith('http')">
                                            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[11px] font-black shrink-0" :class="selectedService()?.classes" x-text="selectedService()?.logo"></span>
                                        </template>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="selectedService()?.name || 'Select service'"></span>
                                            <span class="block text-[11px] text-slate-500 dark:text-slate-400" x-text="type === 'electricity' ? 'Disco' : (type === 'cable' ? 'TV' : 'Network')"></span>
                                        </span>
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 shrink-0 transition" :class="serviceOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="serviceOpen" x-cloak class="absolute z-30 mt-2 left-0 right-0 sm:min-w-[20rem] rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl overflow-hidden">
                                    <div class="p-2 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 sticky top-0 z-10">
                                        <input type="search" x-model="serviceSearch" @keydown.escape.stop="serviceOpen = false; serviceSearch = ''" placeholder="Search service..." class="w-full h-10 rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-mint-500 focus:ring-mint-500">
                                    </div>
                                    <div class="max-h-64 overflow-y-auto">
                                    <template x-for="service in filteredServices()" :key="service.id">
                                        <button type="button" @click="setService(service.id)" class="w-full px-3 py-2.5 flex items-center gap-3 text-left hover:bg-slate-50 dark:hover:bg-slate-700 transition" :class="serviceId === service.id ? 'bg-mint-50 dark:bg-mint-900/20' : ''">
                                            <template x-if="service.logo && String(service.logo).startsWith('http')">
                                                <span class="w-10 h-10 rounded-xl bg-white border border-slate-100 shrink-0 flex items-center justify-center overflow-hidden p-1">
                                                    <img :src="service.logo" :alt="service.name" class="max-w-full max-h-full object-contain">
                                                </span>
                                            </template>
                                            <template x-if="!service.logo || !String(service.logo).startsWith('http')">
                                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[11px] font-black shrink-0" :class="service.classes" x-text="service.logo"></span>
                                            </template>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 whitespace-normal leading-tight" x-text="service.name"></span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400" x-text="type === 'electricity' ? 'Disco' : (type === 'cable' ? 'TV' : 'Network')"></span>
                                            </span>
                                        </button>
                                    </template>
                                    <p x-show="filteredServices().length === 0" x-cloak class="px-3 py-4 text-sm text-slate-500 dark:text-slate-400">No service found.</p>
                                    </div>
                                </div>
                            </div>
                            @error('service_id')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Amount</label>
                            <input type="number" step="1" min="50" name="amount" x-model="amount" value="{{ old('amount') }}" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-mint-500 focus:ring-mint-500" required>
                            @error('amount')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div x-show="type === 'data'" x-cloak class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Data plan</p>
                            <span x-show="loading" class="text-xs text-slate-500">Loading plans...</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                            <button type="button" @click="planPeriod = 'days'; selectedPlan = ''" class="min-h-[40px] rounded-xl text-xs font-semibold transition" :class="planPeriod === 'days' ? 'bg-mint-500 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">Days</button>
                            <button type="button" @click="planPeriod = 'week'; selectedPlan = ''" class="min-h-[40px] rounded-xl text-xs font-semibold transition" :class="planPeriod === 'week' ? 'bg-mint-500 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">Week</button>
                            <button type="button" @click="planPeriod = 'month'; selectedPlan = ''" class="min-h-[40px] rounded-xl text-xs font-semibold transition" :class="planPeriod === 'month' ? 'bg-mint-500 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">Month</button>
                            <button type="button" @click="planPeriod = 'year'; selectedPlan = ''" class="min-h-[40px] rounded-xl text-xs font-semibold transition" :class="planPeriod === 'year' ? 'bg-mint-500 text-white' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'">Year</button>
                        </div>
                        <div class="relative" @click.away="planOpen = false; planSearch = ''">
                            <button type="button" :disabled="type !== 'data'" @click="planOpen = !planOpen" class="w-full min-h-[50px] rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 flex items-center justify-between gap-3 text-left focus:outline-none focus:ring-2 focus:ring-mint-500/30 disabled:opacity-60">
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 truncate" x-text="selectedPlanObject() ? planName(selectedPlanObject()) : 'Select a data plan'"></span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 truncate" x-text="selectedPlanObject() ? `${planValidity(selectedPlanObject()) || 'Data bundle'} ${planPrice(selectedPlanObject()) ? '· ₦' + planPrice(selectedPlanObject()) : ''}` : 'Choose from available bundles'"></span>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 transition" :class="planOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="planOpen" x-cloak class="absolute z-30 mt-2 left-0 right-0 sm:min-w-[28rem] rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl overflow-hidden">
                                <div class="p-2 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 sticky top-0 z-10">
                                    <input type="search" x-model="planSearch" @keydown.escape.stop="planOpen = false; planSearch = ''" placeholder="Search data plan..." class="w-full h-10 rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-mint-500 focus:ring-mint-500">
                                </div>
                                <div class="max-h-72 overflow-y-auto p-2 space-y-1">
                                    <template x-for="plan in filteredPlans()" :key="planCode(plan) || planName(plan)">
                                        <button type="button" @click="choosePlan(plan)" class="w-full rounded-xl px-3 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-700 transition border" :class="String(selectedPlan) === String(planCode(plan)) ? 'border-mint-300 bg-mint-50 dark:border-mint-700 dark:bg-mint-900/20' : 'border-transparent'">
                                            <span class="flex items-start justify-between gap-3">
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight" x-text="planName(plan)"></span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="planValidity(plan) || planCode(plan)"></span>
                                                </span>
                                                <span x-show="planPrice(plan)" class="shrink-0 inline-flex items-center rounded-lg bg-mint-100 dark:bg-mint-900/40 px-2.5 py-1 text-xs font-bold text-mint-700 dark:text-mint-300" x-text="'₦' + planPrice(plan)"></span>
                                            </span>
                                        </button>
                                    </template>
                                    <p x-show="filteredPlans().length === 0" x-cloak class="px-3 py-5 text-sm text-slate-500 dark:text-slate-400 text-center">No data plan found.</p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="variation_code" x-model="selectedPlan" :disabled="type !== 'data'">
                        <p x-show="!loading && plans.length > 0 && filteredPlans().length === 0" x-cloak class="mt-3 text-xs text-amber-600 dark:text-amber-400">
                            No plans found in this duration. Try another duration.
                        </p>
                    </div>

                    <div x-show="type === 'cable'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Package code</label>
                            <input type="text" name="variation_code" :disabled="type !== 'cable'" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-mint-500 focus:ring-mint-500" placeholder="e.g. dstv-yanga">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Smartcard / IUC</label>
                            <input type="text" name="billers_code" :disabled="type !== 'cable'" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-mint-500 focus:ring-mint-500">
                        </div>
                    </div>

                    <div x-show="type === 'electricity'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meter number</label>
                            <input type="text" name="billers_code" :disabled="type !== 'electricity'" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-mint-500 focus:ring-mint-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meter type</label>
                            <select name="meter_type" :disabled="type !== 'electricity'" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-mint-500 focus:ring-mint-500">
                                <option value="prepaid">Prepaid</option>
                                <option value="postpaid">Postpaid</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-mint-500 focus:ring-mint-500" placeholder="08012345678">
                        @error('phone')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" @disabled(!($enabled && $configured)) class="w-full min-h-[52px] rounded-2xl bg-gradient-to-r from-mint-500 to-teal-500 hover:from-mint-600 hover:to-teal-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-mint-500/20 transition">
                        Pay from wallet
                    </button>
                </form>
            </section>

            <aside class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm p-5 sm:p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Recent VTU</h2>
                <div class="space-y-3">
                    @forelse($transactions as $tx)
                        <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white uppercase">{{ $tx->type }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $tx->recipient }} · {{ $tx->reference }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-lg text-[11px] font-semibold {{ $tx->status === 'successful' ? 'bg-mint-100 text-mint-800 dark:bg-mint-900/40 dark:text-mint-200' : ($tx->status === 'refunded' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300') }}">{{ $tx->status }}</span>
                            </div>
                            <p class="mt-3 font-bold text-slate-900 dark:text-white">{{ \App\Models\SiteSetting::formatWalletAmount((float) $tx->wallet_debit) }}</p>
                            @if($tx->token)
                                <p class="mt-2 text-xs text-mint-700 dark:text-mint-300 break-words">Token: {{ $tx->token }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">No VTU purchases yet.</p>
                    @endforelse
                </div>
                @if($transactions->hasPages())
                    <div class="mt-4">{{ $transactions->links() }}</div>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
