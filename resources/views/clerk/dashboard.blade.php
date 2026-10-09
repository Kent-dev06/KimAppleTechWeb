@extends('layouts.app', ['active' => 'clerk'])
@section('title', 'Clerk Dashboard')

@section('content')
    {{-- Alerts for Success/Error --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-2xl font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-2xl font-bold">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Stats Cards (Dynamic na ni dapat gikan sa Controller) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5">
        <div class="bg-amber-50 border border-amber-200 px-4 py-3 rounded-2xl flex items-center gap-3">
            <i class="fas fa-clock text-amber-500 text-lg"></i>
            <div><p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-0">Pending</p>
            <h4 class="text-2xl font-black text-amber-600 mb-0">{{ $pendingCount }}</h4></div>
        </div>
        <div class="bg-blue-50 border border-blue-200 px-4 py-3 rounded-2xl flex items-center gap-3">
            <i class="fas fa-box text-blue-500 text-lg"></i>
            <div><p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-0">Ready for pickup</p>
            <h4 class="text-2xl font-black text-blue-600 mb-0">{{ $readyCount }}</h4></div>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 px-4 py-3 rounded-2xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <div><p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-0">Claimed today</p>
            <h4 class="text-2xl font-black text-emerald-600 mb-0">{{ $claimedTodayCount }}</h4></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5">
        {{-- Table: Manage Orders --}}
        <div id="orders" class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 sm:px-5 sm:py-3 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                <div><h3 class="font-bold text-slate-800 text-lg mb-0">Manage Orders</h3><p class="text-xs text-slate-500 mb-0">{{ $pendingCount + $readyCount }} orders need attention</p></div>
                <form action="{{ route('clerk.orders.search') }}" method="GET" class="flex gap-2">
                    <input type="text" name="query" placeholder="Search Order ID..." 
                        class="text-sm border border-slate-200 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64">
                </form>
            </div>
            
            <div class="max-h-[calc(100vh-22rem)] min-h-[14rem] overflow-auto soft-scrollbar">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400 md:hidden">
                    Swipe sideways to view all columns
                </div>
                <table class="w-full min-w-[900px] text-left">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-[10px] uppercase font-bold text-slate-400 shadow-sm">
                        <tr>
                            <th class="px-6 py-4">Order ID</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Details</th>
                            <th class="px-4 py-3">Pickup Slot</th>
                            <th class="px-4 py-3">Placed At</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse ($orders as $order)
                        <tr class="hover:bg-blue-50/60">
                            <td class="px-4 py-3 font-bold text-slate-700">{{ $order->order_id }}</td>
                            <td class="px-4 py-3 text-blue-600 font-medium">{{ $order->customer_name }}</td>
                            <td class="px-4 py-3">{{ $order->fuel_type }} ({{ $order->liters }}L)</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-600">{{ $order->pickup_time }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-700">{{ $order->created_at->timezone('Asia/Manila')->format('M d, Y') }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase">{{ $order->created_at->timezone('Asia/Manila')->format('h:i A') }} · <span data-order-time="{{ $order->created_at->timezone('Asia/Manila')->toIso8601String() }}" data-no-translate>{{ $order->created_at->timezone('Asia/Manila')->diffForHumans() }}</span></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase
                                    {{ $order->status == 'Ready' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($order->status == 'Pending')
                                    <form action="{{ route('clerk.orders.update', $order->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="Ready">
                                        <button class="bg-[#2a4d7d] text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-900 transition">Mark Ready</button>
                                    </form>
                                @elseif($order->status == 'Ready')
                                    <form action="{{ route('clerk.orders.update', $order->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="Claimed">
                                        <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-700 transition">Mark Claimed</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">No pending or ready orders right now.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Form: New Walk-In Order --}}
        <div id="new-order" class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 h-fit">
            <h3 class="font-bold text-slate-800 mb-3">New Walk-In Order</h3>
            <form action="{{ route('clerk.orders.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="text" name="customer_name" placeholder="Customer Name" required
                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">
                
                <select name="fuel_type" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="Premium Gasoline">Premium Gasoline</option>
                    <option value="Regular Gasoline">Regular Gasoline</option>
                    <option value="Krude">Krude</option>
                </select>

                <input type="number" name="liters" placeholder="Liters" step="0.01" required
                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">

                <select name="pickup_time" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="" disabled selected>Select Pickup Time</option>
                    <option value="Any Time">Any Time</option>
                    <option value="08:00 AM - 09:00 AM">08:00 AM - 09:00 AM</option>
                    <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                    <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                    <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                    <option value="12:00 PM - 01:00 PM">12:00 PM - 01:00 PM</option>
                    <option value="01:00 PM - 02:00 PM">01:00 PM - 02:00 PM</option>
                    <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                    <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                    <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                    <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                    <option value="06:00 PM - 07:00 PM">06:00 PM - 07:00 PM</option>
                </select>

                <button type="submit" class="w-full bg-amber-500 text-white font-bold py-3 rounded-xl hover:bg-amber-600 transition shadow-lg shadow-amber-100">
                    Create Order
                </button>
            </form>
        </div>
    </div>
@endsection
