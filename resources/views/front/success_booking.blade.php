@extends('front.layouts.app')
@section('title', 'Sewa')
@section('content')
		<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative has-[#Bottom-nav]:pb-[144px]">
			<section id="finishBook" class="flex flex-col gap-[30px] max-w-[353px] p-[30px_20px] items-center m-auto">
				<div class="flex flex-col gap-2 items-center">
					<h1 class="text-2xl leading-[36px] font-bold">Finish Booking</h1>
					<p class="leading-[30px] text-[#6E6E70] text-center">Terima kasih telah melakukan pembayaran.</p>
				</div>
				<div class="w-[220px] flex flex-col gap-3 items-center">
					<a href="{{route('front.index')}}" class="w-full text-center rounded-full p-[12px_24px] bg-[#FCCF2F] font-bold text-black">Kembali ke Beranda</a>
				</div>
			</section>
		</main>
@endsection
