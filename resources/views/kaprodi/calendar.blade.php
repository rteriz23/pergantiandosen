<x-app-layout>
<x-slot name="header">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-xl text-gray-800">Kalender Jadwal Dosen</h2>
            <p class="text-sm text-gray-500 mt-0.5">Tampilan jadwal lengkap beserta ruangan, pertemuan & status pengajuan</p>
        </div>
        <a href="{{ route('kaprodi.requests') }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
           ← Daftar Permohonan
        </a>
    </div>
</x-slot>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

        {{-- Filters --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Filter Dosen</label>
                    <select id="filterDosen" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[220px]">
                        <option value="">— Pilih Dosen —</option>
                        @foreach($dosens as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Filter Ruangan</label>
                    <select id="filterRoom" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[180px]">
                        <option value="">Semua Ruangan</option>
                        @foreach($rooms as $r)
                        <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->type }})</option>
                        @endforeach
                    </select>
                </div>
                <button onclick="refreshCalendar()"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-indigo-200">
                    🔍 Tampilkan
                </button>
                <button onclick="refreshCalendar(true)"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                    ↺ Reset
                </button>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-5 text-xs font-semibold text-gray-700">
            <div class="flex items-center gap-2">
                <div class="w-3.5 h-3.5 rounded-full bg-blue-500 shadow-sm shadow-blue-300"></div>
                Jadwal Reguler
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3.5 h-3.5 rounded-full bg-yellow-400 shadow-sm shadow-yellow-200"></div>
                Ada Pengajuan Pending
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3.5 h-3.5 rounded-full bg-green-500 shadow-sm shadow-green-300"></div>
                Sudah Diganti
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3.5 h-3.5 rounded-full bg-red-400 shadow-sm shadow-red-300"></div>
                SLA Breach (mendesak)
            </div>
        </div>

        {{-- Calendar Container --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
            <div id="kaprodi-calendar" class="p-4 min-h-[650px]"></div>
        </div>

    </div>
</div>

{{-- ── Detail Modal ─────────────────────────────────────────────── --}}
<div id="event-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden"
     onclick="if(event.target===this) closeModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden" style="max-height:90vh;overflow-y:auto">

        {{-- Modal Header --}}
        <div id="modal-header" class="px-7 py-5 flex items-start justify-between gap-4">
            <div>
                <div id="modal-status-badge" class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wide mb-2"></div>
                <h3 id="modal-title" class="text-lg font-black text-gray-900 leading-tight"></h3>
                <p id="modal-kelas" class="text-sm text-gray-500 mt-0.5"></p>
            </div>
            <button onclick="closeModal()" class="shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-white/60 hover:bg-gray-100 text-gray-500 transition text-lg">✕</button>
        </div>

        {{-- Modal Body --}}
        <div class="px-7 pb-7 space-y-4">

            {{-- Room Info (highlight) --}}
            <div id="modal-room-box" class="rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shrink-0" id="modal-room-icon">🏫</div>
                <div>
                    <div class="text-xs font-bold uppercase text-gray-400 tracking-widest">Ruangan</div>
                    <div id="modal-room-name" class="text-base font-black text-gray-900 mt-0.5"></div>
                    <div id="modal-room-meta" class="text-xs text-gray-500 mt-0.5"></div>
                </div>
            </div>

            {{-- Schedule Info Grid --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-xs text-gray-400 font-bold uppercase">Dosen</div>
                    <div id="modal-dosen" class="text-sm font-semibold text-gray-800 mt-0.5"></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-xs text-gray-400 font-bold uppercase">Pertemuan ke-</div>
                    <div id="modal-pertemuan" class="text-sm font-semibold text-gray-800 mt-0.5"></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-xs text-gray-400 font-bold uppercase">Waktu</div>
                    <div id="modal-waktu" class="text-sm font-semibold text-gray-800 mt-0.5"></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-xs text-gray-400 font-bold uppercase">Periode</div>
                    <div id="modal-periode" class="text-sm font-semibold text-gray-800 mt-0.5"></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                    <div class="text-xs text-gray-400 font-bold uppercase">Program Studi</div>
                    <div id="modal-prodi" class="text-sm font-semibold text-gray-800 mt-0.5"></div>
                </div>
            </div>

            {{-- Pending Request Info --}}
            <div id="modal-pending-section" class="hidden rounded-2xl border-2 border-amber-200 bg-amber-50 p-4 space-y-3">
                <div class="flex items-center gap-2 text-amber-700 font-black text-sm">
                    <span>⚠️</span> Ada Permohonan Pergantian Pending
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <div class="text-amber-600 font-bold uppercase">Pengaju</div>
                        <div id="modal-pengaju" class="text-gray-800 font-semibold mt-0.5"></div>
                    </div>
                    <div>
                        <div class="text-amber-600 font-bold uppercase">NIM / NIDN</div>
                        <div id="modal-pengaju-nim" class="text-gray-800 font-semibold mt-0.5"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-amber-600 font-bold uppercase">Usulan Waktu Baru</div>
                        <div id="modal-waktu-usulan" class="text-gray-800 font-semibold mt-0.5"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-amber-600 font-bold uppercase">Usulan Ruangan</div>
                        <div id="modal-ruangan-usulan" class="text-gray-800 font-semibold mt-0.5"></div>
                    </div>
                </div>
                <a id="modal-request-link" href="#"
                   class="flex items-center justify-center gap-2 w-full py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black rounded-xl transition">
                   Tinjau Permohonan →
                </a>
            </div>

            {{-- SLA Breach Warning --}}
            <div id="modal-sla-section" class="hidden rounded-2xl border-2 border-red-200 bg-red-50 p-3 flex items-center gap-3 text-red-700 text-sm font-bold">
                🚨 Permohonan ini sudah melewati batas waktu SLA. Segera ditindaklanjuti!
            </div>

        </div>
    </div>
</div>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('kaprodi-calendar');
    calendar = new FullCalendar.Calendar(el, {
        initialView: 'timeGridWeek',
        locale: 'id',
        height: 'auto',
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'timeGridWeek,timeGridDay,dayGridMonth'
        },
        events: [],

        // ── Custom event renderer: tampilkan mata kuliah + ruangan di blok ──
        eventContent: function(arg) {
            const p = arg.event.extendedProps;
            const timeText = arg.timeText;

            const slaIcon  = p.sla_breached        ? '<span class="mr-1">🚨</span>' : '';
            const pendIcon = p.has_pending_request  ? '<span class="mr-1">⚠️</span>' : '';

            const roomHtml = p.room && p.room !== 'Belum ada ruangan'
                ? `<div style="margin-top:3px;font-size:10px;opacity:0.9;display:flex;align-items:center;gap:3px;">
                       <svg style="width:10px;height:10px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                               d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                       </svg>
                       ${p.room}
                   </div>`
                : `<div style="margin-top:3px;font-size:10px;opacity:0.6;font-style:italic;">Belum ada ruangan</div>`;

            const pertHtml = p.pertemuan
                ? `<div style="margin-top:2px;font-size:10px;opacity:0.85;">Pert. ${p.pertemuan}</div>`
                : '';

            return {
                html: `<div style="padding:3px 5px;line-height:1.3;word-break:break-word;">
                    <div style="font-size:11px;font-weight:700;">${slaIcon}${pendIcon}${arg.event.title}</div>
                    ${roomHtml}
                    ${pertHtml}
                </div>`
            };
        },

        // ── Click → open detail modal ──
        eventClick: function(info) {
            openModal(info.event);
        }
    });
    calendar.render();
});

function refreshCalendar(reset = false) {
    if (reset) {
        document.getElementById('filterDosen').value = '';
        document.getElementById('filterRoom').value  = '';
        calendar.removeAllEvents();
        return;
    }
    const dosenId = document.getElementById('filterDosen').value;
    const room    = document.getElementById('filterRoom').value;

    if (!dosenId) { alert('Pilih dosen terlebih dahulu'); return; }

    const url = `/api/schedules?dosen_id=${dosenId}` + (room ? `&room=${encodeURIComponent(room)}` : '');
    fetch(url)
        .then(r => r.json())
        .then(data => {
            calendar.removeAllEvents();
            data.forEach(ev => calendar.addEvent(ev));
        });
}

// ── Modal Logic ────────────────────────────────────────────────────────────
function openModal(event) {
    const p   = event.extendedProps;
    const start = event.start;
    const end   = event.end;

    // Status badge
    const badge    = document.getElementById('modal-status-badge');
    const header   = document.getElementById('modal-header');
    let badgeText  = p.status || 'Terjadwal';
    let badgeCls   = 'bg-blue-100 text-blue-700';
    let headerBg   = 'bg-blue-50';

    if (p.sla_breached) {
        badgeText = '🚨 SLA Breach'; badgeCls = 'bg-red-100 text-red-700'; headerBg = 'bg-red-50';
    } else if (p.has_pending_request) {
        badgeText = '⚠️ Pending'; badgeCls = 'bg-amber-100 text-amber-700'; headerBg = 'bg-amber-50';
    } else if (p.status === 'Diganti') {
        badgeText = '✓ Diganti'; badgeCls = 'bg-green-100 text-green-700'; headerBg = 'bg-green-50';
    }

    badge.textContent  = badgeText;
    badge.className    = `inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wide mb-2 ${badgeCls}`;
    header.style.background = '';
    header.className = `px-7 py-5 flex items-start justify-between gap-4 ${headerBg}`;

    // Basic info
    setText('modal-title',    p.mata_kuliah || event.title);
    setText('modal-kelas',    'Kelas ' + (p.kelas || '-') + (p.dosen_nama ? ' • ' + p.dosen_nama : ''));
    setText('modal-dosen',    p.dosen_nama  || '-');
    setText('modal-pertemuan', p.pertemuan  ? 'Pertemuan ' + p.pertemuan : '-');
    setText('modal-periode',  p.periode     || '-');
    setText('modal-prodi',    p.prodi       || '-');

    // Waktu
    const fmt = (d) => d ? new Date(d).toLocaleString('id-ID', {weekday:'long',day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '-';
    const fmtTime = (d) => d ? new Date(d).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'}) : '-';
    setText('modal-waktu', fmt(start) + (end ? ' — ' + fmtTime(end) : ''));

    // Room box
    const roomBox  = document.getElementById('modal-room-box');
    const roomIcon = document.getElementById('modal-room-icon');
    const roomName = p.room || 'Belum ada ruangan';
    setText('modal-room-name', roomName);

    let roomMeta = '';
    if (p.room_type && p.room_type !== '-') {
        const typeLabel = { kelas:'Ruang Kelas', lab:'Lab Komputer', aula:'Aula', online:'Online' }[p.room_type] || p.room_type;
        roomMeta += typeLabel;
    }
    if (p.room_capacity && p.room_capacity !== '-') roomMeta += (roomMeta ? ' • ' : '') + 'Kap. ' + p.room_capacity + ' org';
    setText('modal-room-meta', roomMeta || '-');

    const roomIcons = { kelas:'🏫', lab:'💻', aula:'🎤', online:'📡' };
    roomIcon.textContent = roomIcons[p.room_type] || '🏫';

    const roomColors = {
        kelas:  'bg-indigo-50 text-indigo-600',
        lab:    'bg-purple-50 text-purple-600',
        aula:   'bg-orange-50 text-orange-600',
        online: 'bg-sky-50 text-sky-600',
    };
    roomBox.className = `rounded-2xl p-4 flex items-center gap-4 ${roomColors[p.room_type] || 'bg-gray-50'}`;

    // Pending request section
    const pendSection = document.getElementById('modal-pending-section');
    const slaSection  = document.getElementById('modal-sla-section');

    if (p.has_pending_request) {
        pendSection.classList.remove('hidden');
        setText('modal-pengaju',        p.pengaju_nama || '-');
        setText('modal-pengaju-nim',    p.pengaju_nim  || '-');
        setText('modal-waktu-usulan',   p.waktu_usulan || '-');
        setText('modal-ruangan-usulan', p.ruangan_usulan || '-');

        const link = document.getElementById('modal-request-link');
        link.href = p.pending_request_id
            ? `{{ route('kaprodi.requests') }}`
            : '#';
    } else {
        pendSection.classList.add('hidden');
    }

    slaSection.classList.toggle('hidden', !p.sla_breached);

    document.getElementById('event-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('event-modal').classList.add('hidden');
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}
</script>

<style>
.fc-event { cursor: pointer !important; border-radius: 8px !important; }
.fc-event:hover { filter: brightness(1.08); }
.fc-timegrid-event .fc-event-main { padding: 2px !important; }
</style>
</x-app-layout>
