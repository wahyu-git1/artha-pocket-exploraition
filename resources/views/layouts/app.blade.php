<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'CatatDuit' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{--navy:#1F4E79;--teal:#19B5A5;--ink:#102A43;--muted:#6B7C93;--line:#E5ECF3;--space:#071A35}
*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui;background:#F4F8FC;color:var(--ink)}
.cosmic{background:radial-gradient(circle at 85% 15%,#23538a 0,transparent 32%),radial-gradient(circle at 20% 0,#123b69 0,transparent 35%),linear-gradient(135deg,#071A35,#0b2b50 55%,#1F4E79);position:relative;overflow:hidden}.cosmic:before{content:'';position:absolute;inset:0;background-image:radial-gradient(#fff 1px,transparent 1px);background-size:42px 42px;opacity:.18;animation:drift 18s linear infinite}.cosmic>*{position:relative}@keyframes drift{to{background-position:42px 42px}}@keyframes float{50%{transform:translateY(-9px)}}.float{animation:float 5s ease-in-out infinite}.glass{background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.17);backdrop-filter:blur(12px)}.card{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 8px 26px rgba(20,54,88,.06)}.sidebar{width:250px}.nav-item{display:flex;align-items:center;gap:12px;border-radius:12px;padding:11px 14px;color:#B9C8D8;font-size:13px;font-weight:600}.nav-item:hover,.nav-active{background:rgba(25,181,165,.15);color:#fff}.kpi{min-height:128px}.badge{font-size:11px;border-radius:999px;padding:4px 8px;font-weight:700}.table-wrap{overflow-x:auto}@media(max-width:900px){.sidebar{width:78px}.nav-label,.brand-label,.side-footer{display:none}.nav-item{justify-content:center}.main-grid{grid-template-columns:1fr!important}.kpis{grid-template-columns:repeat(2,1fr)!important}} 
</style>
</head>
<body>
<div class="min-h-screen flex">
<aside class="sidebar bg-[#071A35] text-white p-5 flex flex-col shrink-0">
<div class="flex items-center gap-3 mb-9"><div class="w-10 h-10 rounded-xl bg-[#19B5A5] flex items-center justify-center text-xl">✦</div><div class="brand-label"><div class="font-extrabold">Catat<span class="text-[#19B5A5]">Duit</span></div><div class="text-[10px] text-slate-400">FINANCE ORBIT</div></div></div>
<nav class="space-y-1 flex-1">
@php($links=[['dashboard','⌂','Dashboard'],['transaksi','▤','Transaksi'],['laporan','◔','Laporan'],['target','◎','Target'],['dana-darurat','◈','Dana Darurat'],['alokasi','◫','Alokasi'],['investasi','◇','Investasi'],['kategori','⊙','Kategori'],['pengaturan','⚙','Pengaturan']])
@foreach($links as $link)<a href="{{ route($link[0]) }}" class="nav-item {{ request()->routeIs($link[0])?'nav-active':'' }}"><span class="text-lg">{{ $link[1] }}</span><span class="nav-label">{{ $link[2] }}</span></a>@endforeach
</nav><div class="side-footer glass rounded-xl p-3 text-xs text-slate-300">🚀 Ruang finansialmu<br><span class="text-[#19B5A5]">Tetap pada orbit.</span></div>
</aside>
<main class="flex-1 min-w-0"><header class="h-[76px] bg-white border-b border-slate-200 px-8 flex items-center gap-5 sticky top-0 z-30"><div class="flex items-center gap-2 text-sm font-bold">Oktober 2026 <span class="text-slate-400">⌄</span></div><div class="flex-1 max-w-2xl relative"><span class="absolute left-4 top-3 text-slate-400">✦</span><input class="w-full rounded-xl bg-[#F4F8FC] border border-slate-200 py-3 pl-11 pr-4 text-sm outline-none focus:ring-2 focus:ring-[#19B5A5]" placeholder="Ketik, mis. beli bakso 10k"></div><div class="text-right hidden sm:block"><div class="text-sm font-bold">Nakano Tech</div><div class="text-[11px] text-slate-400">Navigator Finansial</div></div><div class="w-10 h-10 rounded-full bg-[#1F4E79] text-white flex items-center justify-center font-bold">NT</div></header><div class="p-8">@yield('content')</div></main></div>
<script>document.querySelectorAll('[data-demo]').forEach(x=>x.addEventListener('click',()=>alert('Fitur demo aktif setelah backend terhubung.')))</script>@stack('scripts')</body></html>