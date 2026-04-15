<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KIRA - Decision Support System | Theo Filus Final Project 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ==========================================================================
           CUSTOM THEME & ANIMATIONS
           ========================================================================== 
        */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        :root {
            --kira-blue: #3b82f6;
            --kira-dark: #1d4ed8;
            --bg-dark: #020617;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --accent-glow: rgba(59, 130, 246, 0.15);
        }

        body, html {
            margin: 0; padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            scroll-behavior: smooth;
            background-color: var(--bg-dark);
            color: white;
            overflow-x: hidden;
        }

        /* Snap Scrolling Configuration */
        .presentation-container {
            height: 100vh;
            overflow-y: auto;
            scroll-snap-type: y mandatory;
            scrollbar-width: thin;
            scrollbar-color: var(--kira-blue) var(--bg-dark);
        }

        section {
            min-height: 100vh;
            scroll-snap-align: start;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5rem 2rem;
            overflow: hidden;
        }

        /* Decoration: Grid & Glows */
        .grid-bg {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(to right, rgba(255,255,255,0.015) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: -2;
        }

        .glow-orb {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            pointer-events: none;
        }

        /* Reveal Animations */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .active .reveal {
            opacity: 1;
            transform: translateY(0);
        }

        .delay-1 { transition-delay: 0.2s; }
        .delay-2 { transition-delay: 0.4s; }
        .delay-3 { transition-delay: 0.6s; }
        .delay-4 { transition-delay: 0.8s; }

        /* Component Styles */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 2.5rem;
            transition: all 0.5s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .glass-card:hover {
            border-color: rgba(59, 130, 246, 0.4);
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-10px) scale(1.01);
            box-shadow: 0 20px 60px rgba(59, 130, 246, 0.1);
        }

        .dot-nav {
            position: fixed;
            right: 2.5rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .dot {
            width: 10px;
            height: 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border: 1px solid transparent;
            border-radius: 50%;
            transition: 0.3s;
        }

        .dot.active {
            background: var(--kira-blue);
            transform: scale(1.5);
            box-shadow: 0 0 15px var(--kira-blue);
        }

        .dot.active::after {
            border-color: var(--kira-blue);
        }

        .label-pill {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--kira-blue);
            padding: 0.6rem 1.5rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            display: inline-block;
        }

        /* Decision Tree Logic Graph */
        .tree-node {
            padding: 1rem;
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.05);
            border-radius: 1rem;
            font-size: 0.75rem;
            text-align: center;
            width: 140px;
            position: relative;
        }
        .tree-line {
            height: 2px;
            background: var(--glass-border);
            flex-grow: 1;
            position: relative;
        }
        .tree-line::after {
            content: '';
            position: absolute;
            right: 0; top: -3px;
            border-top: 4px solid transparent;
            border-bottom: 4px solid transparent;
            border-left: 6px solid var(--glass-border);
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        .float { animation: float 5s ease-in-out infinite; }

        /* Logo Pulse */
        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        .pulse-btn { animation: pulse-blue 2s infinite; }

        /* Responsive Settings */
        @media (max-width: 768px) {
            .dot-nav { display: none; }
            section { min-height: auto; scroll-snap-align: none; }
            .presentation-container { height: auto; overflow-y: visible; scroll-snap-type: none; }
        }
    </style>
</head>
<body>

    <!-- SIDE NAVIGATION -->
    <div class="dot-nav">
        <div class="dot active" title="Hero" onclick="scrollToSlide(0)"></div>
        <div class="dot" title="Tragedi Lapangan" onclick="scrollToSlide(1)"></div>
        <div class="dot" title="Core Methodology" onclick="scrollToSlide(2)"></div>
        <div class="dot" title="Decision Tree" onclick="scrollToSlide(3)"></div>
        <div class="dot" title="Logic Simulator" onclick="scrollToSlide(4)"></div>
        <div class="dot" title="Architecture" onclick="scrollToSlide(5)"></div>
        <div class="dot" title="Business Impact" onclick="scrollToSlide(6)"></div>
        <div class="dot" title="Final Conclusion" onclick="scrollToSlide(7)"></div>
    </div>

    <div class="presentation-container" id="container">

        <!-- SLIDE 1: HERO SECTION -->
        <section id="slide-0" class="active">
            <div class="grid-bg"></div>
            <div class="glow-orb" style="top: -20%; left: -10%;"></div>
            <div class="text-center reveal">
                <div class="mb-10 inline-block">
                    <!-- LOGO KIRA APPLY -->
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                        <img src="{{ asset('images/logo-kira.png') }}" 
                            alt="Kira Logo" 
                            class="block mx-auto relative w-32 h-32 md:w-48 md:h-48 object-contain float rounded-3xl" 
                            onerror="this.src='https://via.placeholder.com/200x200?text=Kira+Logo'">
                    </div>
                </div>
                
                <h1 class="text-6xl md:text-9xl font-black mb-8 leading-[0.85] tracking-tighter">
                    THE FUTURE <br> OF <span class="text-blue-500 italic">COSTING.</span>
                </h1>
                
                <p class="text-slate-400 text-xl max-w-3xl mx-auto mb-14 leading-relaxed font-light">
                    Solusi Sistem Pendukung Keputusan (DSS) cerdas untuk menstandarisasi estimasi biaya vendor multimedia dengan metode <span class="text-white font-bold">Rule-Based Decision Tree.</span>
                </p>

                <div class="flex flex-col md:flex-row justify-center items-center gap-8">
                    <button onclick="scrollToSlide(1)" class="pulse-btn bg-blue-600 text-white px-12 py-5 rounded-full font-black text-sm tracking-widest hover:bg-blue-700 hover:scale-105 transition-all shadow-xl shadow-blue-600/30">
                        KENAL KIRA LEBIH JAUH <i class="fa-solid fa-chevron-right ml-3"></i>
                    </button>
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
                            <i class="fa-solid fa-user-graduate text-blue-400"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] uppercase text-slate-500 font-bold leading-none mb-1">Dikerjakan Oleh</p>
                            <p class="text-xs font-bold text-white">Theo Filus Handy S. (0706022210051)</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 2: THE REAL-WORLD TRAGEDY -->
        <section id="slide-1">
            <div class="glow-orb" style="bottom: -10%; right: -10%; opacity: 0.4;"></div>
            <div class="container mx-auto">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="reveal">
                        <span class="label-pill mb-6">Tragedi Lapangan</span>
                        <h2 class="text-5xl font-extrabold mb-10 leading-tight">Mengapa Vendor Sering <br> <span class="text-blue-500 italic">"Gagal Hitung"?</span></h2>
                        <div class="space-y-8">
                            <!-- Kasus 1: Bos di Jepang -->
                            <div class="group p-6 glass-card border-l-4 border-l-blue-500 hover:bg-blue-500/5 cursor-default">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center text-blue-400 mr-4">
                                        <i class="fa-solid fa-plane-up"></i>
                                    </div>
                                    <h4 class="font-bold text-lg">Owner Dependency</h4>
                                </div>
                                <p class="text-slate-400 text-sm leading-relaxed">
                                    "Boss lagi di Jepang, admin bingung mau kasih harga diskon berapa. Klien nunggu kelamaan, akhirnya lari ke vendor sebelah."
                                </p>
                            </div>
                            <!-- Kasus 2: Teknisi di Rigging -->
                            <div class="group p-6 glass-card border-l-4 border-l-cyan-500 hover:bg-cyan-500/5 cursor-default">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-cyan-500/20 rounded-xl flex items-center justify-center text-cyan-400 mr-4">
                                        <i class="fa-solid fa-helmet-safety"></i>
                                    </div>
                                    <h4 class="font-bold text-lg">Operational Blindness</h4>
                                </div>
                                <p class="text-slate-400 text-sm leading-relaxed">
                                    "Teknisi lagi manjat rigging lighting di lapangan, HP mati. Admin gak tau stok videotron yang ready tipe apa. Salah spek, rugi biaya."
                                </p>
                            </div>
                            <!-- Kasus 3: Human Error -->
                            <div class="group p-6 glass-card border-l-4 border-l-red-500 hover:bg-red-500/5 cursor-default">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-red-500/20 rounded-xl flex items-center justify-center text-red-400 mr-4">
                                        <i class="fa-solid fa-calculator"></i>
                                    </div>
                                    <h4 class="font-bold text-lg">Inconsistent Pricing</h4>
                                </div>
                                <p class="text-slate-400 text-sm leading-relaxed">
                                    "Harga pagi beda dengan harga malam karena admin ngantuk/salah input kalkulator. Reputasi vendor hancur di mata klien korporat."
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="reveal delay-2 relative">
                        <div class="absolute -inset-10 bg-blue-600/10 blur-[100px] rounded-full"></div>
                        <div class="glass-card p-10 relative overflow-hidden">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 tracking-widest">LIVE CHAT - LOST OPPORTUNITY</span>
                            </div>
                            <div class="space-y-6">
                                <div class="flex flex-col items-start space-y-2">
                                    <div class="bg-slate-800 p-4 rounded-2xl rounded-bl-none text-xs max-w-[80%]">
                                        "Kak, mau sewa videotron P2.6 buat acara Ballroom besok pagi jam 7. Berapa ya nett-nya?"
                                    </div>
                                    <span class="text-[9px] text-slate-500">09:15 AM - Client</span>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <div class="bg-blue-600 p-4 rounded-2xl rounded-br-none text-xs max-w-[80%] text-right">
                                        "Sebentar kak, saya koordinasi dulu dengan teknisi gudang & boss saya..."
                                    </div>
                                    <span class="text-[9px] text-slate-500">09:17 AM - Admin</span>
                                </div>
                                <div class="flex flex-col items-start space-y-2 opacity-50">
                                    <div class="bg-slate-800 p-4 rounded-2xl rounded-bl-none text-xs max-w-[80%]">
                                        "Udah jam 11 kak? Gimana? Boss belum bangun?"
                                    </div>
                                    <span class="text-[9px] text-slate-500">11:00 AM - Client</span>
                                </div>
                                <div class="flex flex-col items-start space-y-2">
                                    <div class="bg-red-500/20 border border-red-500/30 p-4 rounded-2xl text-xs w-full text-center text-red-400 font-bold">
                                        CANCEL: Klien pindah ke Vendor B yang fast-response.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 3: CORE METHODOLOGY -->
        <section id="slide-2">
            <div class="container mx-auto">
                <div class="text-center mb-20 reveal">
                    <span class="label-pill mb-6">Metode Penelitian</span>
                    <h2 class="text-6xl font-black italic">How Kira Thinks?</h2>
                    <p class="text-slate-400 mt-6 max-w-2xl mx-auto">
                        Mengonversi pengalaman pakar bertahun-tahun ke dalam arsitektur digital yang konsisten.
                    </p>
                </div>
                <div class="grid md:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div class="glass-card p-8 reveal delay-1 text-center relative">
                        <div class="text-blue-500 text-4xl mb-6"><i class="fa-solid fa-database"></i></div>
                        <h4 class="font-bold mb-4">Knowledge <br> Acquisition</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Wawancara dengan owner & teknisi senior untuk menentukan variabel harga.</p>
                        <div class="absolute -right-4 top-1/2 -translate-y-1/2 hidden md:block opacity-20"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <!-- Step 2 -->
                    <div class="glass-card p-8 reveal delay-2 text-center relative">
                        <div class="text-blue-500 text-4xl mb-6"><i class="fa-solid fa-diagram-nested"></i></div>
                        <h4 class="font-bold mb-4">Rule <br> Definition</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Membangun Decision Tree berdasarkan jenis event, skala audiens, dan lokasi.</p>
                        <div class="absolute -right-4 top-1/2 -translate-y-1/2 hidden md:block opacity-20"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <!-- Step 3 -->
                    <div class="glass-card p-8 reveal delay-3 text-center relative">
                        <div class="text-blue-500 text-4xl mb-6"><i class="fa-solid fa-code-branch"></i></div>
                        <h4 class="font-bold mb-4">Inference <br> Engine</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Mekanisme pencocokan fakta (Forward Chaining) untuk mendapatkan hasil akhir.</p>
                        <div class="absolute -right-4 top-1/2 -translate-y-1/2 hidden md:block opacity-20"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <!-- Step 4 -->
                    <div class="glass-card p-8 reveal delay-4 text-center">
                        <div class="text-blue-500 text-4xl mb-6"><i class="fa-solid fa-file-pdf"></i></div>
                        <h4 class="font-bold mb-4">Dynamic <br> Output</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Otomasi pembuatan Quotation (Penawaran) siap kirim dalam format digital.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 4: THE DECISION TREE VISUALIZER (NEW) -->
        <section id="slide-3" class="bg-gradient-to-b from-transparent to-blue-900/10">
            <div class="container mx-auto">
                <div class="grid md:grid-cols-2 gap-20 items-center">
                    <div class="reveal">
                        <span class="label-pill mb-6">Visualisasi Logika</span>
                        <h2 class="text-5xl font-black mb-8 leading-tight">Navigasi Rute <br> <span class="text-blue-500">Harga Terbaik.</span></h2>
                        <p class="text-slate-400 mb-10 leading-relaxed">
                            Kira tidak menebak. Sistem melakukan eliminasi opsi paket yang tidak efisien dan merekomendasikan solusi yang paling menguntungkan (Profit-Effective).
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4 p-4 bg-white/5 rounded-2xl">
                                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center text-green-500"><i class="fa-solid fa-check"></i></div>
                                <span class="text-sm font-semibold">Memastikan alat tersedia di Gudang.</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 bg-white/5 rounded-2xl">
                                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center text-green-500"><i class="fa-solid fa-check"></i></div>
                                <span class="text-sm font-semibold">Menghitung margin profit minimal 30%.</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 bg-white/5 rounded-2xl">
                                <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center text-green-500"><i class="fa-solid fa-check"></i></div>
                                <span class="text-sm font-semibold">Menganalisis tingkat kerumitan Crew.</span>
                            </div>
                        </div>
                    </div>
                    <div class="reveal delay-2">
                        <!-- Interactive SVG Tree -->
                        <div class="glass-card p-10 bg-slate-900/80">
                            <div class="flex flex-col items-center space-y-8">
                                <div class="tree-node bg-blue-600 border-none font-bold">START: EVENT</div>
                                <div class="flex items-center w-full">
                                    <div class="tree-line"></div>
                                    <div class="tree-node">Wedding?</div>
                                    <div class="tree-line"></div>
                                    <div class="tree-node">Seminar?</div>
                                </div>
                                <div class="flex justify-between w-full">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="tree-node bg-white/10 text-[9px]">AUDIENS > 500</div>
                                        <div class="tree-node border-green-500/50 text-green-400 font-bold">DIAMOND PKG</div>
                                    </div>
                                    <div class="flex flex-col items-center space-y-4">
                                        <div class="tree-node bg-white/10 text-[9px]">OUTDOOR?</div>
                                        <div class="tree-node border-blue-500/50 text-blue-400 font-bold">PRO SEMINAR</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-12 pt-6 border-t border-white/10 text-center">
                                <p class="text-[10px] text-slate-500 italic">"Memilih rule yang paling efektif sesuai batasan budget klien."</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 5: INTERACTIVE CALCULATOR (Dosen Trial) -->
        <section id="slide-4" class="bg-blue-600">
            <div class="container mx-auto">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="reveal">
                        <h2 class="text-6xl font-black mb-8 leading-tight">Uji Engine <br> <span class="underline decoration-white/40">Real-Time.</span></h2>
                        <p class="text-blue-100 text-lg mb-10 leading-relaxed font-medium">
                            Silahkan Dewan Penguji mencoba logika SPK ini. Masukkan variabel lapangan dan lihat bagaimana Kira merumuskan paket penawaran.
                        </p>
                        <div class="bg-black/30 p-8 rounded-[2rem] border border-white/10 shadow-inner">
                            <p class="text-[10px] font-black mb-4 flex items-center tracking-widest text-blue-200">
                                <i class="fa-solid fa-terminal mr-3"></i> ENGINE SYSTEM MONITOR v2.0
                            </p>
                            <div class="space-y-2 font-mono text-[11px] h-40 overflow-y-auto" id="log-monitor">
                                <div class="text-green-400">> System Ready... Authenticated.</div>
                                <div class="text-white/60">> Ready to process new costing request.</div>
                            </div>
                        </div>
                    </div>
                    <div class="reveal delay-2">
                        <div class="bg-white text-slate-900 p-10 rounded-[3rem] shadow-[0_30px_100px_rgba(0,0,0,0.5)]">
                            <h4 class="font-black text-3xl mb-8 text-center text-blue-600 tracking-tight">Kira Costing Calc</h4>
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Event Category</label>
                                        <select id="calc-event" class="w-full p-4 bg-slate-100 rounded-2xl border-none outline-none font-bold focus:ring-2 focus:ring-blue-500 transition">
                                            <option value="wedding">Luxury Wedding</option>
                                            <option value="corporate">Corporate Gathering</option>
                                            <option value="concert">Music Festival / Concert</option>
                                            <option value="seminar">Hybrid Seminar</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Audience (Pax)</label>
                                        <input type="number" id="calc-aud" placeholder="Ex: 500" class="w-full p-4 bg-slate-100 rounded-2xl border-none outline-none font-bold">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Duration (Days)</label>
                                        <input type="number" id="calc-days" value="1" class="w-full p-4 bg-slate-100 rounded-2xl border-none outline-none font-bold">
                                    </div>
                                </div>
                                <button onclick="calculateKira()" class="w-full bg-blue-600 text-white p-5 rounded-2xl font-black tracking-widest hover:bg-slate-900 transition-all shadow-xl shadow-blue-600/20 active:scale-95">
                                    PROCESS CALCULATION <i class="fa-solid fa-bolt ml-2"></i>
                                </button>
                                
                                <!-- Result Slot -->
                                <div id="calc-result" class="hidden animate-bounce mt-6 p-6 bg-blue-50 border-2 border-dashed border-blue-200 rounded-3xl text-center">
                                    <p class="text-[10px] font-black text-blue-600 uppercase mb-1">Recommended Solution</p>
                                    <h5 id="calc-res-name" class="font-black text-2xl text-slate-900 italic">...</h5>
                                    <div class="h-[1px] bg-blue-100 my-4"></div>
                                    <p id="calc-res-price" class="text-blue-700 font-black text-xl">Rp 0</p>
                                    <p class="text-[9px] text-slate-400 mt-2 font-bold uppercase tracking-widest">Estimated Margin: 35%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 6: TECHNICAL ARCHITECTURE (LARAVEL DEEP DIVE) -->
        <section id="slide-5">
            <div class="container mx-auto">
                <div class="text-center mb-16 reveal">
                    <span class="label-pill mb-6">Backend Structure</span>
                    <h2 class="text-5xl font-black">Laravel Engine Workflow</h2>
                </div>
                <div class="grid md:grid-cols-2 gap-12 items-stretch">
                    <div class="reveal delay-1">
                        <div class="glass-card p-12 h-full relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-8 text-white/5 text-9xl group-hover:rotate-12 transition-transform">
                                <i class="fa-brands fa-laravel"></i>
                            </div>
                            <h4 class="text-2xl font-bold mb-10 flex items-center border-b border-white/10 pb-6">
                                <span class="w-12 h-12 bg-red-600 rounded-2xl flex items-center justify-center mr-6 shadow-lg shadow-red-600/20">
                                    <i class="fa-solid fa-code"></i>
                                </span>
                                System Core
                            </h4>
                            <div class="space-y-8">
                                <div class="flex items-start">
                                    <div class="p-3 bg-white/5 rounded-xl mr-6"><i class="fa-solid fa-shield-halved text-blue-400"></i></div>
                                    <div>
                                        <h5 class="font-bold text-sm mb-1">Authorization Middleware</h5>
                                        <p class="text-[11px] text-slate-500">Membatasi akses Admin Gudang vs Admin Sales dalam mengubah master data biaya.</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="p-3 bg-white/5 rounded-xl mr-6"><i class="fa-solid fa-server text-blue-400"></i></div>
                                    <div>
                                        <h5 class="font-bold text-sm mb-1">Service Layer Pattern</h5>
                                        <p class="text-[11px] text-slate-500">Memisahkan logic hitungan SPK dari Controller utama untuk kemudahan testing unit.</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="p-3 bg-white/5 rounded-xl mr-6"><i class="fa-solid fa-database text-blue-400"></i></div>
                                    <div>
                                        <h5 class="font-bold text-sm mb-1">Knowledge-Base Seeders</h5>
                                        <p class="text-[11px] text-slate-500">Master data yang dapat diperbarui secara dinamis mengikuti inflasi harga pasar multimedia.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="reveal delay-3">
                        <div class="glass-card p-12 h-full bg-blue-600/5">
                            <h4 class="text-2xl font-bold mb-10 flex items-center border-b border-white/10 pb-6">
                                <span class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center mr-6 shadow-lg shadow-blue-600/20">
                                    <i class="fa-solid fa-gears"></i>
                                </span>
                                Rule Processing
                            </h4>
                            <div class="space-y-6">
                                <div class="bg-black/40 p-6 rounded-2xl border border-white/10 font-mono text-[10px] space-y-2">
                                    <div class="text-pink-400">public function calculateCost(Request $req) {</div>
                                    <div class="pl-4 text-blue-300">$rules = Rule::where('event_type', $req->type)->get();</div>
                                    <div class="pl-4 text-slate-500">// Iterasi Decision Tree Logic</div>
                                    <div class="pl-4 text-white">foreach ($rules as $rule) {</div>
                                    <div class="pl-8 text-yellow-300">if ($req->aud >= $rule->min_aud && $req->aud <= $rule->max_aud) {</div>
                                    <div class="pl-12 text-green-400">return $this->recommendPackage($rule->package_id);</div>
                                    <div class="pl-8 text-yellow-300">}</div>
                                    <div class="pl-4 text-white">}</div>
                                    <div class="text-pink-400">}</div>
                                </div>
                                <p class="text-xs text-slate-400 leading-relaxed italic">
                                    *Implementasi kode di atas memastikan admin tidak perlu lagi menghitung manual dengan Excel yang rawan salah rumus.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 7: BUSINESS IMPACT -->
        <section id="slide-6">
            <div class="container mx-auto">
                <div class="grid md:grid-cols-2 gap-20 items-center">
                    <div class="reveal">
                        <span class="label-pill mb-6">Analisis Hasil</span>
                        <h2 class="text-5xl font-black mb-10 leading-tight italic">The Quantifiable <br> <span class="text-blue-500">Difference.</span></h2>
                        <div class="grid grid-cols-2 gap-10">
                            <div class="glass-card p-8 border-t-4 border-t-green-500">
                                <div class="text-5xl font-black text-green-500 mb-2">30s</div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Response Time</p>
                                <p class="text-[9px] mt-3 text-slate-400 italic">Turun dari rata-rata 2 jam menjadi hitungan detik.</p>
                            </div>
                            <div class="glass-card p-8 border-t-4 border-t-blue-500">
                                <div class="text-5xl font-black text-blue-500 mb-2">100%</div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Consistency</p>
                                <p class="text-[9px] mt-3 text-slate-400 italic">Nihil variasi harga pada permintaan yang identik.</p>
                            </div>
                        </div>
                    </div>
                    <div class="reveal delay-2">
                        <div class="glass-card p-10 bg-gradient-to-br from-blue-600/20 to-transparent">
                            <h4 class="font-bold text-xl mb-6 flex items-center"><i class="fa-solid fa-quote-left mr-4 text-blue-500"></i> User Testimonial</h4>
                            <p class="text-slate-300 italic text-lg leading-relaxed mb-8 font-medium">
                                "Sistem Kira ini bukan cuma aplikasi, tapi jembatan antara teknis dan sales. Sekarang admin magang saya pun bisa kirim Quotation ke klien corporate dalam sekejap tanpa harus telepon saya terus."
                            </p>
                            <div class="flex items-center space-x-6">
                                <div class="w-14 h-14 rounded-full bg-slate-800 border-2 border-blue-500 flex items-center justify-center text-xl font-black">V</div>
                                <div>
                                    <h5 class="font-bold text-white">Vincent Wijaya</h5>
                                    <p class="text-[10px] text-slate-500 uppercase font-black">Owner of Multi-Pro Vendor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SLIDE 8: THE CLOSING -->
        <section id="slide-7" class="bg-gradient-to-t from-blue-600/10 to-transparent">
            <div class="text-center reveal">
                <div class="mb-10">
                    <img src="{{ asset('images/logo-kira.png') }}" 
                    alt="Kira Logo" 
                    class="block mx-auto relative w-32 h-32 md:w-48 md:h-48 object-contain float rounded-3xl" 
                    onerror="this.src='https://via.placeholder.com/200x200?text=Kira+Logo'">
                </div>
                <h2 class="text-7xl md:text-9xl font-black mb-10 italic tracking-tighter">
                    READY FOR <br> <span class="text-blue-500 underline decoration-blue-600/30 underline-offset-[15px]">THE FINAL.</span>
                </h2>
                <p class="text-slate-400 text-xl max-w-2xl mx-auto mb-16 leading-relaxed">
                    Sistem Kira telah divalidasi dan siap dipertanggungjawabkan di hadapan Dewan Penguji Universitas Ciputra.
                </p>

                <div class="flex flex-col md:flex-row justify-center items-center gap-10">
                    <div class="text-left">
                        <p class="text-[10px] font-black text-slate-500 uppercase mb-2">Student Name</p>
                        <p class="text-xl font-bold tracking-tight">Theo Filus Handy Syahputra</p>
                    </div>
                    <div class="w-[1px] h-14 bg-white/10 hidden md:block"></div>
                    <div class="text-left">
                        <p class="text-[10px] font-black text-slate-500 uppercase mb-2">Supervisor</p>
                        <p class="text-xl font-bold tracking-tight">Kartika Gianina Tileng, S.E., M.Cs.</p>
                    </div>
                </div>

                <div class="mt-32 pt-16 border-t border-white/5 flex flex-col items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/id/thumb/d/d3/Logo_Universitas_Ciputra.png/200px-Logo_Universitas_Ciputra.png" alt="UC Logo" class="h-10 opacity-30 hover:opacity-100 transition duration-1000">
                    <p class="text-[9px] font-black text-slate-600 uppercase mt-4 tracking-[0.5em]">Information Systems • Universitas Ciputra Surabaya</p>
                </div>
            </div>
        </section>

    </div>

    <!-- 
       ==========================================================================
       JAVASCRIPT LOGIC & INTERACTIONS
       ========================================================================== 
    -->
    <script>
        const container = document.getElementById('container');
        const sections = document.querySelectorAll('section');
        const dots = document.querySelectorAll('.dot');
        const monitor = document.getElementById('log-monitor');

        // Logic for Calculation Simulator
        function calculateKira() {
            const event = document.getElementById('calc-event').value;
            const aud = parseInt(document.getElementById('calc-aud').value);
            const days = parseInt(document.getElementById('calc-days').value);
            const resDiv = document.getElementById('calc-result');
            const resName = document.getElementById('calc-res-name');
            const resPrice = document.getElementById('calc-res-price');

            if(!aud || aud <= 0) {
                addLog("> Error: Invalid Audience count. Input required.", "text-red-400");
                return;
            }

            addLog(`> Requesting calculation for [${event.toUpperCase()}] scale ${aud} pax...`, "text-blue-400");
            addLog("> Scanning Rule-Base Knowledge Tree...", "text-white/40");
            
            setTimeout(() => {
                let package = "";
                let basePrice = 0;

                // Simple Decision Tree Simulation
                if (event === "wedding") {
                    if(aud > 1000) {
                        package = "Titanium Wedding (3x LED)";
                        basePrice = 85000000;
                    } else if (aud > 500) {
                        package = "Platinum Wedding (2x LED)";
                        basePrice = 45000000;
                    } else {
                        package = "Gold Wedding (Standard)";
                        basePrice = 25000000;
                    }
                } else if (event === "concert") {
                    package = "Ultra Stage Festival Rig";
                    basePrice = 120000000;
                } else if (event === "corporate") {
                    package = aud > 300 ? "Grand Gathering Pack" : "Standard Gathering";
                    basePrice = aud > 300 ? 35000000 : 15000000;
                } else {
                    package = "Essential Seminar Kit";
                    basePrice = 7500000;
                }

                const finalPrice = basePrice * days;

                addLog(`> Node Match Found: [${package}]`, "text-green-400");
                addLog(`> Calculation Finalized: IDR ${finalPrice.toLocaleString()}`, "text-yellow-400");
                
                resDiv.classList.remove('hidden');
                resName.innerText = package;
                resPrice.innerText = "Estimasi: Rp " + finalPrice.toLocaleString('id-ID');
                
                monitor.scrollTop = monitor.scrollHeight;
            }, 1000);
        }

        function addLog(text, colorClass) {
            const div = document.createElement('div');
            div.className = colorClass;
            div.innerText = text;
            monitor.appendChild(div);
            monitor.scrollTop = monitor.scrollHeight;
        }

        // Observer for Active State & Dot Navigation
        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    sections.forEach(s => s.classList.remove('active'));
                    dots.forEach(d => d.classList.remove('active'));
                    
                    entry.target.classList.add('active');
                    const index = Array.from(sections).indexOf(entry.target);
                    if(dots[index]) dots[index].classList.add('active');
                }
            });
        }, observerOptions);

        sections.forEach(section => observer.observe(section));

        // Click to Scroll Implementation
        function scrollToSlide(index) {
            sections[index].scrollIntoView({ behavior: 'smooth' });
        }

        // Custom Keyboard Navigation (Arrows & Space)
        window.addEventListener('keydown', (e) => {
            const current = document.querySelector('section.active');
            let target;
            if (e.key === 'ArrowDown' || e.key === ' ' || e.key === 'PageDown') {
                target = current.nextElementSibling;
            } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
                target = current.previousElementSibling;
            }
            
            if (target && target.tagName === 'SECTION') {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Logo Floating Physics (Slight Mouse Parallax)
        document.addEventListener('mousemove', (e) => {
            const orbs = document.querySelectorAll('.glow-orb');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            orbs.forEach(orb => {
                orb.style.transform = `translate(${x * 30}px, ${y * 30}px)`;
            });
        });
    </script>
</body>
</html>