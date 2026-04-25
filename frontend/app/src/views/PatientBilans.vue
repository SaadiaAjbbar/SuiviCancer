<template>
  <div class="bilan-page">
    <header class="page-header">
      <h2>🩺 Mes Bilans de Santé</h2>
      <p>Consultez l'historique de vos évaluations médicales et les conclusions du médecin.</p>
    </header>

    <div v-if="isLoading" class="loading">Analyse de vos données en cours...</div>

    <div v-else-if="bilans.length > 0" class="bilan-timeline">
      <div v-for="bilan in bilans" :key="bilan.id" class="bilan-card">
        <div class="bilan-aside">
          <div class="bilan-date">
            <span class="day">{{ getDay(bilan.created_at) }}</span>
            <span class="month">{{ getMonth(bilan.created_at) }}</span>
          </div>
          <div class="line"></div>
        </div>

        <div class="bilan-main">
          <div class="bilan-header">
            <h3>
              {{ bilan.consultation_id ? 'Résumé de la consultation' : 'Suivi médical (Réponse)' }}
            </h3>

            <span class="doctor-name">
              <template v-if="bilan.consultation?.medecin">
                Par Dr. {{ bilan.consultation.medecin.user.nom }}
              </template>

              <template v-else-if="bilan.reponse?.patient?.medecin">
                Par Dr. {{ bilan.reponse.patient.medecin.user.nom }}
              </template>
            </span>
          </div>

          <div class="bilan-description">
            <p>{{ bilan.description }}</p>
          </div>

          <div class="bilan-attachments">
            <span v-if="bilan.traitement?.length" class="attach-tag pill">💊 Traitement prescrit</span>
            <span v-if="bilan.rendez_vous?.length" class="attach-tag rdv">📅 RDV fixé</span>
            <span v-if="bilan.conseil?.length" class="attach-tag advice">💡 Conseil ajouté</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <span class="icon">🩺</span>
      <h3>Aucun bilan disponible</h3>
      <p>Vos bilans apparaîtront ici après vos consultations avec le médecin.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const bilans = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchBilans = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-bilans', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      bilans.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur bilans:", e);
  } finally {
    isLoading.value = false;
  }
};

const getDay = (date) => new Date(date).getDate();
const getMonth = (date) => new Date(date).toLocaleDateString('fr-FR', { month: 'short' });

onMounted(fetchBilans);
</script>

<style scoped>
.bilan-page {
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 40px;
}

.bilan-timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.bilan-card {
  display: flex;
  gap: 30px;
}

.bilan-aside {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 60px;
}

.bilan-date {
  background: #3498db;
  color: white;
  padding: 10px;
  border-radius: 12px;
  text-align: center;
  width: 60px;
  box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
}

.bilan-date .day {
  display: block;
  font-size: 1.4rem;
  font-weight: bold;
}

.bilan-date .month {
  font-size: 0.7rem;
  text-transform: uppercase;
}

.line {
  width: 2px;
  flex-grow: 1;
  background: #e2e8f0;
  margin: 10px 0;
}

.bilan-main {
  background: white;
  flex: 1;
  padding: 20px;
  border-radius: 16px;
  margin-bottom: 30px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
  border: 1px solid #f1f5f9;
}

.bilan-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.bilan-header h3 {
  font-size: 1.1rem;
  color: #1e293b;
  margin: 0;
}

.doctor-name {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.bilan-description {
  background: #f8fafc;
  padding: 15px;
  border-radius: 12px;
  color: #334155;
  line-height: 1.6;
  margin-bottom: 15px;
}

.bilan-attachments {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.attach-tag {
  font-size: 0.75rem;
  padding: 5px 12px;
  border-radius: 20px;
  font-weight: 600;
}

.attach-tag.pill {
  background: #e1effe;
  color: #1e429f;
}

.attach-tag.rdv {
  background: #def7ed;
  color: #03543f;
}

.attach-tag.advice {
  background: #fef3c7;
  color: #92400e;
}

.empty-state {
  text-align: center;
  padding: 60px;
  color: #94a3b8;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #3498db;
}
</style>
