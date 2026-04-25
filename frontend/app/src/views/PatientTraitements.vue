<template>
  <div class="treatment-page">
    <header class="page-header">
      <h2>💊 Mes Traitements</h2>
      <p>Retrouvez ici les médicaments et soins prescrits par votre médecin.</p>
    </header>

    <div v-if="isLoading" class="loading">Chargement de vos traitements...</div>

    <div v-else-if="traitements.length > 0" class="treatment-list">
      <div v-for="t in traitements" :key="t.id" class="treatment-card">
        <div class="treatment-icon">💊</div>

        <div class="treatment-content">
          <div class="treatment-header">
            <h3>{{ t.nom }}</h3>
            <span class="prescribed-date">Prescrit le : {{ formatDate(t.created_at) }}</span>
          </div>

          <div class="treatment-description">
            <strong>Instructions :</strong>
            <p>{{ t.description }}</p>
          </div>

          <div class="treatment-footer">
            <span class="doctor-badge" v-if="t.etat_general">
              👨‍⚕️
              <template v-if="t.etat_general.consultation?.medecin">
                Dr. {{ t.etat_general.consultation.medecin.user.nom }}
              </template>
              <template v-else-if="t.etat_general.reponse?.patient?.medecin">
                Dr. {{ t.etat_general.reponse.patient.medecin.user.nom }}
              </template>
              <template v-else>
                Médecin partenaire
              </template>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <span class="icon">💊</span>
      <h3>Aucun traitement</h3>
      <p>Vous n'avez pas de traitement actif pour le moment.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const traitements = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchTraitements = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-traitements', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      traitements.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur traitements:", e);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

onMounted(fetchTraitements);
</script>

<style scoped>
.treatment-page {
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h2 {
  color: #2c3e50;
  font-size: 1.8rem;
}

.treatment-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.treatment-card {
  background: white;
  border-radius: 15px;
  padding: 20px;
  display: flex;
  gap: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border-left: 6px solid #3498db;
  transition: transform 0.2s;
}

.treatment-card:hover {
  transform: scale(1.01);
}

.treatment-icon {
  font-size: 2rem;
  background: #ebf5ff;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.treatment-content {
  flex: 1;
}

.treatment-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.treatment-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.3rem;
  text-transform: capitalize;
}

.prescribed-date {
  font-size: 0.85rem;
  color: #94a3b8;
}

.treatment-description {
  background: #f8fafc;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 15px;
}

.treatment-description p {
  margin: 5px 0 0;
  color: #475569;
  line-height: 1.5;
}

.doctor-badge {
  font-size: 0.85rem;
  color: #3498db;
  font-weight: 600;
  background: #e1effe;
  padding: 4px 10px;
  border-radius: 6px;
}

.empty-state {
  text-align: center;
  padding: 60px;
  background: white;
  border-radius: 20px;
  color: #94a3b8;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #3498db;
}
</style>
