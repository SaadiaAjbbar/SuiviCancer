<template>
  <div class="consultation-page">
    <header class="page-header">
      <h2>🏥 Mon Historique Médical</h2>
      <p>Retrouvez le détail de vos visites et les décisions prises par votre médecin.</p>
    </header>

    <div v-if="isLoading" class="loading">Chargement de votre dossier...</div>

    <div v-else-if="consultations.length > 0" class="consultation-list">
      <div v-for="c in consultations" :key="c.id" class="consultation-card">

        <div class="card-side">
          <div class="date-box">
            <span class="day">{{ getDay(c.date) }}</span>
            <span class="month">{{ getMonth(c.date) }}</span>
          </div>
          <span :class="['gravite-badge', c.gravite.toLowerCase()]">{{ c.gravite }}</span>
        </div>

        <div class="card-main">
          <div class="card-header">
            <h3>Consultation avec Dr. {{ c.medecin?.user?.nom }}</h3>
          </div>

          <div class="medical-tags">
            <span v-for="s in c.symptomes" :key="s.id" class="tag symptome">🩹 {{ s.nom }}</span>
            <span v-for="t in c.toxicites" :key="t.id" class="tag toxicite">⚠️ {{ t.nom }}</span>
          </div>

          <hr class="divider" />

          <div class="decisions-section" v-if="c.etat_general">
            <h4>📋 Décisions & État Général</h4>
            <p class="eg-desc">{{ c.etat_general.description }}</p>

            <div class="decision-badges">
              <div v-if="c.etat_general.traitement?.length" class="decision-item">
                <span class="icon">💊</span> <strong>Traitement :</strong> {{ c.etat_general.traitement.length }}
                prescrit(s)
              </div>
              <div v-if="c.etat_general.rendez_vous?.length" class="decision-item">
                <span class="icon">📅</span> <strong>Prochain RDV :</strong> Prévu
              </div>
              <div v-if="c.etat_general.conseil?.length" class="decision-item">
                <span class="icon">💡</span> <strong>Conseils :</strong> Inclus
              </div>
            </div>
          </div>
          <div v-else class="no-eg">
            <p>En attente du compte-rendu détaillé du médecin.</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <span class="icon">🏥</span>
      <h3>Aucune consultation</h3>
      <p>Votre historique de consultations apparaîtra ici.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const consultations = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchConsultations = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-consultations', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) consultations.value = await res.json();
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

const getDay = (d) => new Date(d).getDate();
const getMonth = (d) => new Date(d).toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase();

onMounted(fetchConsultations);
</script>

<style scoped>
.consultation-page {
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.consultation-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.consultation-card {
  display: flex;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border: 1px solid #eef2f7;
}

.card-side {
  background: #f8fafc;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 100px;
  border-right: 1px solid #eef2f7;
}

.date-box {
  text-align: center;
  margin-bottom: 15px;
}

.day {
  display: block;
  font-size: 1.8rem;
  font-weight: 800;
  color: #2c3e50;
}

.month {
  font-size: 0.9rem;
  color: #3498db;
  font-weight: bold;
}

.gravite-badge {
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: bold;
  text-transform: uppercase;
}

.gravite-badge.normale {
  background: #dcfce7;
  color: #166534;
}

.gravite-badge.urgente {
  background: #fee2e2;
  color: #991b1b;
}

.card-main {
  flex: 1;
  padding: 25px;
}

.card-header h3 {
  margin: 0 0 15px 0;
  color: #1e293b;
}

.medical-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 20px;
}

.tag {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.tag.symptome {
  background: #f1f5f9;
  color: #475569;
}

.tag.toxicite {
  background: #fff7ed;
  color: #9a3412;
  border: 1px solid #ffedd5;
}

.divider {
  border: 0;
  border-top: 1px solid #f1f5f9;
  margin: 20px 0;
}

.decisions-section h4 {
  margin: 0 0 10px 0;
  font-size: 1rem;
  color: #334155;
}

.eg-desc {
  color: #64748b;
  font-style: italic;
  margin-bottom: 15px;
}

.decision-badges {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.decision-item {
  font-size: 0.85rem;
  background: #f0f7ff;
  padding: 8px 12px;
  border-radius: 10px;
  color: #1e40af;
}

.no-eg {
  color: #94a3b8;
  font-size: 0.9rem;
}

.empty-state {
  text-align: center;
  padding: 60px;
  color: #94a3b8;
}
</style>
