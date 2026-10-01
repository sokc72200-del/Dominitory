<script setup>
import { computed, onMounted, ref } from "vue";

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["logout"]);

const userName = computed(() => {
  return props.user?.name || props.user?.email || "Admin";
});

const activeView = ref("overview");
const searchQuery = ref("");
const rooms = ref([]);
const students = ref([]);
const loading = ref(false);
const loadError = ref("");

const navigation = [
  { id: "overview", label: "Overview" },
  { id: "residents", label: "Residents" },
  { id: "rooms", label: "Rooms" },
];

const pageTitle = computed(() => {
  return navigation.find((item) => item.id === activeView.value)?.label || "Overview";
});

const assignedStudents = computed(() =>
  students.value.filter((student) => student.room_id != null),
);

const totalCapacity = computed(() =>
  rooms.value.reduce((total, room) => total + Number(room.capacity || 0), 0),
);

const occupancy = computed(() =>
  totalCapacity.value
    ? Math.round((assignedStudents.value.length / totalCapacity.value) * 100)
    : 0,
);

const openBeds = computed(() =>
  Math.max(totalCapacity.value - assignedStudents.value.length, 0),
);

const filteredStudents = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return students.value;

  return students.value.filter((student) =>
    [student.name, student.email, student.room?.room_number]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(query)),
  );
});

const filteredRooms = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return rooms.value;

  return rooms.value.filter((room) =>
    [room.room_number, room.gender, room.floor]
      .filter((value) => value != null)
      .some((value) => String(value).toLowerCase().includes(query)),
  );
});

function roomOccupancy(room) {
  return students.value.filter(
    (student) => String(student.room_id) === String(room.id),
  ).length;
}

function roomStatus(room) {
  if (room.status === "maintenance") return "Maintenance";
  return roomOccupancy(room) >= Number(room.capacity) ? "Full" : "Available";
}

function initials(name) {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();
}

async function loadDashboard() {
  loading.value = true;
  loadError.value = "";

  try {
    const token = localStorage.getItem("dominitory_token");
    const headers = {
      Accept: "application/json",
      Authorization: `Bearer ${token}`,
    };
    const [roomsResponse, studentsResponse] = await Promise.all([
      fetch("/api/rooms", { headers }),
      fetch("/api/students", { headers }),
    ]);

    if (!roomsResponse.ok || !studentsResponse.ok) {
      throw new Error("Could not load residence data. Please try again.");
    }

    const [roomData, studentData] = await Promise.all([
      roomsResponse.json(),
      studentsResponse.json(),
    ]);
    rooms.value = Array.isArray(roomData) ? roomData : [];
    students.value = Array.isArray(studentData) ? studentData : [];
  } catch (error) {
    loadError.value = error.message || "Could not load residence data.";
  } finally {
    loading.value = false;
  }
}

onMounted(loadDashboard);
</script>

