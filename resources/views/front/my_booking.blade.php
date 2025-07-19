@extends('front.layouts.app')
@section('title', 'Sewa')
@section('content')
    <main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative has-[#Bottom-nav]:pb-[144px]">
        <div id="Top-navbar" class="flex items-center justify-between px-5 pt-5">
            <a href="{{route('front.index')}}">
                <div class="size-[44px] flex shrink-0">
                    <img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
                </div>
            </a>
            <p class="text-lg leading-[27px] font-semibold">My Booking</p>
            <button class="size-[44px] flex shrink-0">
                <img src="{{asset('assets/images/icons/more.svg')}}" alt="icon" />
            </button>
        </div>
        <section class="flex flex-col gap-[30px] mt-[30px] px-5">
            @forelse ($transactions as $trx)
                <a href="{{ route('front.transaction.details', $trx->id) }}" class="no-underline">
                    @if($trx->is_paid)
                        <div class="flex items-center rounded-2xl border border-[#E9E8ED] gap-2 p-4 bg-black text-white">
                            <div class="w-6 h-6 flex shrink-0">
                                <img src="{{ asset('assets/images/icons/note-white.svg') }}" alt="icon">
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1">
                                    <p class="font-semibold text-white">{{ $trx->trx_id}}</p>
                                    <div class="w-5 h-5">
                                        <img src="{{ asset('assets/images/icons/verify.svg') }}" alt="icon">
                                    </div>
                                </div>
                                <p class="font-semibold text-sm leading-[21px]">Payment Success</p>
                                <p class="text-sm leading-[21px] text-white">Protect your booking ID</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center rounded-2xl border border-[#E9E8ED] gap-2 p-4 bg-[#FCCF2F]">
                            <div class="w-6 h-6 flex shrink-0">
                                <img src="{{ asset('assets/images/icons/note-black.svg') }}" alt="icon">
                            </div>
                            <div class="flex flex-col">
                                <p class="font-semibold text-black">{{ $trx->trx_id }}</p>
                                <p class="text-sm leading-[21px] text-[#6E6E70]">Payment Pending</p>
                                <p class="text-sm leading-[21px] text-[#6E6E70]">Protect your booking ID</p>
                            </div>
                        </div>
                    @endif
                </a>
            @empty
                <p class="text-center text-gray-500 mt-5">Belum ada transaksi pemesanan</p>
            @endforelse
        </section>

    </main>
@endsection