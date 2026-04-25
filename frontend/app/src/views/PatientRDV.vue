<template>
  <div class="rdv-page">
    <header class="page-header">
      <h2>📅 Mes Rendez-vous</h2>
      <p>Retrouvez ici vos consultations programmées et passées.</p>
    </header>

    <div v-if="isLoading" class="loading">Chargement de vos rendez-vous...</div>

    <div v-else-if="rdvs.length > 0" class="rdv-grid">
      <div v-for="rdv in rdvs" :key="rdv.id" :class="['rdv-card', getStatusClass(rdv.status)]">
        <div class="rdv-date">
          <span class="day">{{ getDay(rdv.date) }}</span>
          <span class="month">{{ getMonth(rdv.date) }}</span>
        </div>

        <div class="rdv-info">
          <div class="status-badge">{{ rdv.status }}</div>
          <h3>Consultation de suivi</h3>
          <p class="time">🕒 Heure : {{ getHour(rdv.date) }}</p>
          <p class="doctor" v-if="rdv.etat_general?.consultation?.medecin">
            👨‍⚕️ Médecin : Dr. {{ rdv.etat_general.consultation.medecin.user.nom }}
          </p>
        </div>

        <div class="rdv-footer" v-if="isUpcoming(rdv.date)">
          <span class="incoming-tag">Prochainement</span>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <span class="icon">📅</span>
      <h3>Aucun rendez-vous</h3>
      <p>Vous n'avez pas encore de rendez-vous programmé.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const rdvs = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchRDVs = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-rendez-vous', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      rdvs.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur RDV:", e);
  } finally {
    isLoading.value = false;
  }
};

// --- Helpers de formatage ---
const getDay = (d) => new Date(d).getDate();
const getMonth = (d) => new Date(d).toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase();
const getHour = (d) => new Date(d).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
const isUpcoming = (d) => new Date(d) > new Date();

const getStatusClass = (status) => {
  if (status === 'Programmé') return 'status-planned';
  if (status === 'Annulé') return 'status-cancelled';
  return 'status-done';
};

onMounted(fetchRDVs);
</script>

<style scoped>
.rdv-page {
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h2 {
  color: #2c3e50;
  font-size: 1.8rem;
  margin-bottom: 5px;
}

.page-header p {
  color: #7f8c8d;
}

.rdv-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.rdv-card {
  background: white;
  border-radius: 16px;
  display: flex;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
  border: 1px solid #edf2f7;
  transition: transform 0.2s;
}

.rdv-card:hover {
  transform: translateY(-5px);
}

.rdv-date {
  background: #f8fafc;
  padding: 10px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 70px;
  border: 1px solid #e2e8f0;
}

.day {
  font-size: 1.5rem;
  font-weight: 800;
  color: #3498db;
}

.month {
  font-size: 0.8rem;
  font-weight: bold;
  color: #94a3b8;
}

.rdv-info {
  margin-left: 20px;
  flex: 1;
}

.rdv-info h3 {
  font-size: 1.1rem;
  margin: 10px 0 5px;
  color: #1e293b;
}

.rdv-info p {
  margin: 3px 0;
  font-size: 0.9rem;
  color: #64748b;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: bold;
  text-transform: uppercase;
  background: #e2e8f0;
}

/* Couleurs par Status */
.status-planned .status-badge {
  background: #dcfce7;
  color: #166534;
}

.status-planned {
  border-left: 5px solid #22c55e;
}

.incoming-tag {
  position: absolute;
  top: 0;
  right: 0;
  background: #3498db;
  color: white;
  font-size: 0.7rem;
  padding: 4px 12px;
  border-bottom-left-radius: 12px;
  font-weight: bold;
}

.empty-state {
  text-align: center;
  padding: 60px;
  background: white;
  border-radius: 20px;
  color: #94a3b8;
}

.empty-state .icon {
  font-size: 4rem;
  display: block;
  margin-bottom: 10px;
}
</style>