<template>
  <main class="admin-shell">
    <aside class="sidebar">
      <a class="brand" href="#overview" @click.prevent="activeView = 'overview'">
        <span class="brand-mark">D</span>
        <span class="brand-name">Dominitory<span>RESIDENCE OFFICE</span></span>
      </a>

      <p class="nav-caption">WORKSPACE</p>
      <nav class="side-nav" aria-label="Admin navigation">
        <button
          v-for="item in navigation"
          :key="item.id"
          :class="['nav-item', { selected: activeView === item.id }]"
          type="button"
          @click="activeView = item.id; searchQuery = ''"
        >
          <span class="nav-indicator"></span>
          {{ item.label }}
          <span v-if="item.id === 'residents'" class="nav-count">{{ students.length }}</span>
          <span v-if="item.id === 'rooms'" class="nav-count">{{ rooms.length }}</span>
        </button>
      </nav>

      <div class="sidebar-bottom">
        <div class="profile-block">
          <span class="profile-avatar">{{ initials(userName) }}</span>
          <span class="profile-copy"><strong>{{ userName }}</strong><small>Administrator</small></span>
        </div>
        <button class="signout-button" type="button" @click="emit('logout')">Sign out</button>
      </div>
    </aside>

    <section class="workspace">
      <header class="topbar">
        <div class="breadcrumb"><span>Residence office</span><span>/</span><strong>{{ pageTitle }}</strong></div>
        <div class="topbar-right">
          <span class="today-label">{{ new Date().toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' }) }}</span>
          <span class="topbar-avatar">{{ initials(userName) }}</span>
        </div>
      </header>

      <div class="page-content">
        <section v-if="activeView === 'overview'" class="view-content">
          <div class="welcome-banner">
            <div class="welcome-copy">
              <p class="eyebrow">ADMINISTRATIVE OVERVIEW</p>
              <h1>Good day, {{ userName.split(' ')[0] }}.</h1>
              <p>Here’s what’s happening across your residence today.</p>
            </div>
            <div class="banner-note"><span class="note-dot"></span>Residence operations</div>
          </div>

          <div v-if="loadError" class="load-error" role="alert">
            <span>{{ loadError }}</span>
            <button type="button" @click="loadDashboard">Try again</button>
          </div>

          <div class="metric-grid" aria-label="Residence summary">
            <article class="metric-card">
              <span class="metric-label">Total residents</span>
              <strong class="metric-value">{{ loading ? '—' : students.length }}</strong>
              <span class="metric-foot">Registered in the residence</span>
              <span class="metric-accent accent-green"></span>
            </article>
            <article class="metric-card">
              <span class="metric-label">Rooms</span>
              <strong class="metric-value">{{ loading ? '—' : rooms.length }}</strong>
              <span class="metric-foot">Across all floors</span>
              <span class="metric-accent accent-orange"></span>
            </article>
            <article class="metric-card">
              <span class="metric-label">Occupancy</span>
              <strong class="metric-value">{{ loading ? '—' : `${occupancy}%` }}</strong>
              <span class="metric-foot">{{ assignedStudents.length }} of {{ totalCapacity }} beds assigned</span>
              <span class="metric-accent accent-blue"></span>
            </article>
            <article class="metric-card">
              <span class="metric-label">Beds available</span>
              <strong class="metric-value">{{ loading ? '—' : openBeds }}</strong>
              <span class="metric-foot">Remaining room capacity</span>
              <span class="metric-accent accent-yellow"></span>
            </article>
          </div>

          <div class="overview-grid">
            <section class="data-panel residents-panel">
              <div class="panel-heading">
                <div><p class="eyebrow">PEOPLE</p><h2>Residents</h2></div>
                <button class="text-action" type="button" @click="activeView = 'residents'">View all</button>
              </div>
              <div v-if="loading" class="empty-state">Loading residents…</div>
              <div v-else-if="!students.length" class="empty-state">No resident records yet.</div>
              <div v-else class="resident-list">
                <div v-for="student in students.slice(0, 5)" :key="student.id" class="resident-row">
                  <span class="resident-avatar">{{ initials(student.name || '?') }}</span>
                  <div class="resident-info"><strong>{{ student.name }}</strong><span>{{ student.email }}</span></div>
                  <span class="resident-room">{{ student.room?.room_number ? `Room ${student.room.room_number}` : 'Unassigned' }}</span>
                </div>
              </div>
            </section>

            <section class="data-panel rooms-panel">
              <div class="panel-heading">
                <div><p class="eyebrow">CAPACITY</p><h2>Room availability</h2></div>
                <button class="text-action" type="button" @click="activeView = 'rooms'">View all</button>
              </div>
              <div v-if="loading" class="empty-state">Loading rooms…</div>
              <div v-else-if="!rooms.length" class="empty-state">No rooms have been added yet.</div>
              <div v-else class="room-list">
                <div v-for="room in rooms.slice(0, 5)" :key="room.id" class="room-row">
                  <div class="room-row-top"><strong>Room {{ room.room_number }}</strong><span>{{ roomOccupancy(room) }} / {{ room.capacity }}</span></div>
                  <div class="capacity-track"><span :style="{ width: `${room.capacity ? Math.min((roomOccupancy(room) / room.capacity) * 100, 100) : 0}%` }"></span></div>
                  <div class="room-row-bottom"><span>Floor {{ room.floor }} · {{ room.gender }}</span><span :class="['status-label', roomStatus(room).toLowerCase()]">{{ roomStatus(room) }}</span></div>
                </div>
              </div>
            </section>
          </div>
        </section>

        <section v-else class="view-content list-view">
          <div class="list-heading">
            <div><p class="eyebrow">RESIDENCE DIRECTORY</p><h1>{{ pageTitle }}</h1><p class="list-subtitle">{{ activeView === 'residents' ? 'Resident records and room assignments.' : 'Room capacity and availability by floor.' }}</p></div>
            <div class="list-actions">
              <label class="search-box"><span class="search-glyph" aria-hidden="true"></span><input v-model="searchQuery" type="search" :placeholder="activeView === 'residents' ? 'Search residents' : 'Search rooms'" /></label>
              <button class="refresh-button" type="button" :disabled="loading" @click="loadDashboard">{{ loading ? 'Refreshing…' : 'Refresh' }}</button>
            </div>
          </div>

          <div v-if="loadError" class="load-error" role="alert"><span>{{ loadError }}</span><button type="button" @click="loadDashboard">Try again</button></div>

          <section class="data-panel table-panel">
            <div v-if="loading" class="empty-state">Loading {{ activeView }}…</div>
            <div v-else-if="activeView === 'residents' && !filteredStudents.length" class="empty-state">No matching residents.</div>
            <div v-else-if="activeView === 'rooms' && !filteredRooms.length" class="empty-state">No matching rooms.</div>
            <div v-else class="table-scroll">
              <table v-if="activeView === 'residents'">
                <thead><tr><th>Resident</th><th>Email</th><th>Gender</th><th>Room</th><th>Phone</th></tr></thead>
                <tbody><tr v-for="student in filteredStudents" :key="student.id"><td><span class="table-person"><span class="resident-avatar">{{ initials(student.name || '?') }}</span><strong>{{ student.name }}</strong></span></td><td>{{ student.email }}</td><td class="capitalize">{{ student.gender || '—' }}</td><td>{{ student.room?.room_number ? `Room ${student.room.room_number}` : 'Unassigned' }}</td><td>{{ student.phone || '—' }}</td></tr></tbody>
              </table>
              <table v-else>
                <thead><tr><th>Room</th><th>Floor</th><th>Gender</th><th>Occupancy</th><th>Status</th></tr></thead>
                <tbody><tr v-for="room in filteredRooms" :key="room.id"><td><strong>Room {{ room.room_number }}</strong></td><td>{{ room.floor }}</td><td class="capitalize">{{ room.gender }}</td><td><span class="occupancy-cell">{{ roomOccupancy(room) }} / {{ room.capacity }}<span class="capacity-track"><span :style="{ width: `${room.capacity ? Math.min((roomOccupancy(room) / room.capacity) * 100, 100) : 0}%` }"></span></span></span></td><td><span :class="['status-pill', roomStatus(room).toLowerCase()]">{{ roomStatus(room) }}</span></td></tr></tbody>
              </table>
            </div>
            <div v-if="!loading && !loadError" class="table-footer">{{ activeView === 'residents' ? filteredStudents.length : filteredRooms.length }} records</div>
          </section>
        </section>
      </div>
    </section>
  </main>
</template>
