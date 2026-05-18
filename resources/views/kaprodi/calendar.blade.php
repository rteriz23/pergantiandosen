<x-app-layout>
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="font-bold text-xl text-gray-800">Kalender Jadwal Dosen</h2>
        <a href="{{ route('kaprodi.requests') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">← Daftar Permohonan</a>
    </div>
</x-slot>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Filters --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Filter Dosen</label>
                    <select id="filterDosen" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[200px]">
                        <option value="">Semua Dosen</option>
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
                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button onclick="refreshCalendar()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all">Tampilkan</button>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-blue-500"></div> Jadwal Reguler</div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-yellow-500"></div> Ada Pengajuan Pending</div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-green-500"></div> Sudah Diganti</div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-red-400"></div> SLA Breach</div>
        </div>

        {{-- Calendar --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
            <div id="kaprodi-calendar" class="p-4 min-h-[600px]"></div>
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
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay,dayGridMonth'
        },
        events: [],
        eventClick: function(info) {
            const p = info.event.extendedProps;
            alert(`${info.event.title}\nStatus: ${p.status || '-'}\nRuangan: ${p.room || '-'}`);
        }
    });
    calendar.render();
});

async function refreshCalendar() {
    const dosenId = document.getElementById('filterDosen').value;
    const room    = document.getElementById('filterRoom').value;

    if (!dosenId) { alert('Pilih dosen terlebih dahulu'); return; }

    const res  = await fetch(`/api/schedules?dosen_id=${dosenId}`);
    const data = await res.json();

    calendar.removeAllEvents();
    data.forEach(ev => calendar.addEvent(ev));
}
</script>
</x-app-layout>
